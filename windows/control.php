<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Control Window
 * Main navigation hub for the application
 * 
 * Features:
 * - Links to other windows (Data, Payment, Report)
 * - Role-based menu access
 * - Option to close the application
 * - Logout functionality
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    redirect('login.php');
}

// Get user information
$user_role = $_SESSION['user_role'] ?? '';
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';

// Define role-based permissions
$role_permissions = [
    'administrator' => ['data', 'payment', 'report', 'users'],
    'supervisor' => ['data', 'payment', 'report'],
    'cashier' => ['payment'],
    'lecturer' => ['data', 'report'],
    'student' => ['data']
];

$user_permissions = $role_permissions[$user_role] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Control Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header with Logo and Title -->
    <header class="app-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img src="../assets/Logo_Worldskills_Ghana.png" alt="HTU Logo" class="logo">
                </div>
                <div class="col-md-8 text-center">
                    <h1 class="app-title"><?php echo APP_NAME; ?></h1>
                </div>
                <div class="col-md-2 text-end">
                    <div class="user-info">
                        <span>Welcome, <?php echo sanitizeInput($username); ?></span>
                        <br>
                        <small><?php echo ucfirst($user_role); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-4" style="color: var(--blue); font-size: 28px; font-weight: bold;">
                        Control Panel
                    </h2>
                    
                    <!-- User Info Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>User Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Username:</strong> <?php echo sanitizeInput($username); ?></p>
                                    <p><strong>Email:</strong> <?php echo sanitizeInput($email); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Role:</strong> <?php echo ucfirst($user_role); ?></p>
                                    <p><strong>Login Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Cards -->
                    <div class="row">
                        <?php if (in_array('data', $user_permissions)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-users me-2"></i>
                                        <?php echo ($_SESSION['user_role'] === 'student') ? 'Student Records' : 'Student Data'; ?>
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x mb-3" style="color: var(--blue);"></i>
                                    <?php if ($_SESSION['user_role'] === 'student'): ?>
                                        <p>View student information and search records (view only).</p>
                                    <?php else: ?>
                                        <p>Manage student information, import CSV data, and search records.</p>
                                    <?php endif; ?>
                                    <a href="data.php" class="btn btn-primary w-100">
                                        <?php echo ($_SESSION['user_role'] === 'student') ? 'View Records' : 'Access Data Window'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (in_array('payment', $user_permissions)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-credit-card me-2"></i>
                                        <?php echo ($_SESSION['user_role'] === 'supervisor') ? 'Payment History' : 'Payment Processing'; ?>
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <i class="fas fa-credit-card fa-3x mb-3" style="color: var(--orange-brown);"></i>
                                    <?php if ($_SESSION['user_role'] === 'supervisor'): ?>
                                        <p>View payment history and track dues payments (view only).</p>
                                    <?php else: ?>
                                        <p>Process dues payments, generate receipts, and track payment history.</p>
                                    <?php endif; ?>
                                    <a href="payment.php" class="btn btn-primary w-100">
                                        <?php echo ($_SESSION['user_role'] === 'supervisor') ? 'View Payments' : 'Access Payment Window'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (in_array('report', $user_permissions)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Reports</h5>
                                </div>
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-bar fa-3x mb-3" style="color: var(--yellow);"></i>
                                    <p>Generate reports, view analytics, and export data.</p>
                                    <a href="report.php" class="btn btn-primary w-100">Access Report Window</a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (in_array('users', $user_permissions)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>User Management</h5>
                                </div>
                                <div class="card-body text-center">
                                    <i class="fas fa-user-cog fa-3x mb-3" style="color: var(--blue);"></i>
                                    <p>Manage user accounts, roles, and system settings.</p>
                                    <a href="users.php" class="btn btn-primary w-100">Access User Management</a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="?action=logout" class="btn btn-secondary" 
                                   onclick="return confirm('Are you sure you want to logout?')">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-danger" onclick="closeApplication()">
                                    <i class="fas fa-times me-2"></i>Close Application
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Quick Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <div class="stat-item">
                                                <i class="fas fa-users fa-2x mb-2" style="color: var(--blue);"></i>
                                                <h4>0</h4>
                                                <p>Total Students</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="stat-item">
                                                <i class="fas fa-credit-card fa-2x mb-2" style="color: var(--orange-brown);"></i>
                                                <h4>0</h4>
                                                <p>Total Payments</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="stat-item">
                                                <i class="fas fa-money-bill-wave fa-2x mb-2" style="color: var(--yellow);"></i>
                                                <h4>GH₵ 0.00</h4>
                                                <p>Total Revenue</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="stat-item">
                                                <i class="fas fa-calendar fa-2x mb-2" style="color: var(--blue);"></i>
                                                <h4><?php echo date('Y'); ?></h4>
                                                <p>Academic Year</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function closeApplication() {
            if (confirm('Are you sure you want to close the application?')) {
                window.close();
                // Fallback for browsers that don't allow window.close()
                window.location.href = 'login.php';
            }
        }
    </script>
</body>
</html> 