<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BCMS Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Base styles */
        body {
            background-color: #ffffff;
            color: #000000;
            font-family: 'Segoe UI', Arial, sans-serif;
            padding-top: 60px;
            line-height: 1.6;
        }

        /* Navigation */
        .top-nav {
            background: #000000;
            padding: 0.8rem 2rem;
            color: #ffffff;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .user-info {
            color: #ffffff;
        }

        .user-info h5 {
            font-size: 1.1rem;
            font-weight: 500;
            margin: 0;
        }

        .user-info small {
            color: #cccccc;
            font-size: 0.85rem;
        }

        .logout-btn {
            color: #ffffff;
            background-color: #000000;
            padding: 0.5rem 1rem;
            border: 1px solid #ffffff;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            color: #000000;
            background-color: #ffffff;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Cards and Containers */
        .card {
            background: #ffffff;
            border: 1px solid #000000;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: #000000;
            color: #ffffff;
            padding: 1rem;
            border-bottom: 1px solid #000000;
        }

        /* Forms */
        .form-control {
            border: 1px solid #000000;
            border-radius: 4px;
            padding: 0.5rem;
        }

        .form-control:focus {
            border-color: #000000;
            box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
        }

        .form-label {
            color: #000000;
            font-weight: 500;
        }

        /* Buttons */
        .btn-primary {
            background-color: #000000;
            border: 1px solid #000000;
            color: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #ffffff;
            color: #000000;
        }

        .btn-secondary {
            background-color: #ffffff;
            border: 1px solid #000000;
            color: #000000;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #000000;
            color: #ffffff;
        }

        /* Tables */
        .table {
            border: 1px solid #000000;
        }

        .table th {
            background-color: #000000;
            color: #ffffff;
            border: 1px solid #000000;
        }

        .table td {
            border: 1px solid #000000;
        }

        /* Alerts */
        .alert {
            border: 1px solid #000000;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #ffffff;
            color: #000000;
            border-color: #000000;
        }

        .alert-danger {
            background-color: #ffffff;
            color: #dc3545;
            border-color: #dc3545;
        }

        /* Utilities */
        .text-muted {
            color: #666666 !important;
        }

        .border {
            border: 1px solid #000000 !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .top-nav {
                padding: 0.8rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation for all users -->
    <nav class="top-nav d-flex justify-content-between align-items-center">
        <div class="user-info">
            <h5 class="mb-0">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></h5>
            <small class="text-muted"><?php echo ucfirst($_SESSION["user_type"]); ?></small>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 main-content">
                <!-- Page content will be inserted here -->
            </div>
        </div>
    </div>
</body>
</html> 