<?php
echo "<h2>Database Tables Check</h2>";

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=trainbook", "root", "");
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tables in 'trainbook' database:</h3>";
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>✅ $table</li>";
        }
        echo "</ul>";
    } else {
        echo "❌ No tables found";
    }
    
    // Check if we can connect to the database
    echo "<h3>Database Connection Test:</h3>";
    echo "✅ Successfully connected to 'trainbook' database<br>";
    
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
