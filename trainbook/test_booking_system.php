<?php
session_start();
require_once 'config/database.php';

echo "<h2>🔍 Train Booking System Diagnostic</h2>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $db = getDB();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check if tables exist
echo "<h3>2. Database Tables Check</h3>";
try {
    $tables = ['users', 'trains', 'bookings'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' missing<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking tables: " . $e->getMessage() . "<br>";
}

// Test 3: Check trains data
echo "<h3>3. Trains Data Check</h3>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains");
    $result = $stmt->fetch();
    echo "Total trains in database: " . $result['count'] . "<br>";
    
    if ($result['count'] > 0) {
        $stmt = $db->query("SELECT * FROM trains LIMIT 3");
        $trains = $stmt->fetchAll();
        echo "<h4>Sample trains:</h4>";
        foreach ($trains as $train) {
            echo "- " . $train['train_name'] . " (" . $train['train_number'] . ") - " . $train['source_station'] . " to " . $train['destination_station'] . " - " . $train['journey_date'] . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking trains: " . $e->getMessage() . "<br>";
}

// Test 4: Check if user files exist
echo "<h3>4. User Files Check</h3>";
$files = [
    'user/search_trains.php',
    'user/book_train.php', 
    'user/my_bookings.php',
    'user/profile.php',
    'user/dashboard.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

// Test 5: Check specific search functionality
echo "<h3>5. Search Functionality Test</h3>";
try {
    $stmt = $db->prepare("
        SELECT * FROM trains 
        WHERE source_station LIKE ? 
        AND destination_station LIKE ? 
        AND journey_date = ? 
        AND status = 'active' 
        AND available_seats > 0
        ORDER BY departure_time ASC
        LIMIT 5
    ");
    
    $stmt->execute(['%chennai%', '%bangalore%', '2025-09-27']);
    $results = $stmt->fetchAll();
    
    echo "Search results for Chennai to Bangalore on 2025-09-27: " . count($results) . " trains found<br>";
    
    if (count($results) > 0) {
        echo "<h4>Found trains:</h4>";
        foreach ($results as $train) {
            echo "- " . $train['train_name'] . " (" . $train['train_number'] . ") - " . $train['source_station'] . " to " . $train['destination_station'] . " - " . $train['journey_date'] . " - Status: " . $train['status'] . "<br>";
        }
    } else {
        echo "No trains found. Let me check what dates are available...<br>";
        
        $stmt = $db->query("SELECT DISTINCT journey_date FROM trains WHERE source_station LIKE '%chennai%' AND destination_station LIKE '%bangalore%' ORDER BY journey_date LIMIT 10");
        $dates = $stmt->fetchAll();
        
        if (count($dates) > 0) {
            echo "Available dates for Chennai to Bangalore:<br>";
            foreach ($dates as $date) {
                echo "- " . $date['journey_date'] . "<br>";
            }
        } else {
            echo "No Chennai to Bangalore routes found at all.<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Search test failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>🎯 Next Steps:</h3>";
echo "1. If database connection fails, check XAMPP MySQL service<br>";
echo "2. If tables are missing, run database_setup.sql<br>";
echo "3. If no trains found, run fix_search_completely.php<br>";
echo "4. If files are missing, they need to be created<br>";
?>
