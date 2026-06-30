<?php
require_once __DIR__ . '/../config/Database.php';

class TicketModel {
    private $conn;
    private $table_name = "tickets";

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Récupére tous les tickets
    public function getAll() {
        $query = "SELECT id, user_id, titre, description, catégorie AS categorie, priorité AS priorite, statut, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Récupère un ticket par son ID
    public function getById($id) {
        $query = "SELECT id, user_id, titre, description, catégorie AS categorie, priorité AS priorite, statut, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Insère un nouveau ticket
    public function create($data, $user_id) {
        $query = "INSERT INTO " . $this->table_name . "
            SET titre=:titre, description=:description, catégorie=:categorie, priorité=:priorite, user_id=:user_id, statut='Nouveau', created_at=NOW()";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':titre', $data['titre']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':categorie', $data['categorie']);
        $stmt->bindParam(':priorite', $data['priorite']);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }

    // Modifie le statut d'un ticket
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET statut = :statut 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':statut', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}