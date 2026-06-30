<?php
// =========================================================================
// CONFIGURATION DES ENTÊTES (Toutes les réponses de l'API seront en JSON)
// =========================================================================
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Gestion des requêtes de pré-vérification (OPTIONS) pour les clients HTTP
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// =========================================================================
// ANALYSE DE L'URL ET DE LA MÉTHODE HTTP
// =========================================================================
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// On nettoie l'URL pour enlever les sous-dossiers éventuels (ex: /ticket-api/tickets -> tickets)
// Sépare l'URL par les "/" pour analyser les segments
$uriSegments = explode('/', trim($uri, '/'));

// Si vous testez en local dans un sous-dossier (ex: localhost/ticket-api/tickets), 
// décommentez et ajustez la ligne suivante pour ignorer le nom du dossier :
// array_shift($uriSegments); 

$resource = isset($uriSegments[0]) ? $uriSegments[0] : null;
$id = isset($uriSegments[1]) ? (int)$uriSegments[1] : null;
$action = isset($uriSegments[2]) ? $uriSegments[2] : null;

// =========================================================================
// INCLUSION DES CONTRÔLEURS
// =========================================================================
require_once 'controllers/TicketController.php';
require_once 'controllers/AuthController.php';

$ticketController = new TicketController();
$authController = new AuthController();

// =========================================================================
// ROUTAGE & SÉCURITÉ
// =========================================================================

// Route publique : Connexion (Permet d'obtenir un accès)
if ($resource === 'login' && $method === 'POST') {
    $authController->login();
    exit();
}

// Vérification de la sécurité pour TOUTES les autres routes (Contrainte du sujet)
// Le contrôleur d'authentification va vérifier la présence d'un token/session valide.
if (!$authController->isAuthenticated()) {
    http_response_code(401); // Unauthorized
    echo json_encode([
        "error" => "Authentification requise.",
        "message" => "Vous devez vous connecter pour réaliser des actions sur les tickets."
    ]);
    exit();
}

// Routes protégées : Gestion des Tickets
if ($resource === 'tickets') {
    
    switch ($method) {
        case 'GET':
            if ($id === null) {
                // GET /tickets -> Liste complète
                $ticketController->index();
            } else {
                // GET /tickets/:id -> Détail d'un ticket
                $ticketController->show($id);
            }
            break;
            
        case 'POST':
            if ($id === null) {
                // POST /tickets -> Création d'un ticket
                $ticketController->store();
            } elseif ($id !== null && $action === 'status') {
                // POST /tickets/:id/status -> Modification du statut
                $ticketController->updateStatus($id);
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Requête POST invalide ou mal formée."]);
            }
            break;
            
        default:
            http_response_code(405); // Method Not Allowed
            echo json_encode(["error" => "Méthode HTTP non autorisée pour cette route."]);
            break;
    }
    
} else {
    // Si la ressource demandée n'est ni /login ni /tickets
    http_response_code(404);
    echo json_encode(["error" => "Route introuvable ou point d'entrée incorrect."]);
}