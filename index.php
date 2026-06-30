<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config/Database.php';
require_once 'models/TicketModel.php';
require_once 'controller/TicketController.php';
require_once 'controller/UserController.php';
require_once 'middleware/verification.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$uri = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
if (empty($uri)) {
    // Support pour le serveur de développement intégré de PHP (sans .htaccess)
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = trim($requestUri, '/');
}
$uriParts = explode('/', $uri);

// Détection de la route de connexion et d'inscription
$isLoginRoute = ($uriParts[0] === 'login' && $method === 'POST');
$isRegisterRoute = ($uriParts[0] === 'register' && $method === 'POST');

// 1. SÉCURITÉ (Sauf pour le login, register et la méthode pré-vol OPTIONS)
if (!$isLoginRoute && !$isRegisterRoute && $method !== 'OPTIONS') {
    // Normalisation des en-têtes HTTP de manière insensible à la casse
    $headers = apache_request_headers();
    $apiKey = null;
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            $apiKey = str_replace('Bearer ', '', $value);
            break;
        }
    }

    $query = "SELECT id FROM users WHERE api_key = :api_key LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':api_key', $apiKey);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        http_response_code(401);
        echo json_encode(["message" => "Accès non autorisé. Veuillez vous connecter pour obtenir un token."]);
        exit();
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    Verification::setAuthenticatedUserId((int)$user['id']);
}

// 2. ROUTEUR
require_once 'route/route.php';
routeRequest($db, $method, $uriParts, $isLoginRoute, $isRegisterRoute);
?>