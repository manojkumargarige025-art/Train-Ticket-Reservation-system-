<?php
echo "<h2>Login Test</h2>";

$email = 'deena.dhayalan@cmr.edu.in';
$password = '1505';

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=trainbook", "root", "");
    
    // Test admin login
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<h3>Admin Found:</h3>";
        echo "<p>Name: " . $admin['first_name'] . " " . $admin['last_name'] . "</p>";
        echo "<p>Email: " . $admin['email'] . "</p>";
        echo "<p>Password Hash: " . $admin['password'] . "</p>";
        
        // Test password verification
        $md5_test = md5($password) === $admin['password'];
        echo "<p>MD5 Test: " . ($md5_test ? "✅ PASS" : "❌ FAIL") . "</p>";
        
        if ($md5_test) {
            echo "<h3>✅ LOGIN SUCCESS!</h3>";
            echo "<p>You can now login with:</p>";
            echo "<p><strong>Email:</strong> deena.dhayalan@cmr.edu.in</p>";
            echo "<p><strong>Password:</strong> 1505</p>";
        } else {
            echo "<h3>❌ LOGIN FAILED</h3>";
        }
    } else {
        echo "<h3>❌ Admin not found</h3>";
    }
    
} catch(PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
}
?>
