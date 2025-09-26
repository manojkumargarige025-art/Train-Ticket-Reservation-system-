<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirectWithMessage('../index.php', 'Please login to book trains.', 'error');
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Get train ID from URL
$train_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$train_id) {
    redirectWithMessage('search_trains.php', 'Invalid train selection.', 'error');
}

// Get train details
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM trains WHERE id = ? AND status = 'active'");
    $stmt->execute([$train_id]);
    $train = $stmt->fetch();
    
    if (!$train) {
        redirectWithMessage('search_trains.php', 'Train not found or not available.', 'error');
    }
    
    // Check if train has available seats
    if ($train['available_seats'] <= 0) {
        redirectWithMessage('search_trains.php', 'No seats available for this train.', 'error');
    }
    
} catch (Exception $e) {
    error_log("Get train error: " . $e->getMessage());
    redirectWithMessage('search_trains.php', 'Error loading train details.', 'error');
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $passenger_name = sanitizeInput($_POST['passenger_name']);
    $passenger_age = (int)$_POST['passenger_age'];
    $passenger_gender = sanitizeInput($_POST['passenger_gender']);
    $seat_preference = sanitizeInput($_POST['seat_preference']);
    
    // Validation
    if (empty($passenger_name) || $passenger_age < 1 || $passenger_age > 120 || empty($passenger_gender)) {
        $error_message = 'Please fill all required fields correctly.';
    } else {
        try {
            $db = getDB();
            
            // Start transaction
            $db->beginTransaction();
            
            // Check seat availability again
            $stmt = $db->prepare("SELECT available_seats FROM trains WHERE id = ? FOR UPDATE");
            $stmt->execute([$train_id]);
            $current_seats = $stmt->fetch()['available_seats'];
            
            if ($current_seats <= 0) {
                throw new Exception('No seats available');
            }
            
            // Generate booking ID
            $booking_id = 'BK' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            
            // Create booking (pending admin approval)
            $stmt = $db->prepare("
                INSERT INTO bookings (booking_id, user_id, train_id, passenger_name, passenger_age, 
                                    passenger_gender, seat_number, journey_date, total_fare, 
                                    payment_status, booking_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')
            ");
            
            // Generate seat number
            $seat_number = $seat_preference . rand(1, 50);
            
            $stmt->execute([
                $booking_id,
                $user_id,
                $train_id,
                $passenger_name,
                $passenger_age,
                $passenger_gender,
                $seat_number,
                $train['journey_date'],
                $train['fare_per_seat']
            ]);
            
            // Don't reduce seats until admin approval
            // $stmt = $db->prepare("UPDATE trains SET available_seats = available_seats - 1 WHERE id = ?");
            // $stmt->execute([$train_id]);
            
            // Commit transaction
            $db->commit();
            
            // Redirect to success page
            redirectWithMessage('my_bookings.php', 'Booking submitted successfully! Booking ID: ' . $booking_id . ' - Waiting for admin approval.', 'success');
            
        } catch (Exception $e) {
            $db->rollback();
            error_log("Booking error: " . $e->getMessage());
            $error_message = 'Booking failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Train - TrainBook</title>
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

        .train-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            border: none;
            padding: 12px 30px;
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
                        <span class="navbar-brand mb-0 h1">Book Train</span>
                        <div class="d-flex">
                            <span class="badge bg-primary me-3"><?php echo htmlspecialchars($user_name); ?></span>
                            <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Display Messages -->
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Train Information -->
                <div class="card train-info-card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-2"><?php echo htmlspecialchars($train['train_name']); ?></h4>
                                <p class="mb-1"><?php echo htmlspecialchars($train['train_number']); ?></p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <strong><?php echo htmlspecialchars($train['source_station']); ?></strong>
                                        </p>
                                        <small>Departure: <?php echo formatTime($train['departure_time']); ?></small>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <strong><?php echo htmlspecialchars($train['destination_station']); ?></strong>
                                        </p>
                                        <small>Arrival: <?php echo formatTime($train['arrival_time']); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <h3 class="mb-0"><?php echo formatCurrency($train['fare_per_seat']); ?></h3>
                                <small>per seat</small>
                                <div class="mt-2">
                                    <span class="badge bg-success"><?php echo $train['available_seats']; ?> seats available</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Passenger Details</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="book_train.php?id=<?php echo $train_id; ?>">
                            <input type="hidden" name="action" value="book">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="passenger_name" class="form-label">Passenger Name <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="passenger_name" name="passenger_name" 
                                           value="<?php echo htmlspecialchars($user_name); ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="passenger_age" class="form-label">Age <span class="required">*</span></label>
                                    <input type="number" class="form-control" id="passenger_age" name="passenger_age" 
                                           min="1" max="120" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="passenger_gender" class="form-label">Gender <span class="required">*</span></label>
                                    <select class="form-control" id="passenger_gender" name="passenger_gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="seat_preference" class="form-label">Seat Preference</label>
                                    <select class="form-control" id="seat_preference" name="seat_preference">
                                        <option value="A">A (Window)</option>
                                        <option value="B">B (Aisle)</option>
                                        <option value="C">C (Middle)</option>
                                        <option value="D">D (Aisle)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Journey Date</label>
                                    <input type="text" class="form-control" value="<?php echo formatDate($train['journey_date']); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle me-2"></i>Booking Summary</h6>
                                        <p class="mb-1"><strong>Train:</strong> <?php echo htmlspecialchars($train['train_name']); ?> (<?php echo htmlspecialchars($train['train_number']); ?>)</p>
                                        <p class="mb-1"><strong>Route:</strong> <?php echo htmlspecialchars($train['source_station']); ?> → <?php echo htmlspecialchars($train['destination_station']); ?></p>
                                        <p class="mb-1"><strong>Date:</strong> <?php echo formatDate($train['journey_date']); ?></p>
                                        <p class="mb-1"><strong>Time:</strong> <?php echo formatTime($train['departure_time']); ?> - <?php echo formatTime($train['arrival_time']); ?></p>
                                        <p class="mb-0"><strong>Total Fare:</strong> <?php echo formatCurrency($train['fare_per_seat']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="search_trains.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Search
                                </a>
                                <button type="submit" class="btn btn-custom">
                                    <i class="fas fa-credit-card me-2"></i>Confirm Booking
                                </button>
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

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const passengerName = document.getElementById('passenger_name').value.trim();
            const passengerAge = document.getElementById('passenger_age').value;
            const passengerGender = document.getElementById('passenger_gender').value;
            
            if (!passengerName || !passengerAge || !passengerGender) {
                e.preventDefault();
                alert('Please fill all required fields.');
                return false;
            }
            
            if (passengerAge < 1 || passengerAge > 120) {
                e.preventDefault();
                alert('Please enter a valid age (1-120).');
                return false;
            }
            
            // Confirm booking
            if (!confirm('Are you sure you want to book this train? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
