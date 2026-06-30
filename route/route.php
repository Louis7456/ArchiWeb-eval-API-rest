<?php
// route/route.php - Gestionnaire de routes de l'API

function routeRequest($db, $method, $uriParts, $isLoginRoute, $isRegisterRoute) {
    if ($isLoginRoute) {
        $userController = new UserController($db);
        $userController->login();
    } elseif ($isRegisterRoute) {
        $userController = new UserController($db);
        $userController->register();
    } elseif (!empty($uriParts[0]) && $uriParts[0] === 'tickets') {
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
}
