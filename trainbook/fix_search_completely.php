<?php
require_once 'config/database.php';

echo "<h2>🔧 Complete Search Fix</h2>";

try {
    $db = getDB();
    
    echo "<h3>Step 1: Checking current database status</h3>";
    
    // Check what's in the database
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains");
    $totalCount = $stmt->fetch()['count'];
    echo "Total trains: $totalCount<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE journey_date >= CURDATE()");
    $futureCount = $stmt->fetch()['count'];
    echo "Future trains: $futureCount<br>";
    
    // Check if status column exists and has values
    $stmt = $db->query("SHOW COLUMNS FROM trains LIKE 'status'");
    $statusColumn = $stmt->fetch();
    if ($statusColumn) {
        echo "Status column exists: Yes<br>";
        $stmt = $db->query("SELECT DISTINCT status FROM trains");
        $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Status values: " . implode(', ', $statuses) . "<br>";
    } else {
        echo "Status column exists: No<br>";
    }
    
    echo "<h3>Step 2: Adding status column if missing</h3>";
    
    try {
        $db->exec("ALTER TABLE trains ADD COLUMN status ENUM('active', 'cancelled', 'completed') DEFAULT 'active'");
        echo "✅ Added status column<br>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "⚠️ Status column already exists<br>";
        } else {
            echo "❌ Error adding status column: " . $e->getMessage() . "<br>";
        }
    }
    
    // Update all existing trains to have active status
    $db->exec("UPDATE trains SET status = 'active' WHERE status IS NULL OR status = ''");
    echo "✅ Updated all trains to active status<br>";
    
    echo "<h3>Step 3: Adding comprehensive train data</h3>";
    
    // Clear existing RB trains
    $db->exec("DELETE FROM trains WHERE train_number LIKE 'RB%'");
    echo "🧹 Cleared existing RB trains<br>";
    
    // Add comprehensive train data
    $routes = [
        // Chennai to Bangalore
        ['Chennai Central', 'Bangalore', 'Chennai Express', '06:00:00', 120, 1500.00],
        ['Chennai Central', 'Bangalore', 'Shatabdi Express', '08:30:00', 80, 1200.00],
        ['Chennai Central', 'Bangalore', 'Mail Express', '14:00:00', 150, 800.00],
        ['Chennai Central', 'Bangalore', 'Superfast Express', '18:00:00', 100, 1000.00],
        
        // Bangalore to Chennai
        ['Bangalore', 'Chennai Central', 'Karnataka Express', '09:00:00', 120, 1200.00],
        ['Bangalore', 'Chennai Central', 'Bangalore Express', '15:00:00', 100, 1000.00],
        
        // Mumbai to Delhi
        ['Mumbai Central', 'New Delhi', 'Mumbai Rajdhani', '08:00:00', 120, 2500.00],
        ['Mumbai Central', 'New Delhi', 'Duronto Express', '14:00:00', 150, 2000.00],
        
        // Delhi to Mumbai
        ['New Delhi', 'Mumbai Central', 'Delhi Rajdhani', '10:30:00', 120, 2500.00],
        ['New Delhi', 'Mumbai Central', 'Sampark Kranti', '16:00:00', 180, 2200.00],
        
        // Delhi to Bangalore
        ['New Delhi', 'Bangalore', 'Delhi Shatabdi', '06:00:00', 80, 2000.00],
        ['New Delhi', 'Bangalore', 'Karnataka Express', '12:00:00', 120, 1800.00],
        
        // Mumbai to Bangalore
        ['Mumbai Central', 'Bangalore', 'Mumbai Express', '10:00:00', 100, 1800.00],
        ['Mumbai Central', 'Bangalore', 'Karnataka Express', '16:00:00', 120, 1600.00],
        
        // Chennai to Mumbai
        ['Chennai Central', 'Mumbai Central', 'Chennai Express', '07:00:00', 100, 1800.00],
        ['Chennai Central', 'Mumbai Central', 'Mail Express', '13:00:00', 120, 1600.00],
    ];
    
    $addedCount = 0;
    
    // Add trains for next 60 days
    for ($day = 1; $day <= 60; $day++) {
        $journeyDate = date('Y-m-d', strtotime("+$day days"));
        
        foreach ($routes as $route) {
            $source = $route[0];
            $destination = $route[1];
            $trainName = $route[2];
            $departureTime = $route[3];
            $totalSeats = $route[4];
            $baseFare = $route[5];
            
            $availableSeats = $totalSeats - rand(0, min(20, $totalSeats - 10));
            $fareVariation = rand(-100, 200);
            $finalFare = max(500, $baseFare + $fareVariation);
            
            // Calculate arrival time (6-12 hours journey)
            $departureTimestamp = strtotime($journeyDate . ' ' . $departureTime);
            $journeyHours = rand(6, 12);
            $arrivalTimestamp = $departureTimestamp + ($journeyHours * 3600);
            $arrivalTime = date('H:i:s', $arrivalTimestamp);
            
            // Generate unique train number
            $trainNumber = 'RB' . str_pad($day, 2, '0', STR_PAD_LEFT) . str_pad($addedCount % 100, 2, '0', STR_PAD_LEFT);
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO trains (train_number, train_name, source_station, destination_station, 
                                      departure_time, arrival_time, total_seats, available_seats, 
                                      fare_per_seat, journey_date, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ");
                
                $stmt->execute([
                    $trainNumber,
                    $trainName,
                    $source,
                    $destination,
                    $departureTime,
                    $arrivalTime,
                    $totalSeats,
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
    
    echo "✅ Added $addedCount trains for the next 60 days!<br>";
    
    echo "<h3>Step 4: Testing search functionality</h3>";
    
    // Test the exact search that the user is doing
    $testSource = 'chennai';
    $testDestination = 'Bangalore';
    $testDate = '2025-09-27';
    
    echo "Testing search: $testSource to $testDestination on $testDate<br>";
    
    $stmt = $db->prepare("
        SELECT * FROM trains 
        WHERE source_station LIKE ? 
        AND destination_station LIKE ? 
        AND journey_date = ? 
        AND status = 'active' 
        AND available_seats > 0
        ORDER BY departure_time ASC
    ");
    
    $stmt->execute(["%$testSource%", "%$testDestination%", $testDate]);
    $searchResults = $stmt->fetchAll();
    
    echo "Search results: " . count($searchResults) . " trains found<br>";
    
    if (count($searchResults) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Train Number</th><th>Train Name</th><th>From</th><th>To</th><th>Departure</th><th>Arrival</th><th>Seats</th><th>Fare</th></tr>";
        foreach ($searchResults as $train) {
            echo "<tr>";
            echo "<td>" . $train['train_number'] . "</td>";
            echo "<td>" . $train['train_name'] . "</td>";
            echo "<td>" . $train['source_station'] . "</td>";
            echo "<td>" . $train['destination_station'] . "</td>";
            echo "<td>" . $train['departure_time'] . "</td>";
            echo "<td>" . $train['arrival_time'] . "</td>";
            echo "<td>" . $train['available_seats'] . "/" . $train['total_seats'] . "</td>";
            echo "<td>₹" . $train['fare_per_seat'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "❌ No trains found! Let me check what's wrong...<br>";
        
        // Debug: Check what trains exist for that date
        $stmt = $db->prepare("SELECT * FROM trains WHERE journey_date = ?");
        $stmt->execute([$testDate]);
        $dateTrains = $stmt->fetchAll();
        echo "Trains for $testDate: " . count($dateTrains) . "<br>";
        
        if (count($dateTrains) > 0) {
            echo "Sample train: " . $dateTrains[0]['train_name'] . " from " . $dateTrains[0]['source_station'] . " to " . $dateTrains[0]['destination_station'] . "<br>";
        }
        
        // Check case sensitivity
        $stmt = $db->prepare("SELECT * FROM trains WHERE LOWER(source_station) LIKE ? AND LOWER(destination_station) LIKE ? AND journey_date = ?");
        $stmt->execute(["%chennai%", "%bangalore%", $testDate]);
        $caseResults = $stmt->fetchAll();
        echo "Case-insensitive search results: " . count($caseResults) . "<br>";
    }
    
    echo "<h3>Step 5: Final verification</h3>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE journey_date >= CURDATE()");
    $finalCount = $stmt->fetch()['count'];
    echo "📊 Total future trains: $finalCount<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains WHERE LOWER(source_station) LIKE '%chennai%' AND LOWER(destination_station) LIKE '%bangalore%' AND journey_date >= CURDATE()");
    $chennaiCount = $stmt->fetch()['count'];
    echo "🚂 Chennai to Bangalore trains: $chennaiCount<br>";
    
    echo "<br><h2>🎉 Fix Complete!</h2>";
    echo "<p>Now go to <a href='user/search_trains.php' target='_blank'>Search Trains</a> and search for Chennai to Bangalore on September 27, 2025!</p>";
    echo "<p>If it still doesn't work, try searching for 'Chennai Central' to 'Bangalore' (with exact case).</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
