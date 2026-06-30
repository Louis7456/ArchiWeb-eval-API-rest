<?php
class UserController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // POST /login
    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->email) && !empty($data->password)) {
            $query = "SELECT api_key FROM users WHERE email = :email AND password = :password LIMIT 1";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':email', htmlspecialchars(strip_tags($data->email)));
            $stmt->bindParam(':password', htmlspecialchars(strip_tags($data->password)));
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode([
                    "message" => "Connexion réussie.",
                    "token" => $row['api_key']
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Email ou mot de passe incorrect."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Données incomplètes. 'email' et 'password' sont requis."]);
        }
    }
}
?>