<?php

//autoloader ce include automat tot ce este in /src
require dirname(__DIR__) . "/vendor/autoload.php"; 

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv -> load();

header("Content-type: application/json; charset=UTF-8");