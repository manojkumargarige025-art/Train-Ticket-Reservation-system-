<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Show login form instead of redirecting
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - TrainBook</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-center">Admin Login Required</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <strong>Please login as admin to access this page.</strong>
                            </div>
                            <p><a href="../test_admin_login.php" class="btn btn-primary">Go to Admin Login</a></p>
                            <p><a href="../index.php" class="btn btn-secondary">Go to Home</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

// Get all bookings
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT b.*, u.first_name, u.last_name, u.email, t.train_name, t.train_number, t.source_station, t.destination_station
        FROM bookings b 
        JOIN users u ON b.user_id = u.id
        JOIN trains t ON b.train_id = t.id 
        ORDER BY b.booking_date DESC
    ");
    $stmt->execute();
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
    <title>Manage Bookings - Admin Panel</title>
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

        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
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

        .status-processing {
            background: #e8f0fe;
            color: #1a73e8;
        }

        .payment-paid {
            background: #d4edda;
            color: #155724;
        }

        .payment-pending {
            background: #fff3cd;
            color: #856404;
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
                            <a class="nav-link active" href="bookings.php">
                                <i class="fas fa-ticket-alt me-2"></i>Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Users
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
                        <span class="navbar-brand mb-0 h1">Manage Bookings</span>
                        <div class="d-flex">
                            <span class="badge bg-primary me-3"><?php echo htmlspecialchars($admin_name); ?></span>
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
                        <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>All Bookings</h5>
                        <span class="badge bg-primary"><?php echo count($bookings); ?> bookings</span>
                    </div>
                    <div class="card-body">
                        <form method="post" action="bulk_update_bookings.php" onsubmit="return confirm('Apply selected action to chosen bookings?');">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <div class="btn-group" role="group">
                                    <button type="submit" name="bulk_action" value="processing" class="btn btn-warning btn-sm"><i class="fas fa-hourglass-half me-1"></i> Mark Processing</button>
                                    <button type="submit" name="bulk_action" value="approve" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i> Approve Selected</button>
                                    <button type="submit" name="bulk_action" value="reject" class="btn btn-danger btn-sm"><i class="fas fa-times me-1"></i> Reject Selected</button>
                                </div>
                                <span class="text-muted small">Use the checkboxes to select bookings</span>
                            </div>
                            <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:42px">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Booking ID</th>
                                        <th>Passenger</th>
                                        <th>Train</th>
                                        <th>Route</th>
                                        <th>Journey Date</th>
                                        <th>Seat</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Booking Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($bookings)): ?>
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-4">
                                                <i class="fas fa-ticket-alt fa-2x mb-2 d-block"></i>
                                                No bookings found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_ids[]" value="<?php echo (int)$booking['id']; ?>" class="form-check-input row-check">
                                                </td>
                                                <td><strong><?php echo htmlspecialchars($booking['booking_id']); ?></strong></td>
                                                <td>
                                                    <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($booking['train_name']); ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($booking['train_number']); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($booking['source_station']); ?> → 
                                                    <?php echo htmlspecialchars($booking['destination_station']); ?>
                                                </td>
                                                <td><?php echo formatDate($booking['journey_date']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['seat_number']); ?></td>
                                                <td><strong><?php echo formatCurrency($booking['total_fare']); ?></strong></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                                        <?php echo ucfirst($booking['booking_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($booking['booking_status'] === 'pending'): ?>
                                                        <div class="btn-group" role="group">
                                                            <a href="approve_booking.php?id=<?php echo $booking['id']; ?>&action=approve" 
                                                               class="btn btn-success btn-sm" 
                                                               onclick="return confirm('Approve this booking?')">
                                                                <i class="fas fa-check"></i> Approve
                                                            </a>
                                                            <a href="approve_booking.php?id=<?php echo $booking['id']; ?>&action=reject" 
                                                               class="btn btn-danger btn-sm" 
                                                               onclick="return confirm('Reject this booking?')">
                                                                <i class="fas fa-times"></i> Reject
                                                            </a>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">No action needed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge payment-<?php echo $booking['payment_status']; ?>">
                                                        <?php echo ucfirst($booking['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($booking['booking_date']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            </div>
                        </form>
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

        // Select/Deselect all checkboxes
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.row-check').forEach(cb => cb.checked = selectAll.checked);
            });
        }
    </script>
</body>
</html>
