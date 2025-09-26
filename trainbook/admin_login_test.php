<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
            $_SESSION['admin_email'] = $admin['email'];
            
            echo "<h2>✅ Login Successful!</h2>";
            echo "<p>Welcome, " . $admin['first_name'] . "!</p>";
            echo "<p><a href='admin/dashboard.php'>Go to Admin Dashboard</a></p>";
            echo "<p><a href='admin/bookings.php'>Go to Manage Bookings</a></p>";
        } else {
            echo "<h2>❌ Login Failed</h2>";
            echo "<p>Invalid email or password.</p>";
            echo "<p><a href='test_admin_login.php'>Try Again</a></p>";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
} else {
    header("Location: test_admin_login.php");
    exit;
}
?>
