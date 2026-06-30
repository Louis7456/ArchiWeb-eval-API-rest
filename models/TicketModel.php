require_once __DIR__ . '/../config/Database.php';

class TicketModel {
    private $conn;
    private $table_name = "tickets";
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($data, $user_id) {
        $query = "INSERT INTO " . $this->table_name . "
            SET titre=:titre, description=:description, categorie=:categorie, priorite=:priorite, user_id=:user_id, statut='Nouveau'";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':titre', $data['titre']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':categorie', $data['categorie']);
        $stmt->bindParam(':priorite', $data['priorite']);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }
}