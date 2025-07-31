<?php
/**
 * HTU COMPSSA CODEFEST 2025 - General Dashboard
 * Departmental Dues Management System
 */

require_once '../includes/config.php';

// Require login
require_login();

$user = get_current_user_data();

// Redirect to appropriate dashboard based on role
switch ($user['role_name']) {
    case 'administrator':
        header('Location: admin_dashboard.php');
        exit();
    case 'supervisor':
        header('Location: supervisor_dashboard.php');
        exit();
    case 'cashier':
        header('Location: cashier_dashboard.php');
        exit();
    case 'lecturer':
        header('Location: lecturer_dashboard.php');
        exit();
    case 'student':
        header('Location: student_dashboard.php');
        exit();
    default:
        // Show general dashboard for unknown roles
        break;
}

// Get basic system statistics
$db = Database::getInstance();
$student_count = $db->query("SELECT COUNT(*) as count FROM students")->fetch()['count'];
$payment_count = $db->query("SELECT COUNT(*) as count FROM payments")->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        
        .navbar {
            background-color: <?php echo COLOR_ORANGE_BROWN; ?> !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card {
            background: linear-gradient(135deg, <?php echo COLOR_BLUE; ?> 0%, #040470 100%);
            color: white;
        }
        
        .stat-card .card-body {
            padding: 25px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, <?php echo COLOR_YELLOW; ?> 0%, #e6c200 100%);
            color: #000;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background-color: <?php echo COLOR_BLUE; ?>;
            border-color: <?php echo COLOR_BLUE; ?>;
        }
        
        .btn-primary:hover {
            background-color: #040470;
            border-color: #040470;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <div class="navbar-brand">
                <img src="../assets/Logo_Worldskills_Ghana.png" alt="Logo" height="40" class="me-2">
                <?php echo APP_NAME; ?>
            </div>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2><i class="fas fa-home"></i> Welcome to <?php echo APP_NAME; ?>!</h2>
                <p class="mb-0">You are logged in as: <strong><?php echo htmlspecialchars($user['role_name']); ?></strong></p>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo number_format($student_count); ?></div>
                            <div class="stat-label">Total Students</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo number_format($payment_count); ?></div>
                            <div class="stat-label">Total Payments</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> System Information</h5>
                </div>
                <div class="card-body">
                    <p>Welcome to the Departmental Dues Management System. This system allows you to:</p>
                    <ul>
                        <li>View student information and payment records</li>
                        <li>Process dues payments</li>
                        <li>Generate reports and analytics</li>
                        <li>Manage user accounts and permissions</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Note:</strong> If you're not seeing the appropriate dashboard for your role, 
                        please contact the system administrator to update your user permissions.
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="students.php" class="btn btn-primary w-100">
                                        <i class="fas fa-users"></i> View Students
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="payments.php" class="btn btn-primary w-100">
                                        <i class="fas fa-credit-card"></i> View Payments
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="reports.php" class="btn btn-primary w-100">
                                        <i class="fas fa-chart-bar"></i> Reports
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="profile.php" class="btn btn-primary w-100">
                                        <i class="fas fa-user-cog"></i> Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 