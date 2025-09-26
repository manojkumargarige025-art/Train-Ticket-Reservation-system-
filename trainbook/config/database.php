<?php
/**
 * Database Configuration
 * Train Ticket Reservation System - Capstone Project
 */

class Database {
    private $host = 'localhost';
    private $port = '3306';
    private $db_name = 'trainbook';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            die("Connection failed: " . $exception->getMessage());
        }
        
        return $this->conn;
    }
}

// Helper function to get database connection
function getDB() {
    $database = new Database();
    return $database->getConnection();
}

// Helper function for secure password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Helper function for password verification
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Helper function for generating secure tokens
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Helper function for sanitizing input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Helper function for validating email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function for validating phone
function validatePhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

// Helper function for generating booking ID
function generateBookingId() {
    return 'BK' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

// Helper function for formatting currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Helper function for formatting date
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

// Helper function for formatting time
function formatTime($time) {
    return date('h:i A', strtotime($time));
}

// Helper function for calculating journey duration
function calculateDuration($departure, $arrival) {
    $departure_time = new DateTime($departure);
    $arrival_time = new DateTime($arrival);
    
    if ($arrival_time < $departure_time) {
        $arrival_time->add(new DateInterval('P1D')); // Add 1 day if arrival is next day
    }
    
    $duration = $departure_time->diff($arrival_time);
    
    $hours = $duration->h;
    $minutes = $duration->i;
    
    if ($hours > 0) {
        return $hours . 'h ' . $minutes . 'm';
    } else {
        return $minutes . 'm';
    }
}

// Helper function for checking if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
}

// Helper function for getting current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Helper function for getting current admin ID
function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

// Helper function for redirecting with message
function redirectWithMessage($url, $message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $url");
    exit();
}

// Helper function for displaying messages
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        
        $alertClass = '';
        switch($type) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
            default:
                $alertClass = 'alert-info';
        }
        
        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>";
        echo $message;
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
        echo "</div>";
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Helper function for logging activities
function logActivity($user_id, $activity, $details = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, activity, details, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $activity, $details]);
    } catch(Exception $e) {
        error_log("Log activity error: " . $e->getMessage());
    }
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kolkata');
?>
