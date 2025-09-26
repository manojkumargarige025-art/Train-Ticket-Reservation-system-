<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    redirectWithMessage('../index.php', 'Please login as admin to access this page.', 'error');
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

// Handle add train
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $train_number = sanitizeInput($_POST['train_number']);
    $train_name = sanitizeInput($_POST['train_name']);
    $source_station = sanitizeInput($_POST['source_station']);
    $destination_station = sanitizeInput($_POST['destination_station']);
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $total_seats = (int)$_POST['total_seats'];
    $fare_per_seat = (float)$_POST['fare_per_seat'];
    $journey_date = $_POST['journey_date'];
    $repeat_type = $_POST['repeat_type'] ?? 'once'; // once | daily | weekly | multiple
    $end_date = $_POST['end_date'] ?? '';
    $weekdays = isset($_POST['weekdays']) && is_array($_POST['weekdays']) ? array_map('intval', $_POST['weekdays']) : [];
    $multiple_dates_raw = $_POST['multiple_dates'] ?? '';
    
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO trains (train_number, train_name, source_station, destination_station, departure_time, arrival_time, total_seats, available_seats, fare_per_seat, journey_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $datesToCreate = [];
        if ($repeat_type === 'once') {
            $datesToCreate[] = $journey_date;
        } elseif ($repeat_type === 'daily' && $end_date) {
            $start = new DateTime($journey_date);
            $end = new DateTime($end_date);
            if ($end < $start) { $end = clone $start; }
            for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
                $datesToCreate[] = $d->format('Y-m-d');
            }
        } elseif ($repeat_type === 'weekly' && $end_date && !empty($weekdays)) {
            $start = new DateTime($journey_date);
            $end = new DateTime($end_date);
            if ($end < $start) { $end = clone $start; }
            for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
                // 0 (Sun) .. 6 (Sat)
                $w = (int)$d->format('w');
                if (in_array($w, $weekdays, true)) {
                    $datesToCreate[] = $d->format('Y-m-d');
                }
            }
        } elseif ($repeat_type === 'multiple' && trim($multiple_dates_raw) !== '') {
            $parts = preg_split('/[\s,\n\r]+/', $multiple_dates_raw);
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p === '') continue;
                // attempt to normalize to Y-m-d
                $ts = strtotime($p);
                if ($ts !== false) {
                    $datesToCreate[] = date('Y-m-d', $ts);
                }
            }
            $datesToCreate = array_values(array_unique($datesToCreate));
        } else {
            $datesToCreate[] = $journey_date;
        }

        $created = 0; $skipped = 0;
        foreach ($datesToCreate as $dt) {
            try {
                $stmt->execute([$train_number, $train_name, $source_station, $destination_station, $departure_time, $arrival_time, $total_seats, $total_seats, $fare_per_seat, $dt]);
                $created++;
            } catch (Exception $e) {
                // skip duplicates or other row-specific errors
                $skipped++;
            }
        }

        $msg = "Added $created schedules" . ($skipped ? ", $skipped skipped" : '') . "!";
        redirectWithMessage('trains.php', $msg, 'success');
    } catch (Exception $e) {
        error_log("Add train error: " . $e->getMessage());
        redirectWithMessage('trains.php', 'Error adding train. Please try again.', 'error');
    }
}

// Handle delete train
if (isset($_GET['delete'])) {
    $train_id = (int)$_GET['delete'];
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM trains WHERE id = ?");
        $stmt->execute([$train_id]);
        redirectWithMessage('trains.php', 'Train deleted successfully!', 'success');
    } catch (Exception $e) {
        error_log("Delete train error: " . $e->getMessage());
        redirectWithMessage('trains.php', 'Error deleting train.', 'error');
    }
}

// Get all trains
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM trains ORDER BY created_at DESC");
    $trains = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Get trains error: " . $e->getMessage());
    $trains = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trains - Admin Panel</title>
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
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            color: white;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
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

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .status-warning {
            background: #fff3cd;
            color: #856404;
        }

        .status-in-transit {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-boarding {
            background: #d4edda;
            color: #155724;
        }

        .status-departed {
            background: #cce5ff;
            color: #004085;
        }

        .status-arrived {
            background: #d4edda;
            color: #155724;
        }

        .progress {
            border-radius: 10px;
        }

        .progress-bar {
            border-radius: 10px;
        }

        .required {
            color: var(--accent-color);
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
                            <a class="nav-link active" href="trains.php">
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
                        <span class="navbar-brand mb-0 h1">Manage Trains</span>
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

                <!-- Add Train Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Train</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="trains.php">
                            <input type="hidden" name="action" value="add">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="train_number" class="form-label">Train Number <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="train_number" name="train_number" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="train_name" class="form-label">Train Name <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="train_name" name="train_name" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="source_station" class="form-label">Source Station <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="source_station" name="source_station" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="destination_station" class="form-label">Destination Station <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="destination_station" name="destination_station" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="departure_time" class="form-label">Departure Time <span class="required">*</span></label>
                                    <input type="time" class="form-control" id="departure_time" name="departure_time" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="arrival_time" class="form-label">Arrival Time <span class="required">*</span></label>
                                    <input type="time" class="form-control" id="arrival_time" name="arrival_time" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="total_seats" class="form-label">Total Seats <span class="required">*</span></label>
                                    <input type="number" class="form-control" id="total_seats" name="total_seats" min="1" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="fare_per_seat" class="form-label">Fare per Seat <span class="required">*</span></label>
                                    <input type="number" class="form-control" id="fare_per_seat" name="fare_per_seat" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="repeat_type" class="form-label">Schedule Type</label>
                                    <select class="form-control" id="repeat_type" name="repeat_type">
                                        <option value="once" selected>One date</option>
                                        <option value="daily">Daily (date range)</option>
                                        <option value="weekly">Weekly (select days)</option>
                                        <option value="multiple">Multiple dates (list)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="journey_date" class="form-label">Start/Single Date <span class="required">*</span></label>
                                    <input type="date" class="form-control" id="journey_date" name="journey_date" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3 repeat-range d-none">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="row weekly-options d-none">
                                <div class="col-12 mb-3">
                                    <label class="form-label d-block">Weekdays</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <?php $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']; for($i=0; $i<7; $i++): ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="wd_<?php echo $i; ?>" name="weekdays[]" value="<?php echo $i; ?>">
                                                <label class="form-check-label" for="wd_<?php echo $i; ?>"><?php echo $days[$i]; ?></label>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row multiple-options d-none">
                                <div class="col-12 mb-3">
                                    <label for="multiple_dates" class="form-label">Multiple Dates (comma or new-line separated)</label>
                                    <textarea class="form-control" id="multiple_dates" name="multiple_dates" rows="2" placeholder="2025-09-25, 2025-09-28, 2025-10-02"></textarea>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-custom btn-lg">
                                    <i class="fas fa-plus me-2"></i>Add Train
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Trains List -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Trains</h5>
                        <span class="badge bg-primary"><?php echo count($trains); ?> trains</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Train No.</th>
                                        <th>Train Name</th>
                                        <th>Route</th>
                                        <th>Current Location</th>
                                        <th>Real-time Status</th>
                                        <th>Progress</th>
                                        <th>Departure</th>
                                        <th>Arrival</th>
                                        <th>Seats</th>
                                        <th>Fare</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($trains)): ?>
                                        <tr>
                                            <td colspan="12" class="text-center text-muted py-4">
                                                <i class="fas fa-train fa-2x mb-2 d-block"></i>
                                                No trains found. Add your first train above!
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($trains as $train): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($train['train_number']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($train['train_name']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($train['source_station']); ?> → 
                                                    <?php echo htmlspecialchars($train['destination_station']); ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                                    <?php echo htmlspecialchars($train['current_location'] ?? $train['source_station']); ?>
                                                    <?php if (isset($train['delay_minutes']) && $train['delay_minutes'] > 0): ?>
                                                        <br><small class="text-warning">+<?php echo $train['delay_minutes']; ?> min delay</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $status = $train['current_status'] ?? $train['status'];
                                                    $statusClass = 'status-' . $status;
                                                    if ($status === 'in_transit') $statusClass = 'status-active';
                                                    if ($status === 'delayed') $statusClass = 'status-warning';
                                                    ?>
                                                    <span class="status-badge <?php echo $statusClass; ?>">
                                                        <i class="fas fa-<?php echo $status === 'in_transit' ? 'train' : ($status === 'delayed' ? 'clock' : 'check-circle'); ?> me-1"></i>
                                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (isset($train['progress_percentage'])): ?>
                                                        <div class="progress" style="height: 8px; width: 80px;">
                                                            <div class="progress-bar bg-<?php echo $train['progress_percentage'] >= 100 ? 'success' : ($train['progress_percentage'] >= 50 ? 'info' : 'warning'); ?>" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo min(100, $train['progress_percentage']); ?>%">
                                                            </div>
                                                        </div>
                                                        <small class="text-muted"><?php echo number_format($train['progress_percentage'], 1); ?>%</small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo formatTime($train['departure_time']); ?></td>
                                                <td><?php echo formatTime($train['arrival_time']); ?></td>
                                                <td>
                                                    <span class="text-success"><?php echo $train['available_seats']; ?></span> / 
                                                    <?php echo $train['total_seats']; ?>
                                                </td>
                                                <td><strong><?php echo formatCurrency($train['fare_per_seat']); ?></strong></td>
                                                <td><?php echo formatDate($train['journey_date']); ?></td>
                                                <td>
                                                    <a href="?delete=<?php echo $train['id']; ?>" 
                                                       class="btn btn-outline-danger btn-sm"
                                                       onclick="return confirm('Are you sure you want to delete this train?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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

        // Set default journey date to today
        document.getElementById('journey_date').value = '<?php echo date('Y-m-d'); ?>';

        // Toggle schedule UI
        const repeatType = document.getElementById('repeat_type');
        const rangeRow = document.querySelector('.repeat-range');
        const weeklyRow = document.querySelector('.weekly-options');
        const multipleRow = document.querySelector('.multiple-options');

        function updateRepeatUI() {
            const v = repeatType.value;
            rangeRow.classList.toggle('d-none', !(v === 'daily' || v === 'weekly'));
            weeklyRow.classList.toggle('d-none', v !== 'weekly');
            multipleRow.classList.toggle('d-none', v !== 'multiple');
        }
        repeatType.addEventListener('change', updateRepeatUI);
        updateRepeatUI();

        // Real-time train status updates
        function updateTrainStatus() {
            fetch('../api/train_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateTrainTable(data.trains);
                        updateLastUpdatedTime();
                    }
                })
                .catch(error => {
                    console.error('Error fetching train status:', error);
                });
        }

        function updateTrainTable(trains) {
            const tbody = document.querySelector('tbody');
            if (!tbody) return;

            // Clear existing rows except empty state
            const existingRows = tbody.querySelectorAll('tr');
            existingRows.forEach(row => {
                if (!row.querySelector('.fa-train.fa-2x')) {
                    row.remove();
                }
            });

            if (trains.length === 0) {
                return;
            }

            // Remove empty state row if it exists
            const emptyStateRow = tbody.querySelector('tr td[colspan="12"]');
            if (emptyStateRow) {
                emptyStateRow.parentElement.remove();
            }

            // Add updated train rows
            trains.forEach(train => {
                const row = createTrainRow(train);
                tbody.appendChild(row);
            });
        }

        function createTrainRow(train) {
            const row = document.createElement('tr');
            
            const statusClass = getStatusClass(train.current_status);
            const statusIcon = getStatusIcon(train.current_status);
            const progressColor = getProgressColor(train.progress_percentage);
            
            row.innerHTML = `
                <td><strong>${train.train_number}</strong></td>
                <td>${train.train_name}</td>
                <td>${train.source_station} → ${train.destination_station}</td>
                <td>
                    <i class="fas fa-map-marker-alt text-primary me-1"></i>
                    ${train.current_location || train.source_station}
                    ${train.delay_minutes > 0 ? `<br><small class="text-warning">+${train.delay_minutes} min delay</small>` : ''}
                </td>
                <td>
                    <span class="status-badge ${statusClass}">
                        <i class="fas fa-${statusIcon} me-1"></i>
                        ${train.current_status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </span>
                </td>
                <td>
                    <div class="progress" style="height: 8px; width: 80px;">
                        <div class="progress-bar bg-${progressColor}" 
                             role="progressbar" 
                             style="width: ${Math.min(100, train.progress_percentage)}%">
                        </div>
                    </div>
                    <small class="text-muted">${parseFloat(train.progress_percentage).toFixed(1)}%</small>
                </td>
                <td>${formatTime(train.departure_time)}</td>
                <td>${formatTime(train.arrival_time)}</td>
                <td>
                    <span class="text-success">${train.available_seats}</span> / 
                    ${train.total_seats}
                </td>
                <td><strong>₹${parseFloat(train.fare_per_seat).toFixed(2)}</strong></td>
                <td>${formatDate(train.journey_date)}</td>
                <td>
                    <a href="?delete=${train.id}" 
                       class="btn btn-outline-danger btn-sm"
                       onclick="return confirm('Are you sure you want to delete this train?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            `;
            
            return row;
        }

        function getStatusClass(status) {
            const statusMap = {
                'boarding': 'status-boarding',
                'departed': 'status-departed',
                'in_transit': 'status-in-transit',
                'delayed': 'status-warning',
                'arrived': 'status-arrived',
                'cancelled': 'status-cancelled',
                'active': 'status-active'
            };
            return statusMap[status] || 'status-active';
        }

        function getStatusIcon(status) {
            const iconMap = {
                'boarding': 'users',
                'departed': 'train',
                'in_transit': 'train',
                'delayed': 'clock',
                'arrived': 'check-circle',
                'cancelled': 'times-circle',
                'active': 'check-circle'
            };
            return iconMap[status] || 'train';
        }

        function getProgressColor(percentage) {
            if (percentage >= 100) return 'success';
            if (percentage >= 50) return 'info';
            return 'warning';
        }

        function formatTime(timeString) {
            const time = new Date('1970-01-01T' + timeString + 'Z');
            return time.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false 
            });
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }

        function updateLastUpdatedTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            
            let lastUpdatedElement = document.getElementById('last-updated');
            if (!lastUpdatedElement) {
                lastUpdatedElement = document.createElement('div');
                lastUpdatedElement.id = 'last-updated';
                lastUpdatedElement.className = 'text-muted text-end small mt-2';
                document.querySelector('.card-body').appendChild(lastUpdatedElement);
            }
            lastUpdatedElement.innerHTML = `<i class="fas fa-sync me-1"></i>Last updated: ${timeString}`;
        }

        // Update train status every 30 seconds
        setInterval(updateTrainStatus, 30000);
        
        // Initial update after page load
        setTimeout(updateTrainStatus, 1000);
    </script>
</body>
</html>
