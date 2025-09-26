<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    $db = getDB();
    
    // Get all trains with real-time status
    $stmt = $db->query("
        SELECT 
            id, train_number, train_name, source_station, destination_station,
            departure_time, arrival_time, journey_date, current_location, 
            current_status, delay_minutes, progress_percentage, last_updated,
            available_seats, total_seats, fare_per_seat, status
        FROM trains 
        WHERE journey_date >= CURDATE() - INTERVAL 1 DAY
        ORDER BY journey_date, departure_time
    ");
    
    $trains = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Simulate real-time updates for demonstration
    $updatedTrains = [];
    foreach ($trains as $train) {
        // Simulate some real-time changes
        $currentTime = time();
        $departureTime = strtotime($train['journey_date'] . ' ' . $train['departure_time']);
        $arrivalTime = strtotime($train['journey_date'] . ' ' . $train['arrival_time']);
        
        // Update progress based on current time
        if ($currentTime < $departureTime) {
            $train['current_status'] = 'boarding';
            $train['progress_percentage'] = 0;
        } elseif ($currentTime > $arrivalTime) {
            $train['current_status'] = 'arrived';
            $train['progress_percentage'] = 100;
        } else {
            $train['current_status'] = 'in_transit';
            $totalDuration = $arrivalTime - $departureTime;
            $elapsed = $currentTime - $departureTime;
            $train['progress_percentage'] = min(100, max(0, ($elapsed / $totalDuration) * 100));
        }
        
        // Add some random delays for demonstration
        if (rand(1, 10) === 1) {
            $train['delay_minutes'] = rand(5, 30);
            $train['current_status'] = 'delayed';
        }
        
        // Update last_updated timestamp
        $train['last_updated'] = date('Y-m-d H:i:s');
        
        $updatedTrains[] = $train;
    }
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'total_trains' => count($updatedTrains),
        'trains' => $updatedTrains
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>
