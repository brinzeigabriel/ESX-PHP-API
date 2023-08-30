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

    public function get(string $id): array | false
    {
        $sql = "SELECT *
                FROM persoana
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql); // avoiding sql injection 

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}