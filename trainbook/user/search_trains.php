<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirectWithMessage('../index.php', 'Please login to search trains.', 'error');
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get search parameters
$source = $_GET['source'] ?? '';
$destination = $_GET['destination'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

$trains = [];
$search_performed = false;

// Perform search if parameters are provided
if (!empty($source) && !empty($destination)) {
    $search_performed = true;
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM trains 
            WHERE source_station LIKE ? 
            AND destination_station LIKE ? 
            AND journey_date = ? 
            AND status = 'active' 
            AND available_seats > 0
            ORDER BY departure_time ASC
        ");
        $stmt->execute(["%$source%", "%$destination%", $date]);
        $trains = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Search trains error: " . $e->getMessage());
        $trains = [];
    }
}

// Get all stations for dropdown
$stations = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT DISTINCT source_station as station FROM trains UNION SELECT DISTINCT destination_station as station FROM trains ORDER BY station");
    $stations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    error_log("Get stations error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Trains - Train Ticket Reservation System</title>
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

        .search-card {
            background: linear-gradient(135deg, var(--secondary-color), #5dade2);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
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

        .train-card {
            border-left: 4px solid var(--secondary-color);
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .train-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .duration {
            background: var(--light-bg);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            color: var(--primary-color);
        }

        .fare {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--success-color);
        }

        .seats-available {
            color: var(--success-color);
            font-weight: 600;
        }

        .seats-low {
            color: var(--warning-color);
            font-weight: 600;
        }

        .seats-none {
            color: var(--accent-color);
            font-weight: 600;
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
                        <small class="text-white-50">Search Trains</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="search_trains.php">
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
                        <span class="navbar-brand mb-0 h1">Search Trains</span>
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

                <!-- Search Form - RedBus Style -->
                <div class="search-card">
                    <h3 class="mb-4"><i class="fas fa-search me-2"></i>Book Train Tickets</h3>
                    <form method="GET" action="search_trains.php">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="source" class="form-label">From</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <select class="form-control" id="source" name="source" required>
                                        <option value="">Select Source Station</option>
                                        <?php foreach ($stations as $station): ?>
                                            <option value="<?php echo htmlspecialchars($station); ?>" 
                                                    <?php echo $source === $station ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($station); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="destination" class="form-label">To</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <select class="form-control" id="destination" name="destination" required>
                                        <option value="">Select Destination Station</option>
                                        <?php foreach ($stations as $station): ?>
                                            <option value="<?php echo htmlspecialchars($station); ?>" 
                                                    <?php echo $destination === $station ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($station); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="date" class="form-label">Journey Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control" id="date" name="date" 
                                           value="<?php echo htmlspecialchars($date); ?>" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="fas fa-search me-2"></i>Search Trains
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Date Selection -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <label class="form-label">Quick Date Selection:</label>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDate(0)">Today</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDate(1)">Tomorrow</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDate(7)">Next Week</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDate(30)">Next Month</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Search Results -->
                <?php if ($search_performed): ?>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Search Results 
                                <?php if (!empty($source) && !empty($destination)): ?>
                                    (<?php echo htmlspecialchars($source); ?> → <?php echo htmlspecialchars($destination); ?>)
                                <?php endif; ?>
                            </h5>
                            <span class="badge bg-primary"><?php echo count($trains); ?> trains found</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($trains)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-train"></i>
                                    <h5>No Trains Found</h5>
                                    <p>Sorry, no trains are available for your selected route and date.</p>
                                    <p class="text-muted">Try selecting different stations or date.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($trains as $train): ?>
                                    <div class="train-card card p-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h5 class="mb-0 me-3"><?php echo htmlspecialchars($train['train_name']); ?></h5>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($train['train_number']); ?></span>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="mb-1">
                                                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                            <strong><?php echo htmlspecialchars($train['source_station']); ?></strong>
                                                        </p>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            Departure: <?php echo formatTime($train['departure_time']); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-1">
                                                            <i class="fas fa-map-marker-alt text-success me-2"></i>
                                                            <strong><?php echo htmlspecialchars($train['destination_station']); ?></strong>
                                                        </p>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            Arrival: <?php echo formatTime($train['arrival_time']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="duration">
                                                        <i class="fas fa-hourglass-half me-1"></i>
                                                        <?php echo calculateDuration($train['departure_time'], $train['arrival_time']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <div class="mb-3">
                                                    <div class="fare"><?php echo formatCurrency($train['fare_per_seat']); ?></div>
                                                    <small class="text-muted">per seat</small>
                                                </div>
                                                <div class="mb-3">
                                                    <?php
                                                    $seats = $train['available_seats'];
                                                    $seatClass = 'seats-available';
                                                    if ($seats <= 5) $seatClass = 'seats-low';
                                                    if ($seats == 0) $seatClass = 'seats-none';
                                                    ?>
                                                    <span class="<?php echo $seatClass; ?>">
                                                        <i class="fas fa-chair me-1"></i>
                                                        <?php echo $seats; ?> seats available
                                                    </span>
                                                </div>
                                                <div class="d-grid gap-2">
                                                    <?php if ($seats > 0): ?>
                                                        <a href="book_train.php?id=<?php echo $train['id']; ?>" 
                                                           class="btn btn-custom">
                                                            <i class="fas fa-ticket-alt me-2"></i>Book Now
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-secondary" disabled>
                                                            <i class="fas fa-times me-2"></i>Fully Booked
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4>Search for Trains</h4>
                            <p class="text-muted">Select your source, destination, and journey date to find available trains.</p>
                        </div>
                    </div>
                <?php endif; ?>
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

        // Quick date selection
        function setDate(days) {
            const today = new Date();
            const targetDate = new Date(today.getTime() + (days * 24 * 60 * 60 * 1000));
            const dateString = targetDate.toISOString().split('T')[0];
            document.getElementById('date').value = dateString;
        }
        
        // Sort trains by price
        function sortTrains(type) {
            const trainCards = document.querySelectorAll('.train-card');
            const sortedCards = Array.from(trainCards).sort((a, b) => {
                if (type === 'price') {
                    const priceA = parseFloat(a.querySelector('.fare').textContent.replace('₹', '').replace(',', ''));
                    const priceB = parseFloat(b.querySelector('.fare').textContent.replace('₹', '').replace(',', ''));
                    return priceA - priceB;
                }
                return 0;
            });
            
            const container = document.querySelector('.card-body');
            sortedCards.forEach(card => container.appendChild(card));
        }
        
        // Show train details
        function showTrainDetails(trainId) {
            alert('Train details for ID: ' + trainId + '\n\nThis would show detailed information about the train including:\n- Route map\n- Stops\n- Amenities\n- Seat map\n- Reviews');
        }
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const source = document.getElementById('source').value;
            const destination = document.getElementById('destination').value;
            
            if (source === destination) {
                e.preventDefault();
                alert('Source and destination cannot be the same!');
                return false;
            }
        });
        
        // Auto-refresh search results every 30 seconds
        setInterval(function() {
            if (window.location.search.includes('source=') && window.location.search.includes('destination=')) {
                window.location.reload();
            }
        }, 30000);
    </script>
</body>
</html>
