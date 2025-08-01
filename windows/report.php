<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Report Window
 * Reporting and analytics for dues and students
 * 
 * Features:
 * - Generate payment summaries
 * - Student statistics
 * - Filter and export data
 * - Visualize analytics
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and has permission
if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['administrator', 'supervisor', 'lecturer'])) {
    redirect('login.php');
}

$error_message = '';
$success_message = '';
$summary = [];

// Handle export (CSV)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    exportCSV();
}

function exportCSV() {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="student_payment_summary.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Index No', 'Name', 'Email', 'Academic Year', 'Programme', 'Position', 'Total Paid', 'Payment Count', 'Created At']);
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT s.index_no, CONCAT(s.first_name, ' ', s.surname) AS name, s.email, s.academic_year, p.programme_name, s.position, COALESCE(SUM(pay.amount), 0) as total_paid, COUNT(pay.payment_id) as payment_count, s.created_at FROM students s LEFT JOIN programmes p ON s.programme_id = p.programme_id LEFT JOIN payments pay ON s.student_id = pay.student_id GROUP BY s.student_id, s.index_no, s.first_name, s.surname, s.email, s.academic_year, p.programme_name, s.position, s.created_at");
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        fputcsv($output, [$row['index_no'], $row['name'], $row['email'], $row['academic_year'], $row['programme_name'], $row['position'], $row['total_paid'], $row['payment_count'], $row['created_at']]);
    }
    fclose($output);
    exit();
}

// Fetch payment summary
try {
    $pdo = getDBConnection();
    $sql = "SELECT s.index_no, s.first_name, s.surname, s.email, s.academic_year, p.programme_name, s.position, COALESCE(SUM(pay.amount), 0) as total_paid, COUNT(pay.payment_id) as payment_count, s.created_at FROM students s LEFT JOIN programmes p ON s.programme_id = p.programme_id LEFT JOIN payments pay ON s.student_id = pay.student_id GROUP BY s.student_id, s.index_no, s.first_name, s.surname, s.email, s.academic_year, p.programme_name, s.position, s.created_at ORDER BY s.first_name, s.surname";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $summary = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = 'Failed to fetch report: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Reports</title>
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
                    <a href="control.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Control
                    </a>
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
                        Reports & Analytics
                    </h2>
                    
                    <!-- Display Messages -->
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <!-- Export Button -->
                    <div class="mb-4 text-end">
                        <a href="?export=csv" class="btn btn-primary">
                            <i class="fas fa-file-csv me-2"></i>Export CSV
                        </a>
                    </div>
                    
                    <!-- Payment Summary Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Student Payment Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Index No</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Academic Year</th>
                                            <th>Programme</th>
                                            <th>Position</th>
                                            <th>Total Paid</th>
                                            <th>Payment Count</th>
                                            <th>Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($summary)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center">No data found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($summary as $row): ?>
                                                <tr>
                                                    <td><?php echo sanitizeInput($row['index_no']); ?></td>
                                                    <td><?php echo sanitizeInput($row['first_name'] . ' ' . $row['surname']); ?></td>
                                                    <td><?php echo sanitizeInput($row['email']); ?></td>
                                                    <td><?php echo sanitizeInput($row['academic_year']); ?></td>
                                                    <td><?php echo sanitizeInput($row['programme_name']); ?></td>
                                                    <td><?php echo sanitizeInput($row['position']); ?></td>
                                                    <td><?php echo formatCurrency($row['total_paid']); ?></td>
                                                    <td><?php echo sanitizeInput($row['payment_count']); ?></td>
                                                    <td><?php echo sanitizeInput($row['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>