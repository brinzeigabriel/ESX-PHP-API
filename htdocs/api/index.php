<?php
//enable variable type checking
declare(strict_types=1); 

require __DIR__ . "/bootstrap.php";

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[2];

$id = $parts[3] ?? null;

if ($resource != "persoana") {
    http_response_code(404);
    exit;
}

$database = new Database($_ENV["DB_HOST"],$_ENV["DB_NAME"],$_ENV["DB_USER"],$_ENV["DB_PASS"]);

$user_gateway = new UserGateway($database);

$auth = new Auth($user_gateway);

if(!$auth->authenticateAPIKey()){
    exit;
}

$persoana_gateway = new PersoanaGateway($database);
$controller = new PersoanaController($persoana_gateway); // clasa incarcata automat

$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
