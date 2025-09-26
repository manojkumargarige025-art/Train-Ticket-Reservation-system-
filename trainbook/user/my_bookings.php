<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirectWithMessage('../index.php', 'Please login to view your bookings.', 'error');
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get user's bookings
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT b.*, t.train_name, t.train_number, t.source_station, t.destination_station, 
               t.departure_time, t.arrival_time, t.journey_date as train_journey_date
        FROM bookings b 
        JOIN trains t ON b.train_id = t.id 
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Get bookings error: " . $e->getMessage());
    $bookings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - TrainBook</title>
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

        .payment-paid {
            background: #d4edda;
            color: #155724;
        }

        .payment-pending {
            background: #fff3cd;
            color: #856404;
        }

        .booking-card {
            border-left: 4px solid var(--secondary-color);
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
                        <small class="text-white-50">User Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="search_trains.php">
                                <i class="fas fa-search me-2"></i>Search Trains
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="my_bookings.php">
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
                <nav class="navbar navbar-expand-lg navbar-light bg-white mb-4 rounded">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">My Bookings</span>
                        <div class="d-flex">
                            <span class="badge bg-primary me-3"><?php echo htmlspecialchars($user_name); ?></span>
                            <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Display Messages -->
                <?php displayMessage(); ?>

                <!-- Bookings List -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Your Bookings</h5>
                        <span class="badge bg-primary"><?php echo count($bookings); ?> bookings</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bookings)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                <h5>No Bookings Yet</h5>
                                <p class="text-muted">You haven't made any train bookings yet.</p>
                                <a href="search_trains.php" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Search Trains
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($bookings as $booking): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card booking-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($booking['train_name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($booking['train_number']); ?></small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                                            <?php echo ucfirst($booking['booking_status']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <p class="mb-1">
                                                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                            <strong><?php echo htmlspecialchars($booking['source_station']); ?></strong>
                                                        </p>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?php echo formatTime($booking['departure_time']); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-6">
                                                        <p class="mb-1">
                                                            <i class="fas fa-map-marker-alt text-success me-2"></i>
                                                            <strong><?php echo htmlspecialchars($booking['destination_station']); ?></strong>
                                                        </p>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?php echo formatTime($booking['arrival_time']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <p class="mb-1"><strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['booking_id']); ?></p>
                                                        <p class="mb-1"><strong>Passenger:</strong> <?php echo htmlspecialchars($booking['passenger_name']); ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <p class="mb-1"><strong>Seat:</strong> <?php echo htmlspecialchars($booking['seat_number']); ?></p>
                                                        <p class="mb-1"><strong>Date:</strong> <?php echo formatDate($booking['journey_date']); ?></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5 class="text-primary mb-0"><?php echo formatCurrency($booking['total_fare']); ?></h5>
                                                        <small class="text-muted">Total Fare</small>
                                                    </div>
                                                    <div>
                                                        <span class="status-badge payment-<?php echo $booking['payment_status']; ?>">
                                                            <i class="fas fa-<?php echo $booking['payment_status'] === 'paid' ? 'check-circle' : 'clock'; ?> me-1"></i>
                                                            <?php echo ucfirst($booking['payment_status']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($booking['booking_status'] === 'pending'): ?>
                                                    <div class="mt-2">
                                                        <div class="alert alert-warning alert-sm mb-0">
                                                            <i class="fas fa-clock me-2"></i>
                                                            <strong>Waiting for Admin Approval</strong><br>
                                                            <small>Your booking is under review. You will be notified once approved.</small>
                                                        </div>
                                                    </div>
                                                <?php elseif ($booking['booking_status'] === 'rejected'): ?>
                                                    <div class="mt-2">
                                                        <div class="alert alert-danger alert-sm mb-0">
                                                            <i class="fas fa-times-circle me-2"></i>
                                                            <strong>Booking Rejected</strong><br>
                                                            <small>Your booking was not approved. Please try booking another train.</small>
                                                        </div>
                                                    </div>
                                                <?php elseif ($booking['booking_status'] === 'confirmed'): ?>
                                                    <div class="mt-2">
                                                        <div class="alert alert-success alert-sm mb-0">
                                                            <i class="fas fa-check-circle me-2"></i>
                                                            <strong>Booking Confirmed</strong><br>
                                                            <small>Your booking has been approved and confirmed!</small>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="mt-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        Booked on: <?php echo formatDate($booking['booking_date']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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