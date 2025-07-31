<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Student Dashboard
 * Departmental Dues Management System
 */

require_once '../includes/config.php';

// Require student permission
require_permission('student');

$user = get_current_user_data();

// Get student data
$db = Database::getInstance();

// Get student information
$student = $db->query("
    SELECT s.*, p.programme_name 
    FROM students s 
    JOIN programmes p ON s.programme_id = p.programme_id 
    WHERE s.user_id = ?
", [$user['user_id']])->fetch();

// Get payment history
$payments = $db->query("
    SELECT p.*, u.username as processed_by 
    FROM payments p 
    JOIN users u ON p.created_by = u.user_id 
    WHERE p.student_id = ? 
    ORDER BY p.payment_date DESC
", [$student['student_id']])->fetchAll();

// Calculate total paid
$total_paid = array_sum(array_column($payments, 'amount'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?php echo APP_NAME; ?></title>
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
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: <?php echo COLOR_ORANGE_BROWN; ?>;
            color: white;
            border: none;
            font-weight: bold;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, <?php echo COLOR_YELLOW; ?> 0%, #e6c200 100%);
            color: #000;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .profile-section {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
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
                <h2><i class="fas fa-graduation-cap"></i> Welcome, <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['surname']); ?>!</h2>
                <p class="mb-0">View your academic information and payment history.</p>
            </div>

            <!-- Student Profile -->
            <div class="profile-section">
                <h4><i class="fas fa-user"></i> Student Information</h4>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Index Number:</strong></td>
                                <td><?php echo htmlspecialchars($student['index_no']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Full Name:</strong></td>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['surname']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Programme:</strong></td>
                                <td><?php echo htmlspecialchars($student['programme_name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Academic Year:</strong></td>
                                <td><?php echo htmlspecialchars($student['academic_year']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Position:</strong></td>
                                <td><?php echo htmlspecialchars($student['position']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Start Date:</strong></td>
                                <td><?php echo $student['start_date'] ? format_date($student['start_date']) : 'Not specified'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo count($payments); ?></div>
                            <div class="stat-label">Total Payments</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo format_currency($total_paid); ?></div>
                            <div class="stat-label">Total Paid</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <div class="stat-number"><?php echo $student['academic_year']; ?></div>
                            <div class="stat-label">Academic Year</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history"></i> Payment History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <p class="text-muted">No payment records found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Receipt No</th>
                                        <th>Amount</th>
                                        <th>Payment Date</th>
                                        <th>Academic Year</th>
                                        <th>Processed By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['receipt_no']); ?></td>
                                            <td><?php echo format_currency($payment['amount']); ?></td>
                                            <td><?php echo format_date($payment['payment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['academic_year']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['processed_by']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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
                                <div class="col-md-4 mb-2">
                                    <a href="profile.php" class="btn btn-primary w-100">
                                        <i class="fas fa-user-edit"></i> Update Profile
                                    </a>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <a href="receipts.php" class="btn btn-primary w-100">
                                        <i class="fas fa-download"></i> Download Receipts
                                    </a>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <a href="contact.php" class="btn btn-primary w-100">
                                        <i class="fas fa-envelope"></i> Contact Support
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