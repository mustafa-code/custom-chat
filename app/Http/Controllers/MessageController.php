<?php

namespace App\Http\Controllers;

use App\Helpers\AiFunctionsCall;
use App\Helpers\ServerEvent;
use App\Models\Chat;
use App\Models\ChatReport;
use App\Models\Message;
use App\Service\QueryEmbedding;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{

    protected QueryEmbedding $query;

    public function __construct(QueryEmbedding $query)
    {
        $this->query = $query;
    }

    public function index()
    {
        $chat = Chat::create([]);
        return redirect()->route("chat.show", $chat->id);
    }

    public function report($id){
        $message = Message::find($id);
        if($message){
            ChatReport::create([
                "message_id" => $message->id,
            ]);
        }
        return redirect()->back();
    }

    public function show($id)
    {
        $chat = Chat::find($id);
        if(!$chat){
            return redirect()->route("chat.index");
        }
        $messages = $chat->messages()
        ->with("report")
        ->whereIn("role", [Message::ROLE_BOT, Message::ROLE_USER])
        ->whereNotNull("content")->whereNull("function_call")
        ->get();
        return view('conversation', [
            'chat' => $chat,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request)
    {

        try {
            $chat_id = $request->chat_id;
            $question = $request->question;
            $queryVectors = $this->query->getQueryEmbedding($question);
            $vector = json_encode($queryVectors);
            $result = DB::table('embeddings')
                ->select("text", "id")
                ->selectSub("embedding <=> '{$vector}'::vector", "distance")
                // ->where('embed_collection_id', $chat->embed_collection->id)
                ->orderBy('distance', 'asc')
                ->limit(2)
                ->get();
            $context = collect($result)->map(function ($item) {
                return $item->text;
            })->implode("\n");

            $embeddings_ids = collect($result)->map(function ($item) {
                return $item->id;
            });

            $context = preg_replace('/\n+/', '\n', $context);

            $messages = Message::where("chat_id", $chat_id)->get();
            $aiResponse = $this->doChat($chat_id, $context, $question, $messages, $embeddings_ids);
            return response()->json($aiResponse);
        } catch (Exception $e) {
            Log::error($e);
            ServerEvent::send("");
        }
    }

    private function doChat($chat_id, $context, $question, $messages, $embeddings_ids, $function = null){
        $response = $this->query->askQuestion($chat_id, $context, $question, $messages, $function);
        $messageResponse = $response['choices'][0]['message'];

        $resultText = $messageResponse["content"];
        if($resultText){
            // ServerEvent::send($resultText, "");
        }

        $functionCall = array_key_exists("function_call", $messageResponse)? json_encode($messageResponse["function_call"]): null;
        $aiMessage = [
            'chat_id' => $chat_id,
            'role' => Message::ROLE_BOT,
            'content' => $resultText,
            "function_call" => $functionCall,
            "name" => null,
            "embeddings_ids" => json_encode($embeddings_ids),
        ];
        $humanMessage = [
            'chat_id' => $chat_id,
            'role' => Message::ROLE_USER,
            'content' => $question,
            "function_call" => null,
            "embeddings_ids" => null,
            "name" => null,
        ];

        if($function){
            $humanMessage["role"] = Message::ROLE_FUNCTION;
            $humanMessage["name"] = $function;
        }

        Message::create($humanMessage);
        $aiObj = Message::create($aiMessage);
        
        $functionResponse = $this->handleOpenAiFunctionCalls($response);
        if($functionResponse){
            $functionName = $response['choices'][0]['message']['function_call']["name"];
            return $this->doChat($chat_id, $context, $functionResponse, $messages, $embeddings_ids, $functionName);
        }

        $reportUrl = route("chat.report", $aiObj->id);
        return [
            "message" => $resultText,
            "report_url" => $reportUrl,
        ];
    }

    private function handleOpenAiFunctionCalls($completions)
    {
        if (isset($completions['choices'][0]['message']['function_call'])) {
            $functionCall = $completions['choices'][0]['message']['function_call'];
            $functionName = $functionCall['name'];
            return AiFunctionsCall::$functionName(json_decode($functionCall['arguments'], true));
        }
        return null;
    }
}
