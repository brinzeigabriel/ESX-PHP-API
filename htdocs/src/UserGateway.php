<?php

//Aceasta clasa se ocupa de accesul si interogarea datelor utilizatorilor.
class UserGateway
{
    private PDO $conn;
    
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    
    // preluare de informatii din tabela user despre utilizatorul ce foloseste 
    // cheia api introdusa in header
    public function getByAPIKey(string $key): array | false
    {
        $sql = "SELECT *
                FROM user
                WHERE api_key = :api_key";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":api_key", $key, PDO::PARAM_STR);
        
        $stmt->execute();
        
        //obtinerea rezultatului unei interogari sql sub forma de vector asociat
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUsername(string $username): array | false
    {
        $sql = "SELECT *
                FROM user
                WHERE username = :username";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":username", $username, PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByID(int $id): array | false
    {
        $sql = "SELECT *
                FROM user
                WHERE id = :id";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}