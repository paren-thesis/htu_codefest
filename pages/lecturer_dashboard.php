<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Lecturer Dashboard
 * Departmental Dues Management System
 */

require_once '../includes/config.php';

// Require lecturer permission
require_permission('lecturer');

$user = get_current_user_data();

// Get system statistics
$db = Database::getInstance();

// Total students
$student_count = $db->query("SELECT COUNT(*) as count FROM students")->fetch()['count'];

// Students with payments
$paid_students = $db->query("
    SELECT COUNT(DISTINCT s.student_id) as count 
    FROM students s 
    JOIN payments p ON s.student_id = p.student_id
")->fetch()['count'];

// Students without payments
$unpaid_students = $student_count - $paid_students;

// Recent students
$recent_students = $db->query("
    SELECT s.*, p.programme_name 
    FROM students s 
    JOIN programmes p ON s.programme_id = p.programme_id 
    ORDER BY s.created_at DESC 
    LIMIT 10
")->fetchAll();

// Students by programme
$students_by_programme = $db->query("
    SELECT p.programme_name, COUNT(s.student_id) as count
    FROM programmes p 
    LEFT JOIN students s ON p.programme_id = s.programme_id
    GROUP BY p.programme_id, p.programme_name
    ORDER BY count DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - <?php echo APP_NAME; ?></title>
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
        
        .sidebar {
            background-color: <?php echo COLOR_BLUE; ?>;
            min-height: calc(100vh - 76px);
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 12px 20px;
            border-radius: 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white !important;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
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
        
        .btn-primary {
            background-color: <?php echo COLOR_BLUE; ?>;
            border-color: <?php echo COLOR_BLUE; ?>;
        }
        
        .btn-primary:hover {
            background-color: #040470;
            border-color: #040470;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, <?php echo COLOR_YELLOW; ?> 0%, #e6c200 100%);
            color: #000;
            padding: 30px;
            border-radius: 15px;
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

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <h6 class="text-uppercase">Lecturer Panel</h6>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="lecturer_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="students.php">
                            <i class="fas fa-users"></i> Students
                        </a>
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                        <a class="nav-link" href="analytics.php">
                            <i class="fas fa-chart-line"></i> Analytics
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <!-- Welcome Section -->
                    <div class="welcome-section">
                        <h2><i class="fas fa-chalkboard-teacher"></i> Welcome, Lecturer!</h2>
                        <p class="mb-0">Access student information and academic reports.</p>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <div class="stat-number"><?php echo number_format($student_count); ?></div>
                                    <div class="stat-label">Total Students</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <div class="stat-number"><?php echo number_format($paid_students); ?></div>
                                    <div class="stat-label">Paid Students</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <div class="stat-number"><?php echo number_format($unpaid_students); ?></div>
                                    <div class="stat-label">Unpaid Students</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <div class="stat-number"><?php echo count($students_by_programme); ?></div>
                                    <div class="stat-label">Programmes</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-user-plus"></i> Recent Students</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_students)): ?>
                                        <p class="text-muted">No recent students</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Programme</th>
                                                        <th>Year</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_students as $student): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['surname']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['programme_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($student['academic_year']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-graduation-cap"></i> Students by Programme</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($students_by_programme)): ?>
                                        <p class="text-muted">No programme data available</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Programme</th>
                                                        <th>Students</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($students_by_programme as $programme): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($programme['programme_name']); ?></td>
                                                            <td><?php echo number_format($programme['count']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
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
                                                <i class="fas fa-search"></i> View Students
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="reports.php" class="btn btn-primary w-100">
                                                <i class="fas fa-chart-bar"></i> Generate Reports
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="analytics.php" class="btn btn-primary w-100">
                                                <i class="fas fa-chart-line"></i> View Analytics
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <a href="export.php" class="btn btn-primary w-100">
                                                <i class="fas fa-download"></i> Export Data
                                            </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 