<?php
require_once 'config/database.php';

echo "<h2>🚂 Adding Trains for 2025 Dates</h2>";

try {
    $db = getDB();
    
    // Clear any existing 2025 trains first
    $db->exec("DELETE FROM trains WHERE journey_date >= '2025-01-01'");
    echo "🧹 Cleared existing 2025 trains<br>";
    
    // Add trains for the next 60 days (2025)
    $trains = [
        ['RB001', 'Chennai Express', 'Chennai Central', 'Bangalore', '06:00:00', '12:00:00', 120, 1500.00],
        ['RB002', 'Shatabdi Express', 'Chennai Central', 'Bangalore', '08:30:00', '14:30:00', 80, 1200.00],
        ['RB003', 'Mail Express', 'Chennai Central', 'Bangalore', '14:00:00', '20:00:00', 150, 800.00],
        ['RB004', 'Superfast Express', 'Chennai Central', 'Bangalore', '18:00:00', '00:00:00', 100, 1000.00],
        ['RB005', 'Mumbai Rajdhani', 'Mumbai Central', 'New Delhi', '08:00:00', '20:00:00', 120, 2500.00],
        ['RB006', 'Delhi Rajdhani', 'New Delhi', 'Mumbai Central', '10:30:00', '22:30:00', 120, 2500.00],
        ['RB007', 'Bangalore Express', 'Bangalore', 'Mumbai Central', '07:00:00', '19:00:00', 100, 1800.00],
        ['RB008', 'Karnataka Express', 'Bangalore', 'Chennai Central', '09:00:00', '15:00:00', 120, 1200.00],
        ['RB009', 'Delhi Shatabdi', 'New Delhi', 'Bangalore', '06:00:00', '18:00:00', 80, 2000.00],
        ['RB010', 'Mumbai Express', 'Mumbai Central', 'Chennai Central', '10:00:00', '22:00:00', 100, 1800.00],
    ];
    
    $addedCount = 0;
    
    // Add trains for next 60 days starting from today
    for ($day = 1; $day <= 60; $day++) {
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
    
    echo "✅ Successfully added $addedCount trains for the next 60 days!<br>";
    
    // Verify the fix
    echo "<h3>Verifying the fix</h3>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE journey_date >= CURDATE()");
    $count = $stmt->fetch()['count'];
    echo "📊 Total future trains: $count<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE source_station LIKE '%Chennai%' AND destination_station LIKE '%Bangalore%' AND journey_date >= CURDATE()");
    $chennaiCount = $stmt->fetch()['count'];
    echo "🚂 Chennai to Bangalore trains (future): $chennaiCount<br>";
    
    // Test specific date
    $testDate = '2025-09-27';
    $stmt = $db->prepare("
        SELECT COUNT(*) as count FROM trains 
        WHERE source_station LIKE ? 
        AND destination_station LIKE ? 
        AND journey_date = ? 
        AND status = 'active' 
        AND available_seats > 0
    ");
    $stmt->execute(["%Chennai%", "%Bangalore%", $testDate]);
    $testCount = $stmt->fetch()['count'];
    echo "🔍 Trains for $testDate: $testCount<br>";
    
    echo "<br><h2>🎉 Success!</h2>";
    echo "<p>Now go to <a href='user/search_trains.php' target='_blank'>Search Trains</a> and search for Chennai to Bangalore on September 27, 2025!</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
