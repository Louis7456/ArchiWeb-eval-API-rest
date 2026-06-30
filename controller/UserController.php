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
            
            $stmt->bindValue(':email', htmlspecialchars(strip_tags($data->email)));
            $stmt->bindValue(':password', htmlspecialchars(strip_tags($data->password)));
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

    // POST /register
    public function register() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->email) && !empty($data->password)) {
            $email = htmlspecialchars(strip_tags($data->email));
            $password = htmlspecialchars(strip_tags($data->password));

            // Vérifier si l'email existe déjà
            $checkQuery = "SELECT id FROM users WHERE email = :email LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(':email', $email);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(["message" => "Cet email est déjà utilisé."]);
                return;
            }

            // Générer un api_key unique pour cet utilisateur
            $apiKey = "token_" . bin2hex(random_bytes(16));

            // Insérer le nouvel utilisateur
            $query = "INSERT INTO users SET email = :email, password = :password, api_key = :api_key";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':password', $password);
            $stmt->bindValue(':api_key', $apiKey);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Utilisateur créé avec succès.",
                    "email" => $email,
                    "token" => $apiKey
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Impossible de créer l'utilisateur."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Données incomplètes. 'email' et 'password' sont requis."]);
        }
    }
}
?>