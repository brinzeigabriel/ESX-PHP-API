<?php
// Acest cod este folosit pentru curatarea bazei de date de tokenurile expirate

//verificare stricta a tipurilor argumentelor functiilor si valorilor returnate
declare(strict_types=1); 

// composer incarca clase si dependinte PHP
require __DIR__ . "/vendor/autoload.php";

// incarcare variabilele mediului de programare .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//creare conexiune baza de date
$database = new Database($_ENV["DB_HOST"],
                         $_ENV["DB_NAME"],
                         $_ENV["DB_USER"],
                         $_ENV["DB_PASS"]);

// preia tokenul refresh din baza de date si il sterge
$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

echo $refresh_token_gateway->deleteExpired(), "\n";