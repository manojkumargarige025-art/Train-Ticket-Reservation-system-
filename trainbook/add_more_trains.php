<?php
require_once 'config/database.php';

echo "<h2>🚂 Adding More Trains - RedBus Style</h2>";

try {
    $db = getDB();
    
    // First, let's see how many trains we currently have
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains");
    $current_count = $stmt->fetch()['count'];
    echo "<p>Current trains in database: <strong>$current_count</strong></p>";
    
    // Major Indian train routes with multiple trains per day
    $routes = [
        // Delhi routes
        ['Delhi', 'Mumbai', 'Rajdhani Express', '12001', '06:00', '14:30', 500, 450, 2500],
        ['Delhi', 'Mumbai', 'Shatabdi Express', '12002', '08:00', '16:30', 400, 380, 2200],
        ['Delhi', 'Mumbai', 'Duronto Express', '12213', '10:00', '18:30', 600, 550, 2000],
        ['Delhi', 'Mumbai', 'Garib Rath', '12214', '12:00', '20:30', 800, 750, 1200],
        ['Delhi', 'Mumbai', 'Jan Shatabdi', '12051', '14:00', '22:30', 350, 320, 1800],
        
        // Mumbai routes
        ['Mumbai', 'Delhi', 'Rajdhani Express', '12003', '07:00', '15:30', 500, 450, 2500],
        ['Mumbai', 'Delhi', 'Shatabdi Express', '12004', '09:00', '17:30', 400, 380, 2200],
        ['Mumbai', 'Delhi', 'Duronto Express', '12215', '11:00', '19:30', 600, 550, 2000],
        ['Mumbai', 'Delhi', 'Garib Rath', '12216', '13:00', '21:30', 800, 750, 1200],
        ['Mumbai', 'Delhi', 'Jan Shatabdi', '12052', '15:00', '23:30', 350, 320, 1800],
        
        // Chennai routes
        ['Chennai Central', 'Bangalore', 'Shatabdi Express', '12007', '06:00', '11:00', 300, 280, 1500],
        ['Chennai Central', 'Bangalore', 'Brindavan Express', '12640', '08:00', '13:00', 500, 450, 800],
        ['Chennai Central', 'Bangalore', 'Karnataka Express', '12627', '10:00', '15:00', 600, 550, 700],
        ['Chennai Central', 'Bangalore', 'Shatabdi Express', '12008', '12:00', '17:00', 300, 280, 1500],
        ['Chennai Central', 'Bangalore', 'Brindavan Express', '12641', '14:00', '19:00', 500, 450, 800],
        ['Chennai Central', 'Bangalore', 'Karnataka Express', '12628', '16:00', '21:00', 600, 550, 700],
        ['Chennai Central', 'Bangalore', 'Shatabdi Express', '12009', '18:00', '23:00', 300, 280, 1500],
        
        // Bangalore routes
        ['Bangalore', 'Chennai Central', 'Shatabdi Express', '12010', '07:00', '12:00', 300, 280, 1500],
        ['Bangalore', 'Chennai Central', 'Brindavan Express', '12642', '09:00', '14:00', 500, 450, 800],
        ['Bangalore', 'Chennai Central', 'Karnataka Express', '12629', '11:00', '16:00', 600, 550, 700],
        ['Bangalore', 'Chennai Central', 'Shatabdi Express', '12011', '13:00', '18:00', 300, 280, 1500],
        ['Bangalore', 'Chennai Central', 'Brindavan Express', '12643', '15:00', '20:00', 500, 450, 800],
        ['Bangalore', 'Chennai Central', 'Karnataka Express', '12630', '17:00', '22:00', 600, 550, 700],
        ['Bangalore', 'Chennai Central', 'Shatabdi Express', '12012', '19:00', '00:00', 300, 280, 1500],
        
        // Kolkata routes
        ['Kolkata', 'Delhi', 'Rajdhani Express', '12001', '05:00', '13:30', 500, 450, 2500],
        ['Kolkata', 'Delhi', 'Shatabdi Express', '12002', '07:00', '15:30', 400, 380, 2200],
        ['Kolkata', 'Delhi', 'Duronto Express', '12213', '09:00', '17:30', 600, 550, 2000],
        ['Kolkata', 'Mumbai', 'Howrah Express', '12801', '11:00', '19:30', 600, 550, 1800],
        ['Kolkata', 'Mumbai', 'Gitanjali Express', '12802', '13:00', '21:30', 500, 450, 1600],
        
        // Delhi to other cities
        ['Delhi', 'Kolkata', 'Rajdhani Express', '12003', '06:00', '14:30', 500, 450, 2500],
        ['Delhi', 'Kolkata', 'Shatabdi Express', '12004', '08:00', '16:30', 400, 380, 2200],
        ['Delhi', 'Bangalore', 'Rajdhani Express', '12005', '10:00', '18:30', 500, 450, 2500],
        ['Delhi', 'Bangalore', 'Shatabdi Express', '12006', '12:00', '20:30', 400, 380, 2200],
        ['Delhi', 'Chennai', 'Rajdhani Express', '12007', '14:00', '22:30', 500, 450, 2500],
        ['Delhi', 'Chennai', 'Shatabdi Express', '12008', '16:00', '00:30', 400, 380, 2200],
        
        // Mumbai to other cities
        ['Mumbai', 'Kolkata', 'Howrah Express', '12801', '07:00', '15:30', 600, 550, 1800],
        ['Mumbai', 'Kolkata', 'Gitanjali Express', '12802', '09:00', '17:30', 500, 450, 1600],
        ['Mumbai', 'Bangalore', 'Udyan Express', '12627', '11:00', '19:30', 500, 450, 1400],
        ['Mumbai', 'Bangalore', 'Karnataka Express', '12628', '13:00', '21:30', 600, 550, 1200],
        ['Mumbai', 'Chennai', 'Chennai Express', '12601', '15:00', '23:30', 500, 450, 1300],
        ['Mumbai', 'Chennai', 'Mumbai Express', '12602', '17:00', '01:30', 600, 550, 1100],
        
        // Bangalore to other cities
        ['Bangalore', 'Delhi', 'Rajdhani Express', '12009', '08:00', '16:30', 500, 450, 2500],
        ['Bangalore', 'Delhi', 'Shatabdi Express', '12010', '10:00', '18:30', 400, 380, 2200],
        ['Bangalore', 'Mumbai', 'Udyan Express', '12627', '12:00', '20:30', 500, 450, 1400],
        ['Bangalore', 'Mumbai', 'Karnataka Express', '12628', '14:00', '22:30', 600, 550, 1200],
        ['Bangalore', 'Kolkata', 'Bangalore Express', '12803', '16:00', '00:30', 500, 450, 1500],
        ['Bangalore', 'Kolkata', 'Howrah Express', '12804', '18:00', '02:30', 600, 550, 1300],
        
        // Chennai to other cities
        ['Chennai Central', 'Delhi', 'Rajdhani Express', '12011', '09:00', '17:30', 500, 450, 2500],
        ['Chennai Central', 'Delhi', 'Shatabdi Express', '12012', '11:00', '19:30', 400, 380, 2200],
        ['Chennai Central', 'Mumbai', 'Chennai Express', '12601', '13:00', '21:30', 500, 450, 1300],
        ['Chennai Central', 'Mumbai', 'Mumbai Express', '12602', '15:00', '23:30', 600, 550, 1100],
        ['Chennai Central', 'Kolkata', 'Coromandel Express', '12841', '17:00', '01:30', 500, 450, 1400],
        ['Chennai Central', 'Kolkata', 'Howrah Express', '12842', '19:00', '03:30', 600, 550, 1200],
        
        // Kolkata to other cities
        ['Kolkata', 'Delhi', 'Rajdhani Express', '12013', '10:00', '18:30', 500, 450, 2500],
        ['Kolkata', 'Delhi', 'Shatabdi Express', '12014', '12:00', '20:30', 400, 380, 2200],
        ['Kolkata', 'Mumbai', 'Howrah Express', '12801', '14:00', '22:30', 600, 550, 1800],
        ['Kolkata', 'Mumbai', 'Gitanjali Express', '12802', '16:00', '00:30', 500, 450, 1600],
        ['Kolkata', 'Bangalore', 'Bangalore Express', '12803', '18:00', '02:30', 500, 450, 1500],
        ['Kolkata', 'Bangalore', 'Howrah Express', '12804', '20:00', '04:30', 600, 550, 1300],
        ['Kolkata', 'Chennai', 'Coromandel Express', '12841', '22:00', '06:30', 500, 450, 1400],
        ['Kolkata', 'Chennai', 'Howrah Express', '12842', '00:00', '08:30', 600, 550, 1200],
    ];
    
    // Add trains for the next 60 days
    $added_count = 0;
    $start_date = new DateTime();
    
    for ($day = 0; $day < 60; $day++) {
        $current_date = clone $start_date;
        $current_date->add(new DateInterval('P' . $day . 'D'));
        $date_str = $current_date->format('Y-m-d');
        
        foreach ($routes as $route) {
            [$source, $destination, $train_name, $train_number, $departure_time, $arrival_time, $total_seats, $available_seats, $fare] = $route;
            
            // Add some variation to departure times
            $departure_hour = (int)substr($departure_time, 0, 2);
            $departure_hour = ($departure_hour + $day) % 24;
            $departure_time = sprintf('%02d:%s', $departure_hour, substr($departure_time, 3));
            
            // Add some variation to fares
            $fare_variation = rand(-200, 200);
            $fare = max(500, $fare + $fare_variation);
            
            // Add some variation to available seats
            $seat_variation = rand(-50, 50);
            $available_seats = max(0, min($total_seats, $available_seats + $seat_variation));
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO trains (train_number, train_name, source_station, destination_station, 
                                      departure_time, arrival_time, total_seats, available_seats, 
                                      fare_per_seat, journey_date, status, current_location, current_status, 
                                      delay_minutes, progress_percentage) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, 'active', 0, 0.00)
                ");
                
                $stmt->execute([
                    $train_number,
                    $train_name,
                    $source,
                    $destination,
                    $departure_time,
                    $arrival_time,
                    $total_seats,
                    $available_seats,
                    $fare,
                    $date_str,
                    $source
                ]);
                
                $added_count++;
                
            } catch (Exception $e) {
                // Skip if train already exists for this date
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "Error adding train: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    // Check final count
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains");
    $final_count = $stmt->fetch()['count'];
    
    echo "<h3>✅ Success!</h3>";
    echo "<p>Added <strong>$added_count</strong> new trains</p>";
    echo "<p>Total trains in database: <strong>$final_count</strong></p>";
    
    // Show sample of what we added
    echo "<h3>Sample Trains Added:</h3>";
    $stmt = $db->query("
        SELECT train_name, train_number, source_station, destination_station, 
               departure_time, journey_date, fare_per_seat, available_seats
        FROM trains 
        WHERE journey_date >= CURDATE() 
        ORDER BY journey_date, departure_time 
        LIMIT 10
    ");
    $sample_trains = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Train</th><th>Route</th><th>Time</th><th>Date</th><th>Fare</th><th>Seats</th></tr>";
    foreach ($sample_trains as $train) {
        echo "<tr>";
        echo "<td>" . $train['train_name'] . " (" . $train['train_number'] . ")</td>";
        echo "<td>" . $train['source_station'] . " → " . $train['destination_station'] . "</td>";
        echo "<td>" . $train['departure_time'] . "</td>";
        echo "<td>" . $train['journey_date'] . "</td>";
        echo "<td>₹" . $train['fare_per_seat'] . "</td>";
        echo "<td>" . $train['available_seats'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>🎯 Now you can:</h3>";
    echo "<p>1. Go to <a href='user/search_trains.php'>Search Trains</a></p>";
    echo "<p>2. Search for any route (Chennai to Bangalore, Delhi to Mumbai, etc.)</p>";
    echo "<p>3. You should see many more trains now!</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
