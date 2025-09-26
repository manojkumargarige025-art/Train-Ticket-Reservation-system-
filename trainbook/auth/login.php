<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $user_type = isset($_GET['type']) && $_GET['type'] === 'admin' ? 'admin' : 'user';
    
    if (empty($email) || empty($password)) {
        redirectWithMessage('../index.php', 'Please fill in all fields.', 'error');
    }
    
    if (!validateEmail($email)) {
        redirectWithMessage('../index.php', 'Please enter a valid email address.', 'error');
    }
    
    try {
        $db = getDB();
        
        if ($user_type === 'admin') {
            // Admin login
            $stmt = $db->prepare("SELECT id, first_name, last_name, email, password FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && (md5($password) === $admin['password'] || verifyPassword($password, $admin['password']))) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['user_type'] = 'admin';
                
                logActivity($admin['id'], 'Admin Login', 'Admin logged in successfully');
                redirectWithMessage('../admin/dashboard.php', 'Welcome back, ' . $admin['first_name'] . '!', 'success');
            } else {
                redirectWithMessage('../index.php', 'Invalid admin credentials.', 'error');
            }
        } else {
            // User login
            $stmt = $db->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && (md5($password) === $user['password'] || verifyPassword($password, $user['password']))) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = 'user';
                
                logActivity($user['id'], 'User Login', 'User logged in successfully');
                redirectWithMessage('../user/dashboard.php', 'Welcome back, ' . $user['first_name'] . '!', 'success');
            } else {
                redirectWithMessage('../index.php', 'Invalid user credentials.', 'error');
            }
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        redirectWithMessage('../index.php', 'An error occurred. Please try again.', 'error');
    }
} else {
    redirectWithMessage('../index.php', 'Invalid request method.', 'error');
}
?>
