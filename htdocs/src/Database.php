<?php
/*
Gestionarea conexiunii la baza de date.
In aceasta clasa utilizam logica de caching a conexiunii pentru a evita conexiunile 
multiple si autentificarea pentru toate requesturile 
atat timp cat utilizatorul este logat 
astfel imbunatatim si performanta api-ului
*/

class Database
{

    private ?PDO $conn = null; 
    /*CACHE DB CONNECTION
    pentru a evita conexiunile multiple cand folosim ClassGateway.php
    in care se cere conectarea la DB $this->conn = $database->getConnection();
    */

    public function __construct(
        private string $host,
        private string $name,
        private string $user,
        private string $password
    ) {
    }
    
    public function getConnection(): PDO
    {
        if($this->conn === null){
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";
            
            //caching DB connection in conn
            $this->conn =  new PDO($dsn, $this->user, $this->password, [
                //https://www.php.net/manual/en/pdo.setattribute.php
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false, 
                PDO::ATTR_STRINGIFY_FETCHES => false // making sure the numeric values aren't converted to strings when returning data
                
            ]);
        }
        return $this->conn;
    }
}