<?php
/*
Acest cod are logica axata pe tokenul de refresh 
in care se sterge tokenul expirat si se creaza unul nou
*/

// verificarea stricta a tipurilor variabilelor si a functiilor php
declare(strict_types=1);

// include fisierul ce contine declaratia erorilor custom, autoloader si 
// incarcarea variabilelor de mediu al aplicatiei
require __DIR__ . "/bootstrap.php";

// verificarea cererii HTPP daca este POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    
    http_response_code(405);
    header("Allow: POST");
    exit;
}

//preluare cererii http si decodarea jsonului
$data = (array) json_decode(file_get_contents("php://input"), true);

//verificam daca exista tokenul in data
if ( ! array_key_exists("token", $data)) {

    http_response_code(400);
    echo json_encode(["message" => "missing token"]);
    exit;
}

// initializarea unei instante / obiect JWTCodec care se va ocupa 
// de codificarea / decodificarea tokenului JWT
$codec = new JWTCodec($_ENV["SECRET_KEY"]);

// incearca incarcarea si decodarea tokenului JWT
try {
    $payload = $codec->decode($data["token"]);
    
} catch (Exception) {
    
    http_response_code(400);
    echo json_encode(["message" => "invalid token"]);
    exit;
}

//se extrage identificatorul sub din payload-ul JWT header.payload.signature
$user_id = $payload["sub"];

// initializarea unei instante / obiect Database catre baza de date
$database = new Database($_ENV["DB_HOST"],
                         $_ENV["DB_NAME"],
                         $_ENV["DB_USER"],
                         $_ENV["DB_PASS"]);

// initializarea unei instante / obiect RefreshTokenGateway care se va ocupa 
// de operatiuni legate de token-ul de reimprospatare
$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

// obtinem informatii despre tokenul de improspatare din DB
$refresh_token = $refresh_token_gateway->getByToken($data["token"]);

if ($refresh_token === false) {
    
    http_response_code(400);
    echo json_encode(["message" => "invalid token (not on whitelist)"]);
    exit;
}
 
// initializarea unei instante / obiect UserGateway care se va ocupa
// de accesul datelor catre baza de date
$user_gateway = new UserGateway($database);

// obtinem informatii despre userul cu identificatorul user_id din DB
$user = $user_gateway->getByID($user_id);

if ($user === false) {
    
    http_response_code(401);
    echo json_encode(["message" => "invalid authentication"]);
    exit;
}

// includere fisier token 
require __DIR__ . "/tokens.php";

// se sterge tokenul expirat
$refresh_token_gateway->delete($data["token"]);

// si se creaza altul nou
$refresh_token_gateway->create((string)$refresh_token, $refresh_token_expiry);
