<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($phone)) {
        redirectWithMessage('register.php', 'Please fill in all required fields.', 'error');
    }
    
    if (!validateEmail($email)) {
        redirectWithMessage('register.php', 'Please enter a valid email address.', 'error');
    }
    
    if (!validatePhone($phone)) {
        redirectWithMessage('register.php', 'Please enter a valid phone number.', 'error');
    }
    
    if ($password !== $confirm_password) {
        redirectWithMessage('register.php', 'Passwords do not match.', 'error');
    }
    
    if (strlen($password) < 6) {
        redirectWithMessage('register.php', 'Password must be at least 6 characters long.', 'error');
    }
    
    try {
        $db = getDB();
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            redirectWithMessage('register.php', 'Email already exists. Please use a different email.', 'error');
        }
        
        // Create new user
        $hashed_password = hashPassword($password);
        $stmt = $db->prepare("
            INSERT INTO users (first_name, last_name, email, password, phone, address) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$first_name, $last_name, $email, $hashed_password, $phone, $address]);
        
        redirectWithMessage('index.php', 'Registration successful! Please login with your credentials.', 'success');
        
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        redirectWithMessage('register.php', 'An error occurred during registration. Please try again.', 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Train Ticket Reservation System</title>
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
            display: flex;
            align-items: center;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }

        .register-form {
            padding: 3rem;
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
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            color: white;
        }

        .register-image {
            background: linear-gradient(135deg, var(--secondary-color), #5dade2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 3rem;
        }

        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
        }

        .required {
            color: var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="register-container">
                    <div class="row g-0">
                        <!-- Registration Form -->
                        <div class="col-lg-8">
                            <div class="register-form">
                                <div class="text-center mb-4">
                                    <h2 class="fw-bold text-primary">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </h2>
                                    <p class="text-muted">Join TrainBook and start your journey</p>
                                </div>

                                <!-- Display Messages -->
                                <?php displayMessage(); ?>

                                <form method="POST" action="register.php">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">First Name <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">Last Name <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="required">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">Password <span class="required">*</span></label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <small class="text-muted">Minimum 6 characters</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                    </div>

                                    <div class="mb-4 form-check">
                                        <input type="checkbox" class="form-check-input" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
                                        </label>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-custom btn-lg">
                                            <i class="fas fa-user-plus me-2"></i>Create Account
                                        </button>
                                    </div>

                                    <div class="text-center mt-4">
                                        <p class="mb-0">Already have an account? 
                                            <a href="index.php" class="text-primary fw-bold">Login here</a>
                                        </p>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Registration Image -->
                        <div class="col-lg-4">
                            <div class="register-image">
                                <div class="text-center">
                                    <i class="fas fa-train" style="font-size: 5rem; margin-bottom: 2rem;"></i>
                                    <h3 class="mb-3">Welcome to TrainBook!</h3>
                                    <p class="mb-4">Join thousands of travelers who trust us for their train bookings.</p>
                                    
                                    <div class="text-start">
                                        <h6 class="mb-3">Why choose us?</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><i class="fas fa-check-circle me-2"></i>Easy booking process</li>
                                            <li class="mb-2"><i class="fas fa-check-circle me-2"></i>Secure payments</li>
                                            <li class="mb-2"><i class="fas fa-check-circle me-2"></i>24/7 customer support</li>
                                            <li class="mb-2"><i class="fas fa-check-circle me-2"></i>Real-time updates</li>
                                            <li class="mb-2"><i class="fas fa-check-circle me-2"></i>Mobile friendly</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

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
