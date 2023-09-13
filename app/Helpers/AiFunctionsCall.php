<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class AiFunctionsCall
{
    public static function registerStudent($data) {
        $response = Http::post(config("otas.url")."api/v1/createLead", [
            "api_key" => config("otas.api_key"),
            'first_name' => $data["first_name"],
            'last_name' => $data["last_name"],
            'email' => $data["email"],
            'phone' => $data["phone"],
        ]);
        return json_encode($response);
    }

    public static function getFunctionsDef(){
        return [
            [
                "name" => "registerStudent",
                "description" => "Get called when the user provieded lead info",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "first_name" => [
                            "type" => "string",
                            "description" => "The student's first name"
                        ],
                        "last_name" => [
                            "type" => "string",
                            "description" => "The student's last name"
                        ],
                        "phone" => [
                            "type" => "string",
                            "description" => "The student's phone"
                        ],
                        "email" => [
                            "type" => "string",
                            "description" => "The student's email"
                        ],
                        "lang" => [
                            "type" => "string",
                            "description" => "The user's conversation language as a lang code like en, ar, or tr"
                        ],
                        "chat_id" => [
                            "type" => "string",
                            "description" => "The chat id provided in System role."
                        ],
                    ],
                    "required" => ["first_name", "last_name", "phone", "email", "chat_id"]
                ]
            ]
        ];
    }
}
