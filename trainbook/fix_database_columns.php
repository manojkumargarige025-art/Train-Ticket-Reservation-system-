<?php
require_once 'config/database.php';

try {
    $db = getDB();
    
    echo "<h2>Fixing Database Columns...</h2>";
    
    // Add missing columns for real-time tracking
    $alterQueries = [
        "ALTER TABLE trains ADD COLUMN current_location VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE trains ADD COLUMN current_status ENUM('boarding', 'departed', 'in_transit', 'delayed', 'arrived', 'cancelled') DEFAULT 'boarding'",
        "ALTER TABLE trains ADD COLUMN delay_minutes INT DEFAULT 0",
        "ALTER TABLE trains ADD COLUMN last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
        "ALTER TABLE trains ADD COLUMN progress_percentage DECIMAL(5,2) DEFAULT 0.00"
    ];
    
    $successCount = 0;
    foreach ($alterQueries as $query) {
        try {
            $db->exec($query);
            echo "✅ " . substr($query, 0, 50) . "...<br>";
            $successCount++;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "⚠️ Column already exists: " . substr($query, 0, 50) . "...<br>";
            } else {
                echo "❌ Error: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "<br><strong>Added $successCount new columns successfully!</strong><br><br>";
    
    // Now add some basic train data
    echo "<h3>Adding Basic Train Data...</h3>";
    
    $basicTrains = [
        // Chennai to Bangalore
        ['RB001', 'Chennai Express', 'Chennai Central', 'Bangalore', '06:00:00', '12:00:00', 120, 1500.00],
        ['RB002', 'Shatabdi Express', 'Chennai Central', 'Bangalore', '08:30:00', '14:30:00', 80, 1200.00],
        ['RB003', 'Mail Express', 'Chennai Central', 'Bangalore', '14:00:00', '20:00:00', 150, 800.00],
        ['RB004', 'Superfast Express', 'Chennai Central', 'Bangalore', '18:00:00', '00:00:00', 100, 1000.00],
        
        // Mumbai to Delhi
        ['RB005', 'Mumbai Rajdhani', 'Mumbai Central', 'New Delhi', '08:00:00', '20:00:00', 120, 2500.00],
        ['RB006', 'Duronto Express', 'Mumbai Central', 'New Delhi', '14:00:00', '06:00:00', 150, 2000.00],
        
        // Delhi to Mumbai
        ['RB007', 'Delhi Rajdhani', 'New Delhi', 'Mumbai Central', '10:30:00', '22:30:00', 120, 2500.00],
        ['RB008', 'Sampark Kranti', 'New Delhi', 'Mumbai Central', '16:00:00', '08:00:00', 180, 2200.00],
        
        // Bangalore to Mumbai
        ['RB009', 'Bangalore Express', 'Bangalore', 'Mumbai Central', '07:00:00', '19:00:00', 100, 1800.00],
        ['RB010', 'Karnataka Express', 'Bangalore', 'Mumbai Central', '15:00:00', '07:00:00', 120, 1600.00],
    ];
    
    $addedCount = 0;
    
    for ($day = 1; $day <= 30; $day++) {
        $journeyDate = date('Y-m-d', strtotime("+$day days"));
        
        foreach ($basicTrains as $train) {
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
    echo "🚂 Trains added for Chennai-Bangalore, Mumbai-Delhi, Delhi-Mumbai, Bangalore-Mumbai<br>";
    echo "📅 All trains are available for the next 30 days<br><br>";
    
    echo "<h3>🎉 Database Fixed Successfully!</h3>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Go to <a href='user/search_trains.php'>Search Trains</a> to book tickets</li>";
    echo "<li>Go to <a href='admin/generate_redbus_data.php'>Generate More Data</a> for full RedBus experience</li>";
    echo "<li>Search for Chennai to Bangalore on any future date</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
