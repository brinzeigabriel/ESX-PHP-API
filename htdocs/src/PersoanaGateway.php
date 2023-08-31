<?php

class PersoanaGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT *
                FROM persoana
                Order BY id";
        
        $stmt = $this->conn->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(string $id): array | false
    {
        $sql = "SELECT *
                FROM persoana
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql); // avoiding sql injection 

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): string
    {
        
        $sql = "INSERT INTO persoana (CNP, Nume, Prenume, Oras, Tara, Data_de_nastere)
                VALUES (:cnp, :nume, :prenume, :oras, :tara, :data_nasterii)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":cnp", $data["cnp"], PDO::PARAM_STR);
        $stmt->bindValue(":nume", $data["nume"], PDO::PARAM_STR);
        $stmt->bindValue(":prenume", $data["prenume"], PDO::PARAM_STR);

        if(empty($data["oras"])){
            $stmt->bindValue(":oras", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":oras", $data["oras"], PDO::PARAM_STR);
        }

        if(empty($data["tara"])){
            $stmt->bindValue(":tara", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":tara", $data["tara"], PDO::PARAM_STR);
        }
        
        $stmt->bindValue(":data_nasterii", $data["data_nasterii"], PDO::PARAM_STR);

        $stmt->execute();
        
        return $this->conn->lastInsertId();
    }

    public function update(string $id, array $data): int
    {
        $fields = []; //campurile coloanelor din baza de date

        if(!empty($data["cnp"]))
        {
        $fields["cnp"] = [
            $data["cnp"],
            PDO::PARAM_STR
        ];
        }

        if(!empty($data["nume"]))
        {
        $fields["nume"] = [
            $data["nume"],
            PDO::PARAM_STR
        ];
        }

        if(!empty($data["prenume"]))
        {
        $fields["prenume"] = [
            $data["prenume"],
            PDO::PARAM_STR
        ];
        }

        if(!empty($data["oras"]))
        {
        $fields["oras"] = [
            $data["oras"],
            PDO::PARAM_STR
        ];
        }
        
        if(!empty($data["tara"]))
        {
        $fields["tara"] = [
            $data["tara"],
            PDO::PARAM_STR
        ];
        }

        if(!empty($data["data_nasterii"]))
        {
        $fields["data_de_nastere"] = [
            $data["data_nasterii"],
            PDO::PARAM_STR
        ];
        }

        if(empty($fields)){
            return 0;
        }else{
            $sets = array_map(function($value) {
                return "$value = :$value";
            },array_keys($fields));
    
            $sql = "UPDATE persoana "
                    ."SET ".implode(",",$sets)
                    ." WHERE id = :id";
    
            $stmt = $this->conn->prepare($sql);                    
            
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            
            foreach($fields as $name => $values){
                $stmt->bindValue(":$name",$values[0],$values[1]);
            }

            $stmt->execute();

            return $stmt->rowCount();
        }
    }

    public function delete(string $id): int
    {
        $sql = "DELETE FROM persoana
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);                    
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}