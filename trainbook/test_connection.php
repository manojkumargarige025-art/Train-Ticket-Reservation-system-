<?php
/**
 * Database Connection Test
 * This file will help diagnose connection issues
 */

echo "<h2>Database Connection Test</h2>";

// Test 1: Check if MySQL is running
echo "<h3>1. Testing MySQL Connection...</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;port=3307", "root", "");
    echo "✅ MySQL is running on localhost:3307<br>";
} catch (PDOException $e) {
    echo "❌ MySQL connection failed: " . $e->getMessage() . "<br>";
    echo "<strong>Solution:</strong> Start MySQL service in XAMPP Control Panel<br><br>";
}

// Test 2: Check if database exists
echo "<h3>2. Checking Database...</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;port=3307", "root", "");
    $stmt = $pdo->query("SHOW DATABASES LIKE 'trainbook'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Database 'trainbook' exists<br>";
    } else {
        echo "❌ Database 'trainbook' does not exist<br>";
        echo "<strong>Solution:</strong> Import database_setup.sql in phpMyAdmin<br><br>";
    }
} catch (PDOException $e) {
    echo "❌ Cannot check database: " . $e->getMessage() . "<br>";
}

// Test 3: Check if tables exist
echo "<h3>3. Checking Tables...</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=trainbook", "root", "");
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✅ Tables found: " . implode(", ", $tables) . "<br>";
    } else {
        echo "❌ No tables found in database<br>";
        echo "<strong>Solution:</strong> Import database_setup.sql in phpMyAdmin<br><br>";
    }
} catch (PDOException $e) {
    echo "❌ Cannot check tables: " . $e->getMessage() . "<br>";
}

// Test 4: Check XAMPP services
echo "<h3>4. XAMPP Services Check...</h3>";
echo "Please verify in XAMPP Control Panel:<br>";
echo "• Apache: Should be running (green)<br>";
echo "• MySQL: Should be running (green)<br>";
echo "• Port 3306: Should be free<br><br>";

// Test 5: Alternative connection methods
echo "<h3>5. Alternative Solutions...</h3>";
echo "If MySQL won't start, try:<br>";
echo "1. Run XAMPP as Administrator<br>";
echo "2. Check if port 3306 is blocked<br>";
echo "3. Restart your computer<br>";
echo "4. Check Windows Services for conflicting MySQL<br><br>";

echo "<h3>6. Quick Fix Commands...</h3>";
echo "Open Command Prompt as Administrator and run:<br>";
echo "<code>net stop mysql</code><br>";
echo "<code>net stop w3svc</code><br>";
echo "Then start XAMPP services again.<br>";

?>
