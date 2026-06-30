<?php

class Database {
    // Configuration des identifiants 
    private $host = "localhost";
    private $db_name = "ticket-api"; 
    private $username = "root";
    private $password = ""; 
    private $charset = "utf8mb4";
    public $conn;

    // Méthode pour obtenir la connexion à la base de données
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     
                PDO::ATTR_EMULATE_PREPARES => false,              
        } catch (PDOException $exception) {
            http_response_code(500);
            echo json_encode([
                "error" => "Erreur de connexion à la base de données",
                "message" => $exception->getMessage()
            ]);
            exit();
        }

        return $this->conn;
    }
}