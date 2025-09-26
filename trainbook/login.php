<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TrainBook</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
        }

        body, html { height: 100%; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0b1530;
            color: #fff;
        }

        .auth-hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            background: url('a13b29f58680b8c13b8a07992673bdbd.png') center/cover no-repeat;
            filter: saturate(1.05) contrast(1.05);
        }

        .hero-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(14,25,56,0.75) 0%, rgba(14,25,56,0.85) 60%, rgba(14,25,56,0.95) 100%);
        }

        .auth-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 960px;
            padding: 24px;
        }

        .brand {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .card-glass {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            overflow: hidden;
            color: #fff;
        }

        .intro {
            padding: 32px;
        }

        .intro h1 { font-weight: 800; letter-spacing: 0.3px; }
        .intro p { opacity: 0.9; }

        .auth-form {
            padding: 32px;
            background: rgba(4,10,30,0.55);
        }

        .form-control {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.12);
            border-color: #4da3ff;
            box-shadow: 0 0 0 0.2rem rgba(77,163,255,0.25);
            color: #fff;
        }

        .btn-gradient {
            background: linear-gradient(90deg, #4da3ff, #9b59b6);
            border: none;
            color: #fff;
            font-weight: 600;
        }
        .btn-gradient:hover { filter: brightness(1.05); color: #fff; }

        .toggle .btn { color: #cfe2ff; border-color: #4da3ff; }
        .toggle .btn.active { background: #4da3ff; color: #0b1530; }

        a.link { color: #cfe2ff; }
        a.link:hover { color: #fff; }
    </style>
    <script>
        function setLoginType(type) {
            const form = document.getElementById('loginForm');
            form.action = type === 'admin' ? 'auth/login.php?type=admin' : 'auth/login.php';
            document.getElementById('btnUser').classList.toggle('active', type==='user');
            document.getElementById('btnAdmin').classList.toggle('active', type==='admin');
        }
        document.addEventListener('DOMContentLoaded', function(){ setLoginType('user'); });
    </script>
    </head>
<body>
    <div class="auth-hero">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>

        <div class="auth-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="index.php" class="brand"><i class="fas fa-train me-2"></i>TrainBook</a>
                <a href="index.php" class="btn btn-sm btn-outline-light">Home</a>
            </div>

            <div class="card-glass row g-0">
                <div class="col-lg-6 intro">
                    <h1 class="display-6 mb-3">Book your next train ticket</h1>
                    <p class="mb-4">Simple, fast rail booking. Sign in to manage your trips, view bookings, and get real-time updates.</p>
                    <ul class="mb-0">
                        <li>Search and book in minutes</li>
                        <li>Secure payments and e-tickets</li>
                        <li>Manage bookings anytime</li>
                    </ul>
                </div>
                <div class="col-lg-6 auth-form">
                    <div class="text-center mb-3 toggle btn-group" role="group">
                        <button id="btnUser" type="button" class="btn btn-sm btn-outline-primary active" onclick="setLoginType('user')">User Login</button>
                        <button id="btnAdmin" type="button" class="btn btn-sm btn-outline-primary" onclick="setLoginType('admin')">Admin Login</button>
                    </div>
                    <form id="loginForm" method="POST" action="auth/login.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="#" class="link">Forgot password?</a>
                        </div>
                        <button class="btn btn-gradient w-100 mb-3" type="submit">Login</button>
                        <div class="text-center">
                            <small>Dont have an account? <a class="link" href="register.php">Register</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($_SESSION['message'])): ?>
    <script>
        alert('<?php echo addslashes($_SESSION['message']); ?>');
    </script>
    <?php unset($_SESSION['message']); endif; ?>
</body>
</html>


