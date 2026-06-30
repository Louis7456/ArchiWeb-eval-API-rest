require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../middleware/verification.php';

class TicketController {
    private $ticketModel;
    public function __construct() {
        $this->ticketModel = new TicketModel();
    }
    public function index() {
        $tickets = $this->ticketModel->getAll();
        http_response_code(200);
        echo json_encode($tickets);
    }
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);
        Verification::validateTicketData($data);
        $user_id = Verification::getAuthenticatedUserId();;
        if ($this->ticketModel->create($data, $user_id)) {
            http_response_code(201);
            echo json_encode(["message" => "Ticket créé avec succès !"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Impossible de créer le ticket."]);
        }
    }
}