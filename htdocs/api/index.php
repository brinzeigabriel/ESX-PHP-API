<?php
//enable variable type checking
declare(strict_types=1); 

//autoloader ce include automat tot ce este in /src
require dirname(__DIR__) . "/vendor/autoload.php"; 

//ini_set("diplay_errors","On"); //enable display errors
set_exception_handler("ErrorHandler::handleException");

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[2];

$id = $parts[3] ?? null;

if ($resource != "persoana") {
    http_response_code(404);
    exit;
}

header("Content-type: application/json; charset=UTF-8");

$controller = new PersoanaController; // clasa incarcata automat

$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
