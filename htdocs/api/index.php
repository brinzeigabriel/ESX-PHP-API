<?php
/* 
acest cod gestioneaza cererile utilizatorului pentru resursa persoana,
va autentifica si verifica utilizatorul si ofera capabilitatea acestuia
de a interactiona cu baza de date pusa la dispozitie
*/


//enable variable type checking
declare(strict_types=1); 

// include fisierul ce contine declaratia erorilor custom, autoloader si 
// incarcarea variabilelor de mediu al aplicatiei
require __DIR__ . "/bootstrap.php";

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); // extragem url requestului

$parts = explode("/", $path); //split the url with /

$resource = $parts[2]; // extrage al treilea segment din url localhost/api/persoana

$id = $parts[3] ?? null; // extrage al patrulea segment din url (:id) si atribuie null in 
                        // cazul in care id-ul nu este trimis prin request

if ($resource != "persoana") { // verifica ca am introdus in url /persoana
    http_response_code(404);
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

// initializarea unei instante / obiect JWTCodec care se va ocupa 
// de codificarea / decodificarea tokenului JWT
$codec = new JWTCodec($_ENV["SECRET_KEY"]);

// initializarea unei instante / obiect AUTH care se va ocupa
// de autentificarea utilizatorului si a tokenului
$auth = new Auth($user_gateway,$codec);

//incercam autentificarea userului prin token de acces
if(!$auth->authenticateAccessToken()){ 
    exit;
}

// initializarea unei instante / obiect PersoanaGateway care se va ocupa
// de datele legate de persoane din DB
$persoana_gateway = new PersoanaGateway($database);

// clase incarcate automat datorita autoloader-ului fara a mai fi nevoie 
// includerea fisierului din care fac parte

// initializarea unei instante / obiect PersoanaController care se va ocupa
// de proceresele cererilor legate de resursa persoana
$controller = new PersoanaController($persoana_gateway); 

// se va apela metodele aferente requestului transmis
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
