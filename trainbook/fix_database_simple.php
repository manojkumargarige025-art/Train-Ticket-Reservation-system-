<?php
require_once 'config/database.php';

echo "<h2>🔧 Fixing Database Schema...</h2>";

try {
    $db = getDB();
    
    // First, let's check what columns exist
    echo "<h3>Step 1: Checking current table structure</h3>";
    $stmt = $db->query("DESCRIBE trains");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Current columns: " . implode(', ', $columns) . "<br><br>";
    
    // Add missing columns one by one
    echo "<h3>Step 2: Adding missing columns</h3>";
    
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
                echo "❌ Error: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "<br><h3>Step 3: Adding basic train data</h3>";
    
    // Clear any existing RB trains first
    $db->exec("DELETE FROM trains WHERE train_number LIKE 'RB%'");
    echo "🧹 Cleared existing RB trains<br>";
    
    // Add some basic trains
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
                $stmt = $db->prepare("
                    INSERT INTO trains (train_number, train_name, source_station, destination_station, 
                                      departure_time, arrival_time, total_seats, available_seats, 
                                      fare_per_seat, journey_date, status, current_location, current_status, 
                                      delay_minutes, progress_percentage) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, 'boarding', 0, 0.0)
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
                    $journeyDate,
                    $train[2] // current_location starts at source
                ]);
                
                $addedCount++;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    throw $e;
                }
            }
        }
    }
    
    echo "✅ Successfully added $addedCount trains for the next 30 days!<br>";
    
    // Verify the fix
    echo "<h3>Step 4: Verifying the fix</h3>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE journey_date >= CURDATE()");
    $count = $stmt->fetch()['count'];
    echo "📊 Total trains available: $count<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE source_station LIKE '%Chennai%' AND destination_station LIKE '%Bangalore%'");
    $chennaiCount = $stmt->fetch()['count'];
    echo "🚂 Chennai to Bangalore trains: $chennaiCount<br>";
    
    echo "<br><h2>🎉 Database Fixed Successfully!</h2>";
    echo "<p><strong>You can now:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Go to <a href='user/search_trains.php' target='_blank'>Search Trains</a> to book tickets</li>";
    echo "<li>✅ Search for Chennai to Bangalore on any future date</li>";
    echo "<li>✅ Search for Mumbai to Delhi, Delhi to Mumbai, etc.</li>";
    echo "<li>✅ Go to <a href='admin/generate_redbus_data.php' target='_blank'>Generate More Data</a> for full RedBus experience</li>";
    echo "</ul>";
    
    echo "<br><p><strong>Test it now:</strong> Go to Search Trains and search for Chennai to Bangalore on tomorrow's date!</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
    echo "<br><br>Please check your database connection and try again.";
}
?>
