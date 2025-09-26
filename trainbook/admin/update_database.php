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
        
        if ($_POST['action'] === 'add_columns') {
            // Add new columns for real-time tracking
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
                    $successCount++;
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                        throw $e;
                    }
                }
            }
            
            $message = "✓ Successfully added $successCount new columns to trains table!";
            $messageType = 'success';
            
        } elseif ($_POST['action'] === 'add_sample_data') {
            // Insert realistic train data with current locations
            $insertQuery = "
                INSERT INTO trains (train_number, train_name, source_station, destination_station, departure_time, arrival_time, total_seats, available_seats, fare_per_seat, journey_date, current_location, current_status, delay_minutes, progress_percentage) VALUES
                ('TR101', 'Mumbai Rajdhani Express', 'Mumbai Central', 'New Delhi', '08:00:00', '20:00:00', 120, 45, 2500.00, CURDATE(), 'Vadodara Junction', 'in_transit', 15, 35.5),
                ('TR102', 'Shatabdi Express', 'New Delhi', 'Agra Cantt', '06:00:00', '08:30:00', 80, 12, 1200.00, CURDATE(), 'Mathura Junction', 'in_transit', 0, 75.2),
                ('TR103', 'Duronto Express', 'Kolkata', 'Chennai Central', '14:00:00', '06:00:00', 150, 78, 1800.00, CURDATE(), 'Bhubaneswar', 'in_transit', 30, 28.8),
                ('TR104', 'Vande Bharat Express', 'Delhi', 'Varanasi', '06:00:00', '14:00:00', 100, 23, 1500.00, CURDATE(), 'Kanpur Central', 'in_transit', 5, 60.3),
                ('TR105', 'Tejas Express', 'Mumbai', 'Pune', '07:00:00', '10:00:00', 60, 8, 800.00, CURDATE(), 'Lonavala', 'in_transit', 0, 85.7),
                ('TR201', 'Garib Rath Express', 'Mumbai', 'Goa', '22:00:00', '08:00:00', 200, 156, 600.00, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Mumbai Central', 'boarding', 0, 0.0),
                ('TR202', 'Sampark Kranti', 'Delhi', 'Bangalore', '11:00:00', '14:00:00', 180, 134, 2200.00, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'New Delhi', 'boarding', 0, 0.0),
                ('TR203', 'Jan Shatabdi', 'Mumbai', 'Ahmedabad', '15:30:00', '23:30:00', 90, 67, 900.00, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Mumbai Central', 'boarding', 0, 0.0),
                ('TR301', 'Rajdhani Express', 'New Delhi', 'Mumbai Central', '10:30:00', '22:30:00', 120, 0, 2500.00, CURDATE(), 'Mumbai Central', 'arrived', 0, 100.0),
                ('TR302', 'Shatabdi Express', 'Agra Cantt', 'New Delhi', '09:00:00', '11:30:00', 80, 0, 1200.00, CURDATE(), 'New Delhi', 'arrived', 0, 100.0),
                ('TR401', 'Express Superfast', 'Mumbai', 'Delhi', '08:00:00', '20:00:00', 100, 45, 1500.00, CURDATE(), 'Bhopal Junction', 'delayed', 45, 42.1),
                ('TR402', 'Duronto Express', 'Chennai', 'Kolkata', '16:00:00', '08:00:00', 150, 89, 1800.00, CURDATE(), 'Vijayawada', 'delayed', 20, 25.6)
            ";
            
            $db->exec($insertQuery);
            $message = "✓ Successfully added 12 new trains with real-time location data!";
            $messageType = 'success';
            
        } elseif ($_POST['action'] === 'update_existing') {
            // Update existing trains with current status
            $updateQuery = "
                UPDATE trains SET 
                    current_location = CASE 
                        WHEN train_number = 'TR001' THEN 'Vadodara Junction'
                        WHEN train_number = 'TR002' THEN 'Mathura Junction'
                        WHEN train_number = 'TR003' THEN 'Agra Cantt'
                        WHEN train_number = 'TR004' THEN 'Bhubaneswar'
                        WHEN train_number = 'TR005' THEN 'Lonavala'
                        ELSE source_station
                    END,
                    current_status = CASE 
                        WHEN train_number = 'TR001' THEN 'in_transit'
                        WHEN train_number = 'TR002' THEN 'in_transit'
                        WHEN train_number = 'TR003' THEN 'arrived'
                        WHEN train_number = 'TR004' THEN 'in_transit'
                        WHEN train_number = 'TR005' THEN 'in_transit'
                        ELSE 'boarding'
                    END,
                    delay_minutes = CASE 
                        WHEN train_number = 'TR001' THEN 15
                        WHEN train_number = 'TR002' THEN 0
                        WHEN train_number = 'TR003' THEN 0
                        WHEN train_number = 'TR004' THEN 30
                        WHEN train_number = 'TR005' THEN 0
                        ELSE 0
                    END,
                    progress_percentage = CASE 
                        WHEN train_number = 'TR001' THEN 35.5
                        WHEN train_number = 'TR002' THEN 75.2
                        WHEN train_number = 'TR003' THEN 100.0
                        WHEN train_number = 'TR004' THEN 28.8
                        WHEN train_number = 'TR005' THEN 85.7
                        ELSE 0.0
                    END
                WHERE train_number IN ('TR001', 'TR002', 'TR003', 'TR004', 'TR005')
            ";
            
            $result = $db->exec($updateQuery);
            $message = "✓ Successfully updated $result existing trains with real-time status!";
            $messageType = 'success';
        }
        
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Check if columns exist
$columnsExist = false;
try {
    $db = getDB();
    $stmt = $db->query("SHOW COLUMNS FROM trains LIKE 'current_location'");
    $columnsExist = $stmt->rowCount() > 0;
} catch (Exception $e) {
    // Ignore error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database - Admin Panel</title>
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

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-ready { background-color: #28a745; }
        .status-pending { background-color: #ffc107; }
        .status-error { background-color: #dc3545; }
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
                            <a class="nav-link active" href="update_database.php">
                                <i class="fas fa-database me-2"></i>Update Database
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
                        <span class="navbar-brand mb-0 h1">Update Database</span>
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

                <!-- Database Update Options -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database Schema</h5>
                            </div>
                            <div class="card-body">
                                <p>Add new columns to the trains table for real-time tracking:</p>
                                <ul class="list-unstyled">
                                    <li><span class="status-indicator status-<?php echo $columnsExist ? 'ready' : 'pending'; ?>"></span>current_location</li>
                                    <li><span class="status-indicator status-<?php echo $columnsExist ? 'ready' : 'pending'; ?>"></span>current_status</li>
                                    <li><span class="status-indicator status-<?php echo $columnsExist ? 'ready' : 'pending'; ?>"></span>delay_minutes</li>
                                    <li><span class="status-indicator status-<?php echo $columnsExist ? 'ready' : 'pending'; ?>"></span>progress_percentage</li>
                                    <li><span class="status-indicator status-<?php echo $columnsExist ? 'ready' : 'pending'; ?>"></span>last_updated</li>
                                </ul>
                                
                                <?php if (!$columnsExist): ?>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="add_columns">
                                        <button type="submit" class="btn btn-custom">
                                            <i class="fas fa-plus me-2"></i>Add Columns
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>Columns already exist!
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-train me-2"></i>Sample Train Data</h5>
                            </div>
                            <div class="card-body">
                                <p>Add realistic train data with current locations and real-time status:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-map-marker-alt text-primary me-2"></i>12 new trains with locations</li>
                                    <li><i class="fas fa-clock text-warning me-2"></i>Real-time status tracking</li>
                                    <li><i class="fas fa-chart-line text-info me-2"></i>Progress percentages</li>
                                    <li><i class="fas fa-exclamation-triangle text-danger me-2"></i>Delay information</li>
                                </ul>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="add_sample_data">
                                    <button type="submit" class="btn btn-custom">
                                        <i class="fas fa-plus me-2"></i>Add Sample Data
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-sync me-2"></i>Update Existing Trains</h5>
                            </div>
                            <div class="card-body">
                                <p>Update existing trains with current location and status information:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-route text-primary me-2"></i>TR001 - TR005 trains</li>
                                    <li><i class="fas fa-map-marker-alt text-success me-2"></i>Current locations</li>
                                    <li><i class="fas fa-chart-bar text-info me-2"></i>Progress tracking</li>
                                </ul>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_existing">
                                    <button type="submit" class="btn btn-custom">
                                        <i class="fas fa-sync me-2"></i>Update Existing
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-eye me-2"></i>View Results</h5>
                            </div>
                            <div class="card-body">
                                <p>View the updated trains with real-time location data:</p>
                                <a href="trains.php" class="btn btn-custom">
                                    <i class="fas fa-eye me-2"></i>View Trains
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
