<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    redirectWithMessage('../index.php', 'Please login as admin to access this page.', 'error');
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $db = getDB();
        
        if ($_POST['action'] === 'add_future_trains') {
            // Add trains for the next 30 days
            $trainTemplates = [
                // Popular routes with multiple daily trains
                ['TR501', 'Mumbai Rajdhani Express', 'Mumbai Central', 'New Delhi', '08:00:00', '20:00:00', 120, 2500.00],
                ['TR502', 'Delhi Rajdhani Express', 'New Delhi', 'Mumbai Central', '10:30:00', '22:30:00', 120, 2500.00],
                ['TR503', 'Shatabdi Express', 'New Delhi', 'Agra Cantt', '06:00:00', '08:30:00', 80, 1200.00],
                ['TR504', 'Shatabdi Express', 'Agra Cantt', 'New Delhi', '09:00:00', '11:30:00', 80, 1200.00],
                ['TR505', 'Duronto Express', 'Kolkata', 'Chennai Central', '14:00:00', '06:00:00', 150, 1800.00],
                ['TR506', 'Duronto Express', 'Chennai Central', 'Kolkata', '16:00:00', '08:00:00', 150, 1800.00],
                ['TR507', 'Vande Bharat Express', 'Delhi', 'Varanasi', '06:00:00', '14:00:00', 100, 1500.00],
                ['TR508', 'Vande Bharat Express', 'Varanasi', 'Delhi', '15:00:00', '23:00:00', 100, 1500.00],
                ['TR509', 'Tejas Express', 'Mumbai', 'Pune', '07:00:00', '10:00:00', 60, 800.00],
                ['TR510', 'Tejas Express', 'Pune', 'Mumbai', '11:00:00', '14:00:00', 60, 800.00],
                ['TR511', 'Garib Rath Express', 'Mumbai', 'Goa', '22:00:00', '08:00:00', 200, 600.00],
                ['TR512', 'Garib Rath Express', 'Goa', 'Mumbai', '20:00:00', '06:00:00', 200, 600.00],
                ['TR513', 'Sampark Kranti', 'Delhi', 'Bangalore', '11:00:00', '14:00:00', 180, 2200.00],
                ['TR514', 'Sampark Kranti', 'Bangalore', 'Delhi', '16:00:00', '19:00:00', 180, 2200.00],
                ['TR515', 'Jan Shatabdi', 'Mumbai', 'Ahmedabad', '15:30:00', '23:30:00', 90, 900.00],
                ['TR516', 'Jan Shatabdi', 'Ahmedabad', 'Mumbai', '06:00:00', '14:00:00', 90, 900.00],
                ['TR517', 'Gatimaan Express', 'Delhi', 'Agra', '08:10:00', '09:50:00', 40, 900.00],
                ['TR518', 'Gatimaan Express', 'Agra', 'Delhi', '10:30:00', '12:10:00', 40, 900.00],
                ['TR519', 'Express Superfast', 'Mumbai', 'Delhi', '08:00:00', '20:00:00', 100, 1500.00],
                ['TR520', 'Express Superfast', 'Delhi', 'Mumbai', '10:00:00', '22:00:00', 100, 1500.00]
            ];
            
            $addedCount = 0;
            $daysToAdd = 30; // Add trains for next 30 days
            
            for ($day = 1; $day <= $daysToAdd; $day++) {
                $journeyDate = date('Y-m-d', strtotime("+$day days"));
                
                foreach ($trainTemplates as $template) {
                    // Add some randomness to seat availability and fare
                    $availableSeats = $template[6] - rand(0, min(20, $template[6] - 10));
                    $fareVariation = rand(-100, 200);
                    $finalFare = max(500, $template[7] + $fareVariation);
                    
                    // Add some trains with different departure times for variety
                    $departureTime = $template[4];
                    if (rand(1, 3) === 1) {
                        $hour = rand(6, 22);
                        $minute = rand(0, 59);
                        $departureTime = sprintf('%02d:%02d:00', $hour, $minute);
                    }
                    
                    try {
                        $stmt = $db->prepare("
                            INSERT INTO trains (train_number, train_name, source_station, destination_station, 
                                              departure_time, arrival_time, total_seats, available_seats, 
                                              fare_per_seat, journey_date, status, current_location, current_status, 
                                              delay_minutes, progress_percentage) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, 'boarding', 0, 0.0)
                        ");
                        
                        // Calculate arrival time (simplified - just add 8-12 hours)
                        $departureTimestamp = strtotime($journeyDate . ' ' . $departureTime);
                        $arrivalTimestamp = $departureTimestamp + (rand(8, 12) * 3600);
                        $arrivalTime = date('H:i:s', $arrivalTimestamp);
                        
                        $stmt->execute([
                            $template[0] . '_' . $day, // Make train number unique per day
                            $template[1],
                            $template[2],
                            $template[3],
                            $departureTime,
                            $arrivalTime,
                            $template[6],
                            $availableSeats,
                            $finalFare,
                            $journeyDate,
                            $template[2] // current_location starts at source
                        ]);
                        
                        $addedCount++;
                    } catch (Exception $e) {
                        // Skip if train already exists for this date
                        if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                            throw $e;
                        }
                    }
                }
            }
            
            $message = "✓ Successfully added $addedCount trains for the next $daysToAdd days!";
            $messageType = 'success';
            
        } elseif ($_POST['action'] === 'add_weekly_schedule') {
            // Add a weekly recurring schedule
            $weeklyTrains = [
                ['TR601', 'Weekly Express', 'Mumbai', 'Delhi', '09:00:00', '21:00:00', 150, 2000.00],
                ['TR602', 'Weekly Express', 'Delhi', 'Mumbai', '11:00:00', '23:00:00', 150, 2000.00],
                ['TR603', 'Weekend Special', 'Mumbai', 'Goa', '18:00:00', '06:00:00', 120, 800.00],
                ['TR604', 'Weekend Special', 'Goa', 'Mumbai', '20:00:00', '08:00:00', 120, 800.00]
            ];
            
            $addedCount = 0;
            $weeksToAdd = 8; // Add for next 8 weeks
            
            for ($week = 1; $week <= $weeksToAdd; $week++) {
                for ($day = 1; $day <= 7; $day++) {
                    $journeyDate = date('Y-m-d', strtotime("+$week weeks +$day days"));
                    
                    foreach ($weeklyTrains as $template) {
                        $availableSeats = $template[6] - rand(0, min(30, $template[6] - 20));
                        $fareVariation = rand(-50, 100);
                        $finalFare = max(500, $template[7] + $fareVariation);
                        
                        try {
                            $stmt = $db->prepare("
                                INSERT INTO trains (train_number, train_name, source_station, destination_station, 
                                                  departure_time, arrival_time, total_seats, available_seats, 
                                                  fare_per_seat, journey_date, status, current_location, current_status, 
                                                  delay_minutes, progress_percentage) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, 'boarding', 0, 0.0)
                            ");
                            
                            $departureTimestamp = strtotime($journeyDate . ' ' . $template[4]);
                            $arrivalTimestamp = $departureTimestamp + (rand(10, 14) * 3600);
                            $arrivalTime = date('H:i:s', $arrivalTimestamp);
                            
                            $stmt->execute([
                                $template[0] . '_W' . $week . 'D' . $day,
                                $template[1],
                                $template[2],
                                $template[3],
                                $template[4],
                                $arrivalTime,
                                $template[6],
                                $availableSeats,
                                $finalFare,
                                $journeyDate,
                                $template[2]
                            ]);
                            
                            $addedCount++;
                        } catch (Exception $e) {
                            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                                throw $e;
                            }
                        }
                    }
                }
            }
            
            $message = "✓ Successfully added $addedCount weekly trains for the next $weeksToAdd weeks!";
            $messageType = 'success';
        }
        
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get current train count by date
$trainCounts = [];
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT journey_date, COUNT(*) as count 
        FROM trains 
        WHERE journey_date >= CURDATE() 
        GROUP BY journey_date 
        ORDER BY journey_date 
        LIMIT 10
    ");
    $trainCounts = $stmt->fetchAll();
} catch (Exception $e) {
    // Ignore error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Future Trains - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-bg: #ecf0f1;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 10px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        .main-content {
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            color: white;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .date-row {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white"><i class="fas fa-train me-2"></i>TrainBook</h4>
                        <small class="text-white-50">Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="trains.php">
                                <i class="fas fa-train me-2"></i>Manage Trains
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="bookings.php">
                                <i class="fas fa-ticket-alt me-2"></i>Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="add_future_trains.php">
                                <i class="fas fa-calendar-plus me-2"></i>Add Future Trains
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white mb-4 rounded">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">Add Future Trains</span>
                        <div class="d-flex">
                            <span class="badge bg-primary me-3"><?php echo htmlspecialchars($admin_name); ?></span>
                            <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Display Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Current Train Schedule -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Current Train Schedule (Next 10 Days)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($trainCounts)): ?>
                            <p class="text-muted">No trains scheduled for future dates.</p>
                        <?php else: ?>
                            <?php foreach ($trainCounts as $count): ?>
                                <div class="date-row d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-calendar-day me-2"></i><?php echo date('l, M d, Y', strtotime($count['journey_date'])); ?></span>
                                    <span class="badge bg-primary"><?php echo $count['count']; ?> trains</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add Future Trains Options -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Daily Trains (30 Days)</h5>
                            </div>
                            <div class="card-body">
                                <p>Add popular train routes for the next 30 days with:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-route text-primary me-2"></i>20 different train routes</li>
                                    <li><i class="fas fa-clock text-info me-2"></i>Varied departure times</li>
                                    <li><i class="fas fa-coins text-warning me-2"></i>Dynamic pricing</li>
                                    <li><i class="fas fa-users text-success me-2"></i>Realistic seat availability</li>
                                </ul>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="add_future_trains">
                                    <button type="submit" class="btn btn-custom">
                                        <i class="fas fa-plus me-2"></i>Add 30 Days of Trains
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Weekly Schedule (8 Weeks)</h5>
                            </div>
                            <div class="card-body">
                                <p>Add a recurring weekly schedule for 8 weeks with:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-repeat text-primary me-2"></i>Weekly recurring trains</li>
                                    <li><i class="fas fa-weekend text-info me-2"></i>Weekend special trains</li>
                                    <li><i class="fas fa-calendar-alt text-warning me-2"></i>56 days of coverage</li>
                                    <li><i class="fas fa-train text-success me-2"></i>Popular routes</li>
                                </ul>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="add_weekly_schedule">
                                    <button type="submit" class="btn btn-custom">
                                        <i class="fas fa-calendar-week me-2"></i>Add Weekly Schedule
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-eye me-2"></i>View All Trains</h5>
                            </div>
                            <div class="card-body">
                                <p>View and manage all trains in the system:</p>
                                <a href="trains.php" class="btn btn-custom">
                                    <i class="fas fa-eye me-2"></i>View Trains
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Test User Booking</h5>
                            </div>
                            <div class="card-body">
                                <p>Test the user booking system with future dates:</p>
                                <a href="../user/search_trains.php" class="btn btn-custom" target="_blank">
                                    <i class="fas fa-search me-2"></i>Test Search
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
