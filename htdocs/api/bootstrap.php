<?php

//autoloader ce include automat tot ce este in /src
require dirname(__DIR__) . "/vendor/autoload.php"; 

//erori custom
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

//incarcarea variabilelor proiectului
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv -> load();

//seteaza raspunsul generat de server sub forma de json
header("Content-type: application/json; charset=UTF-8");