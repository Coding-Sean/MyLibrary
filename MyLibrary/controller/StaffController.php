<?php
// filepath: c:\xampp\htdocs\MyLibrary\controller\StaffController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../model/StaffModel.php';

class StaffController extends BaseController {
    private $staffModel;

    public function __construct() {
        parent::__construct();
        $this->requireAuth('Staff');
        $this->staffModel = new StaffModel($this->db);
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        $actions = [
            'borrow' => 'borrowBook',
            'return' => 'returnBook',
            'add_penalty' => 'addPenalty',
            'clearance' => 'processClearance',
            'reject_reservation' => 'rejectReservation',
            'get_user_details' => 'getUserDetails',
            'waive_penalty' => 'waivePenalty',
            'mark_paid' => 'markPaid'
        ];

        if (isset($actions[$action])) {
            $this->{$actions[$action]}();
        } else {
            $this->redirect('../view/Staff_Dashboard.php');
        }
    }

    private function borrowBook() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('../view/Staff_Dashboard.php');
        }

        $user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
        $book_id = filter_var($_POST['book_id'] ?? 0, FILTER_VALIDATE_INT);
        $reserve_id = filter_var($_POST['reserve_id'] ?? 0, FILTER_VALIDATE_INT);

        $user = $this->staffModel->getUserDetails($user_id);
        if (!$user) {
            $this->redirect('../view/Staff_Dashboard.php', 'User not found', 'error');
        }

        $eligibility = $this->staffModel->canUserBorrow($user_id, $user['role']);
        if (!$eligibility['can_borrow']) {
            $this->redirect('../view/Staff_Dashboard.php', $eligibility['reason'], 'error');
        }

        $result = $this->staffModel->borrowBook($user_id, $book_id, $reserve_id > 0 ? $reserve_id : null);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    private function returnBook() {
        $borrow_id = filter_var($_GET['borrow_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->staffModel->returnBook($borrow_id);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    private function addPenalty() {
        $borrow_id = filter_var($_GET['borrow_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->staffModel->addPenalty($borrow_id);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    private function processClearance() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('../view/Staff_Dashboard.php');
        }

        $user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
        $semester = htmlspecialchars(trim($_POST['semester'] ?? ''), ENT_QUOTES, 'UTF-8');

        if (empty($semester)) {
            $this->redirect('../view/Staff_Dashboard.php', 'Semester is required', 'error');
        }

        $result = $this->staffModel->processClearance($user_id, $semester);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    private function rejectReservation() {
        $reserve_id = filter_var($_GET['reserve_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->staffModel->rejectReservation($reserve_id);
        
        $message = $result ? 'Reservation rejected' : 'Failed to reject reservation';
        $this->redirect('../view/Staff_Dashboard.php', $message, $result ? 'success' : 'error');
    }

    private function getUserDetails() {
        $user_id = filter_var($_GET['user_id'] ?? 0, FILTER_VALIDATE_INT);
        
        $user = $this->staffModel->getUserDetails($user_id);
        $borrowedBooks = $this->staffModel->getUserBorrowedBooks($user_id);
        $penalties = $this->staffModel->getUserPenalties($user_id);
        $eligibility = $this->staffModel->canUserBorrow($user_id, $user['role']);

        header('Content-Type: application/json');
        echo json_encode([
            'user' => $user,
            'borrowed_books' => $borrowedBooks,
            'penalties' => $penalties,
            'can_borrow' => $eligibility['can_borrow'],
            'borrow_reason' => $eligibility['reason']
        ]);
        exit;
    }

    private function waivePenalty() {
        $penalty_id = filter_var($_GET['penalty_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->staffModel->waivePenalty($penalty_id);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    private function markPaid() {
        $penalty_id = filter_var($_GET['penalty_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->staffModel->markPenaltyAsPaid($penalty_id);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }
}

$controller = new StaffController();
$controller->handleRequest();
?>