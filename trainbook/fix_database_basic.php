<?php
require_once 'config/database.php';

echo "<h2>🔧 Fixing Database - Basic Approach</h2>";

try {
    $db = getDB();
    
    echo "<h3>Step 1: Adding missing columns (if they don't exist)</h3>";
    
    // Try to add columns, but don't fail if they already exist
    $alterQueries = [
        "ALTER TABLE trains ADD COLUMN current_location VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE trains ADD COLUMN current_status ENUM('boarding', 'departed', 'in_transit', 'delayed', 'arrived', 'cancelled') DEFAULT 'boarding'",
        "ALTER TABLE trains ADD COLUMN delay_minutes INT DEFAULT 0",
        "ALTER TABLE trains ADD COLUMN last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
        "ALTER TABLE trains ADD COLUMN progress_percentage DECIMAL(5,2) DEFAULT 0.00"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $db->exec($query);
            echo "✅ Added: " . substr($query, 0, 50) . "...<br>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "⚠️ Already exists: " . substr($query, 0, 50) . "...<br>";
            } else {
                echo "❌ Error adding column: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "<br><h3>Step 2: Adding trains WITHOUT new columns (basic approach)</h3>";
    
    // Clear any existing RB trains first
    try {
        $db->exec("DELETE FROM trains WHERE train_number LIKE 'RB%'");
        echo "🧹 Cleared existing RB trains<br>";
    } catch (Exception $e) {
        echo "⚠️ Could not clear existing trains: " . $e->getMessage() . "<br>";
    }
    
    // Add trains using only the original columns
    $trains = [
        ['RB001', 'Chennai Express', 'Chennai Central', 'Bangalore', '06:00:00', '12:00:00', 120, 1500.00],
        ['RB002', 'Shatabdi Express', 'Chennai Central', 'Bangalore', '08:30:00', '14:30:00', 80, 1200.00],
        ['RB003', 'Mail Express', 'Chennai Central', 'Bangalore', '14:00:00', '20:00:00', 150, 800.00],
        ['RB004', 'Superfast Express', 'Chennai Central', 'Bangalore', '18:00:00', '00:00:00', 100, 1000.00],
        ['RB005', 'Mumbai Rajdhani', 'Mumbai Central', 'New Delhi', '08:00:00', '20:00:00', 120, 2500.00],
        ['RB006', 'Delhi Rajdhani', 'New Delhi', 'Mumbai Central', '10:30:00', '22:30:00', 120, 2500.00],
        ['RB007', 'Bangalore Express', 'Bangalore', 'Mumbai Central', '07:00:00', '19:00:00', 100, 1800.00],
        ['RB008', 'Karnataka Express', 'Bangalore', 'Chennai Central', '09:00:00', '15:00:00', 120, 1200.00],
    ];
    
    $addedCount = 0;
    
    for ($day = 1; $day <= 30; $day++) {
        $journeyDate = date('Y-m-d', strtotime("+$day days"));
        
        foreach ($trains as $train) {
            $availableSeats = $train[6] - rand(0, min(20, $train[6] - 10));
            $fareVariation = rand(-100, 200);
            $finalFare = max(500, $train[7] + $fareVariation);
            
            // Calculate arrival time
            $departureTimestamp = strtotime($journeyDate . ' ' . $train[4]);
            $arrivalTimestamp = $departureTimestamp + (6 * 3600); // 6 hours journey
            $arrivalTime = date('H:i:s', $arrivalTimestamp);
            
            try {
                // Use only the original columns that definitely exist
                $stmt = $db->prepare("
                    INSERT INTO trains (train_number, train_name, source_station, destination_station, 
                                      departure_time, arrival_time, total_seats, available_seats, 
                                      fare_per_seat, journey_date, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ");
                
                $stmt->execute([
                    $train[0] . '_' . $day,
                    $train[1],
                    $train[2],
                    $train[3],
                    $train[4],
                    $arrivalTime,
                    $train[6],
                    $availableSeats,
                    $finalFare,
                    $journeyDate
                ]);
                
                $addedCount++;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "❌ Error adding train: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    echo "✅ Successfully added $addedCount trains for the next 30 days!<br>";
    
    // Verify the fix
    echo "<h3>Step 3: Verifying the fix</h3>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE journey_date >= CURDATE()");
    $count = $stmt->fetch()['count'];
    echo "📊 Total trains available: $count<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE source_station LIKE '%Chennai%' AND destination_station LIKE '%Bangalore%'");
    $chennaiCount = $stmt->fetch()['count'];
    echo "🚂 Chennai to Bangalore trains: $chennaiCount<br>";
    
    // Test a simple query
    echo "<h3>Step 4: Testing search query</h3>";
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
    
    $testDate = date('Y-m-d', strtotime('+1 day'));
    $stmt->execute(["%Chennai%", "%Bangalore%", $testDate]);
    $testResults = $stmt->fetchAll();
    
    echo "🔍 Test search results for tomorrow: " . count($testResults) . " trains found<br>";
    
    if (count($testResults) > 0) {
        echo "✅ Search is working! First train: " . $testResults[0]['train_name'] . "<br>";
    }
    
    echo "<br><h2>🎉 Database Fixed Successfully!</h2>";
    echo "<p><strong>You can now:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Go to <a href='user/search_trains.php' target='_blank'>Search Trains</a> to book tickets</li>";
    echo "<li>✅ Search for Chennai to Bangalore on any future date</li>";
    echo "<li>✅ Search for Mumbai to Delhi, Delhi to Mumbai, etc.</li>";
    echo "</ul>";
    
    echo "<br><p><strong>Test it now:</strong> Go to Search Trains and search for Chennai to Bangalore on tomorrow's date!</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
    echo "<br><br>Please check your database connection and try again.";
}
?>
