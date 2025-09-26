<?php
echo "<h2>Admin Account Verification</h2>";

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=trainbook", "root", "");
    
    // Check if Deena's account exists
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute(['deena.dhayalan@cmr.edu.in']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<h3>✅ Admin Account Found!</h3>";
        echo "<p><strong>Name:</strong> " . $admin['first_name'] . " " . $admin['last_name'] . "</p>";
        echo "<p><strong>Email:</strong> " . $admin['email'] . "</p>";
        echo "<p><strong>Phone:</strong> " . $admin['phone'] . "</p>";
        echo "<p><strong>Password Hash:</strong> " . $admin['password'] . "</p>";
        echo "<p><strong>Created:</strong> " . $admin['created_at'] . "</p>";
        
        // Test password verification
        $test_password = '1505';
        $stored_hash = $admin['password'];
        
        if (md5($test_password) === $stored_hash) {
            echo "<p><strong>✅ Password Verification:</strong> Password '1505' matches!</p>";
        } else {
            echo "<p><strong>❌ Password Verification:</strong> Password '1505' does NOT match!</p>";
        }
        
    } else {
        echo "<h3>❌ Admin Account Not Found</h3>";
    }
    
    // Show all admin accounts
    echo "<h3>All Admin Accounts:</h3>";
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, phone, created_at FROM admins");
    $admins = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Created</th></tr>";
    foreach ($admins as $admin) {
        echo "<tr>";
        echo "<td>" . $admin['id'] . "</td>";
        echo "<td>" . $admin['first_name'] . " " . $admin['last_name'] . "</td>";
        echo "<td>" . $admin['email'] . "</td>";
        echo "<td>" . $admin['phone'] . "</td>";
        echo "<td>" . $admin['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
}
?>
