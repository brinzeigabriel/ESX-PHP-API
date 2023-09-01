<?php

class Auth
{

    public function __construct(private UserGateway $user_gateway)
    {
        
    }

    public function authenticateAPIKey(): bool
    {
        //https://www.php.net/manual/en/reserved.variables.server.php
        // custom header X-API-key with value for $_SERVER
        if(empty($_SERVER["HTTP_X_API_KEY"])){
            http_response_code(400);
            echo json_encode(["message" => "API KEY is missing"]);
            return false;
        } 

        $api_key = $_SERVER["HTTP_X_API_KEY"];

        
        if ($this->user_gateway->getByAPIKey($api_key) === false){
            http_response_code(401);
            echo json_encode(["message" => "API KEY is invalid"]);
            exit;
        }

        return true;
    }
}