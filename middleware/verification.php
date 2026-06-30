public static function validateTicketData($data) {
        if (empty($data['titre']) || empty($data['description']) || empty($data['categorie']) || empty($data['priorite'])) {
            http_response_code(400);
            echo json_encode(["error" => "Données incomplètes."]);
            exit();
        }
    }
    // 1. Récupérer tous les en-têtes HTTP de la requête
        $headers = getallheaders();

        // 2. Vérifier si le header "Authorization" existe
        if (!isset($headers['Authorization'])) {
            http_response_code(401); // Unauthorized
            echo json_encode(["error" => "Accès refusé. Token d'authentification manquant."]);
            exit();
        }
        // Le header ressemble généralement à : "Bearer TOKEN_SECRET_ETUDIANT_1"
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
        // 3. Extraction de l'ID (Ici on vérifie si le token commence bien par notre clé)
        if (strpos($token, 'TOKEN_SECRET_ETUDIANT_') === 0) {
            // On extrait ce qu'il y a après "TOKEN_SECRET_ETUDIANT_"
            $userId = (int)str_replace('TOKEN_SECRET_ETUDIANT_', '', $token);
            
            if ($userId > 0) {
                return $userId; // On retourne le VRAI ID utilisateur trouvé !
            }
        }
        // Si le token est invalide ou corrompu
        http_response_code(401);
        echo json_encode(["error" => "Token invalide ou expiré."]);
        exit();