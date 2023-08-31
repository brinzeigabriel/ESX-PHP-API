<?php
//enable variable type checking
declare(strict_types=1); 

//autoloader ce include automat tot ce este in /src
require dirname(__DIR__) . "/vendor/autoload.php"; 

set_error_handler("ErrorHandler::handleError");
//ini_set("diplay_errors","On"); //enable display errors
set_exception_handler("ErrorHandler::handleException");

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv -> load();

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[2];

$id = $parts[3] ?? null;

if ($resource != "persoana") {
    http_response_code(404);
    exit;
}

header("Content-type: application/json; charset=UTF-8");

$database = new Database($_ENV["DB_HOST"],$_ENV["DB_NAME"],$_ENV["DB_USER"],$_ENV["DB_PASS"]);

$persoana_gateway = new PersoanaGateway($database);
$controller = new PersoanaController($persoana_gateway); // clasa incarcata automat

$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
