<?php

require __DIR__ . "/vendor/autoload.php";

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    $database = new Database($_ENV["DB_HOST"],
                             $_ENV["DB_NAME"],
                             $_ENV["DB_USER"],
                             $_ENV["DB_PASS"]);
                             
    $conn = $database->getConnection();

    if (!empty($_POST["name"]) && !empty($_POST["username"]) && !empty($_POST["password"])) 
    {
        // Check if username already exists
        $username = $_POST["username"];
        $checkSql = "SELECT COUNT(*) FROM user WHERE username = :username";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindValue(":username", $username, PDO::PARAM_STR);
        $checkStmt->execute();

        $existingCount = $checkStmt->fetchColumn();

        if ($existingCount > 0) {
            echo "Username already exists. Please choose a different username.";
        } else {
            $sql = "INSERT INTO user (name, username, password_hash, api_key)
                    VALUES (:name, :username, :password_hash, :api_key)";
                    
            $stmt = $conn->prepare($sql);
            
            $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
            $api_key = bin2hex(random_bytes(16)); //creare key
            
            $stmt->bindValue(":name", $_POST["name"], PDO::PARAM_STR);
            $stmt->bindValue(":username", $_POST["username"], PDO::PARAM_STR);
            $stmt->bindValue(":password_hash", $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(":api_key", $api_key, PDO::PARAM_STR);
            
            $stmt->execute();
            
            echo "Thank you for registering. Your API key is ", $api_key;
            exit;
        }
    } else { 
        echo "Please fill out the fields.";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@latest/css/pico.min.css">
</head>
<body>
    
    <main class="container">
    
        <h1>Register</h1>
        
        <form method="post">
            
            <label for="name">
                Name
                <input name="name" id="name">
            </label>
            
            <label for="username">
                Username
                <input name="username" id="username">
            </label>
            
            <label for="password">
                Password
                <input type="password" name="password" id="password">
            </label>
            
            <button>Register</button>
        </form>
    
    </main>
    
</body>
</html>
        
        
        
        
        
        
        
        
        
        
        
        
        