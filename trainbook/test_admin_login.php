<?php
session_start();
require_once 'config/database.php';

echo "<h2>🔍 Admin Login Test</h2>";

// Check if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    echo "✅ Admin is already logged in<br>";
    echo "Admin ID: " . $_SESSION['admin_id'] . "<br>";
    echo "Admin Name: " . $_SESSION['admin_name'] . "<br>";
    echo "<a href='admin/dashboard.php'>Go to Admin Dashboard</a><br>";
    exit;
}

// Check if admin table exists and has data
try {
    $db = getDB();
    
    // Check if admins table exists
    $stmt = $db->query("SHOW TABLES LIKE 'admins'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Admins table doesn't exist. Creating it...<br>";
        
        // Create admins table
        $sql = "
        CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->exec($sql);
        echo "✅ Admins table created<br>";
    }
    
    // Check if admin exists
    $stmt = $db->query("SELECT COUNT(*) as count FROM admins");
    $admin_count = $stmt->fetch()['count'];
    
    if ($admin_count == 0) {
        echo "❌ No admin found. Creating default admin...<br>";
        
        // Create default admin
        $stmt = $db->prepare("
            INSERT INTO admins (username, email, password, first_name, last_name) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'admin',
            'admin@trainbook.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            'Admin',
            'User'
        ]);
        echo "✅ Default admin created<br>";
    }
    
    // Show admin details
    $stmt = $db->query("SELECT * FROM admins LIMIT 1");
    $admin = $stmt->fetch();
    
    echo "<h3>Admin Account Details:</h3>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Name: " . $admin['first_name'] . " " . $admin['last_name'] . "<br>";
    
    echo "<h3>Login Form:</h3>";
    echo "<form method='POST' action='admin_login_test.php'>";
    echo "<input type='hidden' name='action' value='login'>";
    echo "<p>Email: <input type='email' name='email' value='" . $admin['email'] . "' required></p>";
    echo "<p>Password: <input type='password' name='password' value='admin123' required></p>";
    echo "<p><button type='submit'>Login as Admin</button></p>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
