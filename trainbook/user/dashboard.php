<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirectWithMessage('../index.php', 'Please login to access your dashboard.', 'error');
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Get user statistics
try {
    $db = getDB();
    
    // Get total bookings
    $stmt = $db->prepare("SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_bookings = $stmt->fetch()['total_bookings'];
    
    // Get recent bookings
    $stmt = $db->prepare("
        SELECT b.*, t.train_name, t.train_number, t.source_station, t.destination_station, t.departure_time, t.arrival_time
        FROM bookings b 
        JOIN trains t ON b.train_id = t.id 
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_bookings = $stmt->fetchAll();
    
    // Get upcoming journeys
    $stmt = $db->prepare("
        SELECT b.*, t.train_name, t.train_number, t.source_station, t.destination_station, t.departure_time, t.arrival_time
        FROM bookings b 
        JOIN trains t ON b.train_id = t.id 
        WHERE b.user_id = ? AND b.journey_date >= CURDATE() AND b.booking_status = 'confirmed'
        ORDER BY b.journey_date ASC 
        LIMIT 3
    ");
    $stmt->execute([$user_id]);
    $upcoming_journeys = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $total_bookings = 0;
    $recent_bookings = [];
    $upcoming_journeys = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Train Ticket Reservation System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/theme.css" rel="stylesheet">
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

        .stat-card {
            background: linear-gradient(135deg, var(--secondary-color), #5dade2);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .booking-card {
            border-left: 4px solid var(--secondary-color);
            margin-bottom: 15px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
            color: white;
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--secondary-color), #5dade2);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
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
                        <small class="text-white-50">User Dashboard</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="search_trains.php">
                                <i class="fas fa-search me-2"></i>Search Trains
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_bookings.php">
                                <i class="fas fa-ticket-alt me-2"></i>My Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user me-2"></i>Profile
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
                <nav class="navbar navbar-expand-lg navbar-light navbar-custom mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</span>
                        <div class="d-flex">
                            <span class="badge bg-primary me-3"><?php echo htmlspecialchars($user_email); ?></span>
                            <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Display Messages -->
                <?php displayMessage(); ?>

                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-3"><i class="fas fa-train me-2"></i>Welcome to Your Dashboard</h2>
                            <p class="mb-0">Manage your train bookings, search for new journeys, and stay updated with your travel plans.</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="search_trains.php" class="btn btn-light btn-lg">
                                <i class="fas fa-search me-2"></i>Search Trains
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $total_bookings; ?></div>
                            <div>Total Bookings</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card" style="background: linear-gradient(135deg, var(--success-color), #58d68d);">
                            <div class="stat-number"><?php echo count($upcoming_journeys); ?></div>
                            <div>Upcoming Journeys</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card" style="background: linear-gradient(135deg, var(--warning-color), #f7dc6f);">
                            <div class="stat-number">0</div>
                            <div>Pending Payments</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Bookings -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Bookings</h5>
                                <a href="my_bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_bookings)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-ticket-alt"></i>
                                        <h5>No Bookings Yet</h5>
                                        <p>Start your journey by searching for trains and making your first booking!</p>
                                        <a href="search_trains.php" class="btn btn-custom">Search Trains</a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <div class="booking-card card p-3">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($booking['train_name']); ?></h6>
                                                    <p class="mb-1 text-muted">
                                                        <i class="fas fa-route me-1"></i>
                                                        <?php echo htmlspecialchars($booking['source_station']); ?> → 
                                                        <?php echo htmlspecialchars($booking['destination_station']); ?>
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo formatDate($booking['journey_date']); ?> | 
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo formatTime($booking['departure_time']); ?>
                                                    </small>
                                                </div>
                                                <div class="col-md-4 text-md-end">
                                                    <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                                        <?php echo ucfirst($booking['booking_status']); ?>
                                                    </span>
                                                    <div class="mt-2">
                                                        <strong><?php echo formatCurrency($booking['total_fare']); ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Journeys -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Upcoming Journeys</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($upcoming_journeys)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <h6>No Upcoming Journeys</h6>
                                        <p class="small">Book your next trip!</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($upcoming_journeys as $journey): ?>
                                        <div class="border-bottom pb-3 mb-3">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($journey['train_name']); ?></h6>
                                            <p class="mb-1 small text-muted">
                                                <?php echo htmlspecialchars($journey['source_station']); ?> → 
                                                <?php echo htmlspecialchars($journey['destination_station']); ?>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo formatDate($journey['journey_date']); ?> | 
                                                <?php echo formatTime($journey['departure_time']); ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="search_trains.php" class="btn btn-custom">
                                        <i class="fas fa-search me-2"></i>Search Trains
                                    </a>
                                    <a href="my_bookings.php" class="btn btn-outline-primary">
                                        <i class="fas fa-ticket-alt me-2"></i>View Bookings
                                    </a>
                                    <a href="profile.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-user me-2"></i>Update Profile
                                    </a>
                                </div>
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
