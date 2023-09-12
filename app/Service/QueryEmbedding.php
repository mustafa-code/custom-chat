<?php

namespace App\Service;

use Exception;
use Illuminate\Support\Facades\Log;
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

    public function askQuestion($context, $question, $messages, $function = null)
    {
        $params = $this->getParams($context, $question, $messages, $function);
        return OpenAi::chat()->create($params);
    }

    private function getParams($context, $question, $messages, $function = null){
        $system_template = "
        Use the following pieces of context to answer the users question. 
        If you don't know the answer, just say that you don't know, don't try to make up an answer.
        when user give you his name call sayHello function.
        ----------------
        {context}
        ";
        $system_prompt = str_replace("{context}", $context, $system_template);

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
            "functions" => [
                [
                    "name" => "sayHello",
                    "description" => "Get called when the user write his name",
                    "parameters" => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'description' => 'The user\'s name',
                            ],
                        ],
                    ]
                ],
            ]
        ];
    }
}
