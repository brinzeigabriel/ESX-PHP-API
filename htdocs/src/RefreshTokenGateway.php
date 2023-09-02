<?php

//Clasa ce se ocupa de operatiuni legate de token-urile de reimprospatare
class RefreshTokenGateway
{
    private PDO $conn;
    private string $key;
    
    public function __construct(Database $database, string $key)
    {
        $this->conn = $database->getConnection();
        $this->key = $key;
    }
    
    public function create(string $token, int $expiry): bool
    {
        /* 
        Acest hash_HMAC este utilizat pentru a verifica integritatea datelor 
        si pentru a asigura ca acestea nu au fost modificate
        Rezultatul acestei linii va contine hash-ul tokenului calculat cu cheia secreta
        $this->key folosind algoritmul SHA256. = more secured than just api_key
        */
        $hash = hash_hmac("sha256", $token, $this->key);

        
        $sql = "INSERT INTO refresh_token (token_hash, expires_at)
                VALUES (:token_hash, :expires_at)";
                
        $stmt = $this->conn->prepare($sql); //prepare este pentru executarea securizata a comenzii sql
        
        //binding the values for the query
        $stmt->bindValue(":token_hash", $hash, PDO::PARAM_STR);
        $stmt->bindValue(":expires_at", $expiry, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function delete(string $token): int
    {
        $hash = hash_hmac("sha256", $token, $this->key);
        
        $sql = "DELETE FROM refresh_token
                WHERE token_hash = :token_hash";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":token_hash", $hash, PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }
    
    public function getByToken(string $token): array | false
    {
        $hash = hash_hmac("sha256", $token, $this->key);
        
        $sql = "SELECT *
                FROM refresh_token
                WHERE token_hash = :token_hash";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":token_hash", $hash, PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function deleteExpired(): int
    {
        $sql = "DELETE FROM refresh_token
                WHERE expires_at < UNIX_TIMESTAMP()";
            
        $stmt = $this->conn->query($sql);
        
        return $stmt->rowCount();
    }
}