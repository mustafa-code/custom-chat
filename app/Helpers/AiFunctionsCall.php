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
}
