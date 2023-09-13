<?php

namespace App\Service;

use App\Helpers\AiFunctionsCall;
use Exception;
use OpenAI\Laravel\Facades\OpenAI;

class QueryEmbedding
{

    public function getQueryEmbedding($question): array
    {
        $result = OpenAI::embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => $question,
        ]);

        if (count($result['data']) == 0) {
            throw new Exception("Failed to generated query embedding!");
        }

        return $result['data'][0]['embedding'];
    }

    public function askQuestion($chat_id, $context, $question, $messages, $function = null)
    {
        $params = $this->getParams($chat_id, $context, $question, $messages, $function);
        return OpenAi::chat()->create($params);
    }

    private function getParams($chat_id, $context, $question, $messages, $function = null){
        $system_template = "
        Use the following pieces of context to answer user question.
        You are a student assistant to help students apply to OKTamam System.
        Never say you are an AI model, always refer to yourself as a students assistant.
        If you do not know the answer say I will call the manager and get back to you and never made up any answer.
        Always answer with shortes answer you can, do not say too much words.
        Never say reach out to the university directly or any other similar sentences, instead ask the manager and he will respond to you.
        It's important to append student language and chat id, when calling any functions.
        If the student wants to register you should ask him for some data one by one in separate questions:
        - First Name
        - Last Name
        - Phone
        - Email Address
        when the user give you his/her name, email, and phone number and add user language, and call the registerStudent Function.

        Chat Id: '{chat_id}'
        Context: '{context}'
        ";
        $system_prompt = str_replace("{context}", $context, $system_template);
        $system_prompt = str_replace("{chat_id}", $chat_id, $system_template);

        $messagesArray = [
            ['role' => 'system', 'content' => $system_prompt],
        ];
        foreach($messages as $message){
            $item = [
                "role" => $message->role,
                "content" => $message->content,
            ];
            if($message->name){
                $item["name"] = $message->name;
            }
            if($message->function_call){
                $item["function_call"] = json_decode($message->function_call);
            }
            $messagesArray[] = $item;
        }
        $userQuestion = [
            'role' => $function ? 'function': 'user', 
            'content' => $question,
        ];
        if($function){
            $userQuestion["name"] = $function;
        }
        $messagesArray[] = $userQuestion;
        return [
            'model' => 'gpt-3.5-turbo',
            'temperature' => 0.1,
            'messages' => $messagesArray,
            "functions" => AiFunctionsCall::getFunctionsDef(),
        ];
    }

}
