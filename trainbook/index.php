<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Train Ticket Reservation System - Capstone Project</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .hero-section {
            position: relative;
            background: linear-gradient(180deg, rgba(10,31,73,0.95) 0%, rgba(10,31,73,0.85) 40%, rgba(10,31,73,0.85) 100%),
                        radial-gradient(circle at 20% 10%, rgba(77,163,255,0.25), transparent 40%),
                        radial-gradient(circle at 80% 20%, rgba(155,89,182,0.25), transparent 45%),
                        #0a1f49;
            color: white;
            padding: 80px 0 60px;
            overflow: hidden;
        }
        .hero-rail {
            position: absolute; bottom: -40px; left: -5%; right: -5%; height: 240px; opacity: 0.12;
            background: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1200 200%22%3E%3Cpath d=%22M0,120 C200,80 400,120 600,100 C800,80 1000,100 1200,70 L1200,200 L0,200 Z%22 fill=%22%23ffffff%22/%3E%3C/svg%3E') center/cover no-repeat;
        }

        .search-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            padding: 22px;
        }
        .search-card .form-control, .search-card .form-select {
            border-radius: 12px; padding: 12px 14px;
        }
        .btn-cta {
            background: linear-gradient(90deg, #4da3ff, #9b59b6);
            border: none; color: #fff; font-weight: 600;
        }
        .btn-cta:hover { filter: brightness(1.05); color: #fff; }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            color: white;
        }

        .stats-section {
            background: var(--light-bg);
            padding: 60px 0;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--secondary-color);
            display: block;
        }

        .navbar-custom {
            background: rgba(255,255,255,0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .footer {
            background: var(--primary-color);
            color: white;
            padding: 40px 0 20px;
        }

        .login-modal .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .login-form {
            padding: 2rem;
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

        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top navbar-custom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-train me-2"></i>TrainBook
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary ms-2" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-rail"></div>
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 order-lg-2 text-lg-start text-center">
                    <h1 class="display-5 fw-bold mb-3">Book your next train ticket</h1>
                    <p class="lead mb-4">Search, compare and reserve seats in minutes. Simple, fast rail booking across India.</p>
                    <a href="#features" class="btn btn-light me-2"><i class="fas fa-info-circle me-1"></i> Learn More</a>
                    <a href="login.php" class="btn btn-cta"><i class="fas fa-user-plus me-1"></i> Get Started</a>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <div class="search-card">
                        <form method="get" action="user/search_trains.php">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">From</label>
                                    <input type="text" class="form-control" name="source" placeholder="Enter source station">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">To</label>
                                    <input type="text" class="form-control" name="destination" placeholder="Enter destination">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Journey Date</label>
                                    <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Passengers</label>
                                    <select class="form-select" name="passengers">
                                        <?php for($i=1; $i<=6; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-12 d-grid d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-cta px-4"><i class="fas fa-search me-2"></i>Get times & tickets</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-4 fw-bold mb-3">Why Choose Our Platform?</h2>
                    <p class="lead text-muted">Experience the best in train ticket booking with our advanced features and user-friendly interface.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Easy Search</h4>
                        <p class="text-muted">Find trains between any two stations with our advanced search functionality. Get real-time availability and pricing.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Secure Payment</h4>
                        <p class="text-muted">Safe and secure payment processing with multiple payment options. Your financial data is always protected.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Mobile Friendly</h4>
                        <p class="text-muted">Responsive design that works perfectly on all devices. Book tickets on the go from your smartphone.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Real-time Updates</h4>
                        <p class="text-muted">Get instant updates on train schedules, delays, and cancellations. Stay informed about your journey.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h4 class="fw-bold mb-3">User Management</h4>
                        <p class="text-muted">Comprehensive user profiles with booking history, preferences, and easy account management.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Admin Panel</h4>
                        <p class="text-muted">Complete administrative control for managing trains, routes, bookings, and system operations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number">10+</span>
                        <h5>Active Trains</h5>
                        <p class="text-muted">Multiple routes across the country</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number">1000+</span>
                        <h5>Happy Customers</h5>
                        <p class="text-muted">Satisfied users worldwide</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number">99.9%</span>
                        <h5>Uptime</h5>
                        <p class="text-muted">Reliable service availability</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <h5>Support</h5>
                        <p class="text-muted">Round-the-clock assistance</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">About Our Platform</h2>
                    <p class="lead mb-4">This Train Ticket Reservation System is a comprehensive capstone project built using modern web technologies including PHP, MySQL, HTML5, CSS3, and JavaScript.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-check-circle text-success me-2"></i>User-Friendly</h5>
                            <p class="text-muted">Intuitive interface designed for all users</p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-check-circle text-success me-2"></i>Secure</h5>
                            <p class="text-muted">Advanced security measures implemented</p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-check-circle text-success me-2"></i>Scalable</h5>
                            <p class="text-muted">Built to handle growing user base</p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-check-circle text-success me-2"></i>Modern</h5>
                            <p class="text-muted">Latest web technologies and practices</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-train" style="font-size: 8rem; color: var(--secondary-color);"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-3">Get In Touch</h2>
                    <p class="lead text-muted">Have questions or need support? We're here to help!</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h5>Email Support</h5>
                    <p class="text-muted">support@trainbook.com</p>
                </div>
                <div class="col-lg-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h5>Phone Support</h5>
                    <p class="text-muted">+1 (555) 123-4567</p>
                </div>
                <div class="col-lg-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5>Support Hours</h5>
                    <p class="text-muted">24/7 Available</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h5><i class="fas fa-train me-2"></i>TrainBook</h5>
                    <p class="mb-3">Your trusted partner for train ticket reservations. Book with confidence and travel with ease.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="#features" class="text-white-50 text-decoration-none">Features</a></li>
                        <li><a href="#about" class="text-white-50 text-decoration-none">About</a></li>
                        <li><a href="#contact" class="text-white-50 text-decoration-none">Contact</a></li>
                        <li><a href="login.php" class="text-white-50 text-decoration-none">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6>Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50 text-decoration-none">Help Center</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Privacy Policy</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Terms of Service</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 TrainBook. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Capstone Project - Train Ticket Reservation System</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Login Modal removed; using dedicated login page -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Show success/error messages
        <?php if (isset($_SESSION['message'])): ?>
            alert('<?php echo $_SESSION['message']; unset($_SESSION['message']); ?>');
        <?php endif; ?>
    </script>
</body>
</html>
