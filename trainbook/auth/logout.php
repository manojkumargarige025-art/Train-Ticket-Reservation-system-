<?php
session_start();
require_once '../config/database.php';

// Log the logout activity
if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'User Logout', 'User logged out successfully');
} elseif (isset($_SESSION['admin_id'])) {
    logActivity($_SESSION['admin_id'], 'Admin Logout', 'Admin logged out successfully');
}

// Destroy all session data
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to home page
redirectWithMessage('../index.php', 'You have been logged out successfully.', 'success');
?>
