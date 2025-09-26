<?php
require_once 'config/database.php';

echo "<h2>🔍 Checking Database Status</h2>";

try {
    $db = getDB();
    
    echo "<h3>Step 1: Checking table structure</h3>";
    $stmt = $db->query("DESCRIBE trains");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    echo "<h3>Step 2: Checking existing trains</h3>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains");
    $totalCount = $stmt->fetch()['count'];
    echo "Total trains in database: $totalCount<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE journey_date >= CURDATE()");
    $futureCount = $stmt->fetch()['count'];
    echo "Future trains: $futureCount<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE source_station LIKE '%Chennai%'");
    $chennaiCount = $stmt->fetch()['count'];
    echo "Trains from Chennai: $chennaiCount<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE destination_station LIKE '%Bangalore%'");
    $bangaloreCount = $stmt->fetch()['count'];
    echo "Trains to Bangalore: $bangaloreCount<br>";
    
    echo "<h3>Step 3: Sample trains</h3>";
    $stmt = $db->query("SELECT train_number, train_name, source_station, destination_station, journey_date FROM trains ORDER BY journey_date LIMIT 10");
    $sampleTrains = $stmt->fetchAll();
    
    if (count($sampleTrains) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Train Number</th><th>Train Name</th><th>From</th><th>To</th><th>Date</th></tr>";
        foreach ($sampleTrains as $train) {
            echo "<tr>";
            echo "<td>" . $train['train_number'] . "</td>";
            echo "<td>" . $train['train_name'] . "</td>";
            echo "<td>" . $train['source_station'] . "</td>";
            echo "<td>" . $train['destination_station'] . "</td>";
            echo "<td>" . $train['journey_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "No trains found in database!<br>";
    }
    
    echo "<h3>Step 4: Testing search query</h3>";
    $testDate = date('Y-m-d', strtotime('+1 day'));
    echo "Testing search for Chennai to Bangalore on: $testDate<br>";
    
    $stmt = $db->prepare("
        SELECT * FROM trains 
        WHERE source_station LIKE ? 
        AND destination_station LIKE ? 
        AND journey_date = ? 
        AND status = 'active' 
        AND available_seats > 0
        ORDER BY departure_time ASC
    ");
    
    $stmt->execute(["%Chennai%", "%Bangalore%", $testDate]);
    $searchResults = $stmt->fetchAll();
    
    echo "Search results: " . count($searchResults) . " trains found<br>";
    
    if (count($searchResults) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Train Number</th><th>Train Name</th><th>Departure</th><th>Arrival</th><th>Seats</th><th>Fare</th></tr>";
        foreach ($searchResults as $train) {
            echo "<tr>";
            echo "<td>" . $train['train_number'] . "</td>";
            echo "<td>" . $train['train_name'] . "</td>";
            echo "<td>" . $train['departure_time'] . "</td>";
            echo "<td>" . $train['arrival_time'] . "</td>";
            echo "<td>" . $train['available_seats'] . "/" . $train['total_seats'] . "</td>";
            echo "<td>₹" . $train['fare_per_seat'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    echo "<h3>Step 5: Adding trains if none exist</h3>";
    
    if ($totalCount == 0) {
        echo "No trains found! Adding some basic trains...<br>";
        
        $basicTrains = [
            ['RB001', 'Chennai Express', 'Chennai Central', 'Bangalore', '06:00:00', '12:00:00', 120, 1500.00],
            ['RB002', 'Shatabdi Express', 'Chennai Central', 'Bangalore', '08:30:00', '14:30:00', 80, 1200.00],
            ['RB003', 'Mail Express', 'Chennai Central', 'Bangalore', '14:00:00', '20:00:00', 150, 800.00],
            ['RB004', 'Superfast Express', 'Chennai Central', 'Bangalore', '18:00:00', '00:00:00', 100, 1000.00],
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
        
        echo "✅ Added $addedCount trains!<br>";
    }
    
    echo "<br><h2>🎉 Database Check Complete!</h2>";
    echo "<p>Now go to <a href='user/search_trains.php' target='_blank'>Search Trains</a> and try searching for Chennai to Bangalore on tomorrow's date.</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
