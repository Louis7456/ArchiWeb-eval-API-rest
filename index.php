<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config/Database.php';
require_once 'models/Ticket.php';
require_once 'controllers/TicketController.php';
require_once 'controllers/UserController.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$uri = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$uriParts = explode('/', $uri);

// Détection de la route de connexion
$isLoginRoute = ($uriParts[0] === 'login' && $method === 'POST');

// 1. SÉCURITÉ (Sauf pour le login et la méthode pré-vol OPTIONS)
if (!$isLoginRoute && $method !== 'OPTIONS') {
    $headers = apache_request_headers();
    $apiKey = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

    $query = "SELECT id FROM users WHERE api_key = :api_key LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':api_key', $apiKey);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        http_response_code(401);
        echo json_encode(["message" => "Accès non autorisé. Veuillez vous connecter pour obtenir un token."]);
        exit();
    }
}

// 2. ROUTEUR
if ($isLoginRoute) {
    $userController = new UserController($db);
    $userController->login();
} elseif ($uriParts[0] === 'tickets') {
    $controller = new TicketController($db);
    $id = isset($uriParts[1]) ? (int)$uriParts[1] : null;
    $action = isset($uriParts[2]) ? $uriParts[2] : null;

    if ($method === 'GET') {
        if ($id) {
            $controller->getTicket($id);
        } else {
            $controller->getAllTickets();
        }
    } elseif ($method === 'POST') {
        if ($id && $action === 'status') {
            $controller->updateStatus($id);
        } elseif (!$id) {
            $controller->createTicket();
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Route introuvable."]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["message" => "Méthode non autorisée."]);
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint introuvable."]);
}
?>