<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    redirectWithMessage('../index.php', 'Please login as admin to access the dashboard.', 'error');
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];
$admin_email = $_SESSION['admin_email'];

// Get admin statistics
try {
    $db = getDB();
    
    // Get total users
    $stmt = $db->prepare("SELECT COUNT(*) as total_users FROM users");
    $stmt->execute();
    $total_users = $stmt->fetch()['total_users'];
    
    // Get total trains
    $stmt = $db->prepare("SELECT COUNT(*) as total_trains FROM trains WHERE status = 'active'");
    $stmt->execute();
    $total_trains = $stmt->fetch()['total_trains'];
    
    // Get total bookings
    $stmt = $db->prepare("SELECT COUNT(*) as total_bookings FROM bookings");
    $stmt->execute();
    $total_bookings = $stmt->fetch()['total_bookings'];
    
    // Get today's bookings
    $stmt = $db->prepare("SELECT COUNT(*) as today_bookings FROM bookings WHERE DATE(booking_date) = CURDATE()");
    $stmt->execute();
    $today_bookings = $stmt->fetch()['today_bookings'];
    
    // Get recent bookings
    $stmt = $db->prepare("
        SELECT b.*, u.first_name, u.last_name, u.email, t.train_name, t.train_number
        FROM bookings b 
        JOIN users u ON b.user_id = u.id
        JOIN trains t ON b.train_id = t.id 
        ORDER BY b.booking_date DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_bookings = $stmt->fetchAll();
    
    // Get revenue data
    $stmt = $db->prepare("
        SELECT 
            SUM(total_fare) as total_revenue,
            COUNT(*) as total_bookings
        FROM bookings 
        WHERE payment_status = 'paid' AND booking_status = 'confirmed'
    ");
    $stmt->execute();
    $revenue_data = $stmt->fetch();
    
    // Get popular trains
    $stmt = $db->prepare("
        SELECT t.train_name, t.train_number, COUNT(b.id) as booking_count
        FROM trains t
        LEFT JOIN bookings b ON t.id = b.train_id
        GROUP BY t.id
        ORDER BY booking_count DESC
        LIMIT 5
    ");
    $stmt->execute();
    $popular_trains = $stmt->fetchAll();
    
    // Revenue trend (last 7 days)
    $stmt = $db->prepare("
        SELECT DATE(booking_date) as d, SUM(total_fare) as total
        FROM bookings
        WHERE payment_status = 'paid' AND booking_status = 'confirmed'
          AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(booking_date)
    ");
    $stmt->execute();
    $trend_rows = $stmt->fetchAll();
    
    // Build 7-day series
    $trendLabels = [];
    $trendValues = [];
    $map = [];
    foreach ($trend_rows as $r) { $map[$r['d']] = (float)$r['total']; }
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-{$i} day"));
        $trendLabels[] = date('d M', strtotime($day));
        $trendValues[] = isset($map[$day]) ? round($map[$day], 2) : 0;
    }
    
    // Bookings by status
    $stmt = $db->prepare("SELECT booking_status, COUNT(*) as c FROM bookings GROUP BY booking_status");
    $stmt->execute();
    $status_rows = $stmt->fetchAll();
    $statusLabels = [];
    $statusValues = [];
    foreach ($status_rows as $r) { $statusLabels[] = ucfirst($r['booking_status']); $statusValues[] = (int)$r['c']; }
    
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $total_users = 0;
    $total_trains = 0;
    $total_bookings = 0;
    $today_bookings = 0;
    $recent_bookings = [];
    $revenue_data = ['total_revenue' => 0, 'total_bookings' => 0];
    $popular_trains = [];
    $trendLabels = [];
    $trendValues = [];
    $statusLabels = [];
    $statusValues = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Train Ticket Reservation System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css" rel="stylesheet">
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
            background: linear-gradient(135deg, var(--accent-color), #e67e22);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
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

        .revenue-card {
            background: linear-gradient(135deg, var(--success-color), #58d68d);
        }

        .users-card {
            background: linear-gradient(135deg, var(--warning-color), #f7dc6f);
        }

        .trains-card {
            background: linear-gradient(135deg, #9b59b6, #bb8fce);
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
                            <a class="nav-link active" href="dashboard.php">
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
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar me-2"></i>Reports
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
                        <span class="navbar-brand mb-0 h1">Admin Dashboard</span>
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

                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-3"><i class="fas fa-crown me-2"></i>Welcome to Admin Panel</h2>
                            <p class="mb-0">Manage trains, bookings, users, and monitor system performance from this comprehensive dashboard.</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="trains.php" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>Add Train
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $total_users; ?></div>
                            <div>Total Users</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card trains-card">
                            <div class="stat-number"><?php echo $total_trains; ?></div>
                            <div>Active Trains</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card users-card">
                            <div class="stat-number"><?php echo $total_bookings; ?></div>
                            <div>Total Bookings</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card revenue-card">
                            <div class="stat-number"><?php echo formatCurrency($revenue_data['total_revenue']); ?></div>
                            <div>Total Revenue</div>
                        </div>
                    </div>
                </div>

                <!-- Clock & Analytics -->
                <div class="row mb-4">
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="far fa-clock me-2"></i>Server Time</h5>
                            </div>
                            <div class="card-body text-center">
                                <div id="liveDate" class="fs-5 text-muted mb-2"></div>
                                <div id="liveTime" class="display-5 fw-bold"></div>
                                <small class="text-muted">Timezone: <?php echo date_default_timezone_get(); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Revenue (7 days)</h5></div>
                            <div class="card-body"><canvas id="revTrend"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Bookings by Status</h5></div>
                            <div class="card-body"><canvas id="statusChart"></canvas></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Bookings -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Bookings</h5>
                                <a href="bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Booking ID</th>
                                                <th>Passenger</th>
                                                <th>Train</th>
                                                <th>Journey Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_bookings)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="fas fa-ticket-alt fa-2x mb-2 d-block"></i>
                                                        No bookings found
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recent_bookings as $booking): ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($booking['booking_id']); ?></strong></td>
                                                        <td>
                                                            <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($booking['train_name']); ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($booking['train_number']); ?></small>
                                                        </td>
                                                        <td><?php echo formatDate($booking['journey_date']); ?></td>
                                                        <td><strong><?php echo formatCurrency($booking['total_fare']); ?></strong></td>
                                                        <td>
                                                            <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                                                <?php echo ucfirst($booking['booking_status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Popular Trains & Quick Stats -->
                    <div class="col-lg-4">
                        <!-- Popular Trains -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Popular Trains</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($popular_trains)): ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-train fa-2x mb-2 d-block"></i>
                                        No data available
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($popular_trains as $train): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($train['train_name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($train['train_number']); ?></small>
                                            </div>
                                            <span class="badge bg-primary"><?php echo $train['booking_count']; ?> bookings</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Quick Stats</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="border-end">
                                            <h4 class="text-success"><?php echo $today_bookings; ?></h4>
                                            <small class="text-muted">Today's Bookings</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <h4 class="text-info"><?php echo $revenue_data['total_bookings']; ?></h4>
                                        <small class="text-muted">Paid Bookings</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-grid gap-2">
                                    <a href="trains.php" class="btn btn-custom">
                                        <i class="fas fa-plus me-2"></i>Add New Train
                                    </a>
                                    <a href="bookings.php" class="btn btn-outline-primary">
                                        <i class="fas fa-list me-2"></i>View All Bookings
                                    </a>
                                    <a href="reports.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-chart-bar me-2"></i>Generate Reports
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Live clock
        function updateClock() {
            const now = new Date();
            const dateOpts = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            const timeOpts = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('liveDate').textContent = now.toLocaleDateString(undefined, dateOpts);
            document.getElementById('liveTime').textContent = now.toLocaleTimeString(undefined, timeOpts);
        }
        updateClock(); setInterval(updateClock, 1000);

        // Charts data from PHP
        const revLabels = <?php echo json_encode($trendLabels); ?>;
        const revData = <?php echo json_encode($trendValues); ?>;
        const stLabels = <?php echo json_encode($statusLabels); ?>;
        const stData = <?php echo json_encode($statusValues); ?>;

        // Revenue trend line
        if (document.getElementById('revTrend')) {
            new Chart(document.getElementById('revTrend'), {
                type: 'line',
                data: { labels: revLabels, datasets: [{
                    label: 'Revenue', data: revData,
                    borderColor: '#4da3ff', backgroundColor: 'rgba(77,163,255,0.2)', fill: true, tension: 0.35,
                }]},
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
        }

        // Status doughnut
        if (document.getElementById('statusChart')) {
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: { labels: stLabels, datasets: [{
                    data: stData,
                    backgroundColor: ['#58d68d', '#f1948a', '#f7dc6f', '#85c1e9', '#d2b4de']
                }]},
                options: { plugins: { legend: { position: 'bottom' } } }
            });
        }
    </script>
</body>
</html>
