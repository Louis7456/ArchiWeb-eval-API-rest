<?php

class Verification {
    private static $authenticatedUserId = null;

    // Définit l'ID de l'utilisateur connecté
    public static function setAuthenticatedUserId($userId) {
        self::$authenticatedUserId = $userId;
    }

    // Récupère l'ID de l'utilisateur connecté
    public static function getAuthenticatedUserId() {
        return self::$authenticatedUserId;
    }

    // Validation des données pour la création ou mise à jour de ticket
    public static function validateTicketData($data) {
        if (!is_array($data) || empty($data['titre']) || empty($data['description']) || empty($data['categorie']) || empty($data['priorite'])) {
            http_response_code(400);
            echo json_encode(["error" => "Données incomplètes ou invalides. Les champs 'titre', 'description', 'categorie' et 'priorite' sont requis."]);
            exit();
        }
    }
}