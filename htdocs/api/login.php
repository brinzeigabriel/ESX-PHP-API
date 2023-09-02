<?php
/*
Acest cod primeste datele de autentificare sub forma de JSON printr-o cerere POST
verifica si autentifica utilizatorul in baza de date
genereaza token de reimprospatare JWT si il stocheaza in baza de date
-- securizare conexiune --
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

//verificam daca exista username si parola in data
if ( ! array_key_exists("username", $data) ||
     ! array_key_exists("password", $data)) {

    http_response_code(400);
    echo json_encode(["message" => "Missing login credentials"]);
    exit;
}

// initializarea unei instante / obiect Database catre baza de date
$database = new Database($_ENV["DB_HOST"],
                        $_ENV["DB_NAME"],
                        $_ENV["DB_USER"],
                        $_ENV["DB_PASS"]);

// initializarea unei instante / obiect UserGateway care se va ocupa
// de accesul datelor catre baza de date
$user_gateway = new UserGateway($database);

// Se incearca obtinerea datelor utilizatorului din DB dupa username
$user = $user_gateway->getByUsername($data["username"]);

// verificarea daca am introdus corect datele
if($user === false){
    http_response_code(401);
    echo json_encode(["message" => "Invalid login credentials"]);
    exit;
}

// verificarea daca am introdus corect datele
if(!password_verify($data["password"],$user["password_hash"]))
{
    http_response_code(401);
    echo json_encode(["message" => "Invalid login credentials"]);
    exit;
}

// initializarea unei instante / obiect JWTCodec care se va ocupa 
// de codificarea / decodificarea tokenului JWT
$codec = new JWTCodec($_ENV["SECRET_KEY"]);

// includere fisier token 
require __DIR__ . "/tokens.php";

// initializarea unei instante / obiect RefreshTokenGateway care se va ocupa 
// de operatiuni legate de token-ul de reimprospatare
$refresh_token_gateway = new RefreshTokenGateway($database,$_ENV["SECRET_KEY"]);

// se apeleaza metoda create ce creaza un nou token de reimprospatare la fiecare login
$refresh_token_gateway->create($refresh_token,$refresh_token_expiry);