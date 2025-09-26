<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    redirectWithMessage('../index.php', 'Please login as admin.', 'error');
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <?php displayMessage(); ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Reports</h5>
                <a href="dashboard.php" class="btn btn-sm btn-secondary">Back to Dashboard</a>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">This page is a placeholder. Add your reports here.</p>
                <ul class="mb-0">
                    <li>Total bookings by status</li>
                    <li>Revenue by month</li>
                    <li>Top routes and trains</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>


