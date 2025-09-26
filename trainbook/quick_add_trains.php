<?php
require_once 'config/database.php';

try {
    $db = getDB();
    
    // Add some Chennai to Bangalore trains for the next 30 days
    $chennaiToBangaloreTrains = [
        ['RB001', 'Chennai Express', 'Chennai Central', 'Bangalore', '06:00:00', '12:00:00', 120, 1500.00],
        ['RB002', 'Shatabdi Express', 'Chennai Central', 'Bangalore', '08:30:00', '14:30:00', 80, 1200.00],
        ['RB003', 'Mail Express', 'Chennai Central', 'Bangalore', '14:00:00', '20:00:00', 150, 800.00],
        ['RB004', 'Superfast Express', 'Chennai Central', 'Bangalore', '18:00:00', '00:00:00', 100, 1000.00],
    ];
    
    $addedCount = 0;
    
    for ($day = 1; $day <= 30; $day++) {
        $journeyDate = date('Y-m-d', strtotime("+$day days"));
        
        foreach ($chennaiToBangaloreTrains as $train) {
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
                    throw $e;
                }
            }
        }
    }
    
    echo "✅ Successfully added $addedCount Chennai to Bangalore trains for the next 30 days!<br>";
    echo "🚂 You can now search for trains from Chennai Central to Bangalore<br>";
    echo "📅 Try searching for any date in the next 30 days<br>";
    echo "<br><a href='user/search_trains.php'>Go to Search Trains</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
