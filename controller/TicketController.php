<?php
require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../middleware/verification.php';

class TicketController {
    private $ticketModel;

    public function __construct($db = null) {
        $this->ticketModel = new TicketModel($db);
    }

    // Récupère tous les tickets
    public function getAllTickets() {
        $tickets = $this->ticketModel->getAll();
        http_response_code(200);
        echo json_encode($tickets);
    }

    // Récupère un ticket
    public function getTicket($id) {
        $ticket = $this->ticketModel->getById($id);
        if ($ticket) {
            http_response_code(200);
            echo json_encode($ticket);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Ticket non trouvé."]);
        }
    }

    // Crée un ticket
    public function createTicket() {
        $data = json_decode(file_get_contents("php://input"), true);
        Verification::validateTicketData($data);
        $user_id = Verification::getAuthenticatedUserId();
        
        if ($this->ticketModel->create($data, $user_id)) {
            http_response_code(201);
            echo json_encode(["message" => "Ticket créé avec succès !"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Impossible de créer le ticket."]);
        }
    }

    // Met à jour le statut d'un ticket
    public function updateStatus($id) {
        $ticket = $this->ticketModel->getById($id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(["message" => "Ticket non trouvé."]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $status = isset($data['statut']) ? $data['statut'] : (isset($data['status']) ? $data['status'] : null);

        if (!$status) {
            http_response_code(400);
            echo json_encode(["error" => "Le champ 'statut' est requis."]);
            return;
        }

        $allowedStatuses = ['Nouveau', 'En cours', 'Résolu'];
        if (!in_array($status, $allowedStatuses)) {
            http_response_code(400);
            echo json_encode(["error" => "Statut invalide. Les statuts possibles sont: " . implode(', ', $allowedStatuses)]);
            return;
        }

        if ($this->ticketModel->updateStatus($id, $status)) {
            http_response_code(200);
            echo json_encode([
                "message" => "Statut du ticket mis à jour avec succès !",
                "id" => $id,
                "statut" => $status
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Impossible de modifier le statut."]);
        }
    }
}