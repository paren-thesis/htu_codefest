<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Payment Window
 * Dues payment processing and history
 * 
 * Features:
 * - Process dues payments
 * - Generate receipts
 * - Track payment history
 * - Payment validation
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and has permission
if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['administrator', 'cashier', 'supervisor'])) {
    redirect('login.php');
}

$error_message = '';
$success_message = '';
$payments = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'make_payment':
            // Only administrators and cashiers can process payments
            if (in_array($_SESSION['user_role'], ['administrator', 'cashier'])) {
                handleMakePayment();
            } else {
                $error_message = 'You do not have permission to process payments.';
            }
            break;
    }
}

function handleMakePayment() {
    global $error_message, $success_message;
    
    $student_id = sanitizeInput($_POST['student_id'] ?? '');
    $amount = sanitizeInput($_POST['amount'] ?? '');
    $payment_date = sanitizeInput($_POST['payment_date'] ?? date('Y-m-d'));
    $academic_year = sanitizeInput($_POST['academic_year'] ?? '');
    $created_by = $_SESSION['user_id'] ?? null;
    
    if (empty($student_id) || empty($amount) || empty($payment_date) || empty($academic_year)) {
        $error_message = 'Please fill in all required fields.';
        return;
    }
    if (!is_numeric($amount) || $amount <= 0) {
        $error_message = 'Amount must be a positive number.';
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if student exists
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();
        if (!$student) {
            $error_message = 'Student not found.';
            return;
        }
        
        // Generate unique receipt number
        $receipt_no = generateReceiptNumber();
        
        // Insert payment
        $stmt = $pdo->prepare("INSERT INTO payments (student_id, amount, receipt_no, payment_date, academic_year, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $amount, $receipt_no, $payment_date, $academic_year, $created_by]);
        
        $success_message = 'Payment processed successfully! Receipt No: ' . $receipt_no;
        
    } catch (PDOException $e) {
        $error_message = 'Failed to process payment: ' . $e->getMessage();
    }
}

// Get students for dropdown
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT student_id, index_no, first_name, surname FROM students ORDER BY first_name, surname");
    $stmt->execute();
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
}

// Get payment history (latest 20 payments)
try {
    $pdo = getDBConnection();
    $sql = "SELECT p.*, s.index_no, s.first_name, s.surname, u.username AS cashier
            FROM payments p
            LEFT JOIN students s ON p.student_id = s.student_id
            LEFT JOIN users u ON p.created_by = u.user_id
            ORDER BY p.payment_date DESC, p.payment_id DESC
            LIMIT 20";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $payments = $stmt->fetchAll();
} catch (PDOException $e) {
    $payments = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Payment Processing</title>
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
                        Payment Processing
                    </h2>
                    
                    <!-- Display Messages -->
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <!-- Payment Form - Only for Administrators and Cashiers -->
                    <?php if (in_array($_SESSION['user_role'], ['administrator', 'cashier'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Process Payment</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="make_payment">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="student_id" class="form-label">Student</label>
                                            <select class="form-control" id="student_id" name="student_id" required>
                                                <option value="">Select Student</option>
                                                <?php foreach ($students as $student): ?>
                                                    <option value="<?php echo $student['student_id']; ?>">
                                                        <?php echo $student['index_no'] . ' - ' . $student['first_name'] . ' ' . $student['surname']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Amount (GH₵)</label>
                                            <input type="number" step="0.01" min="0" class="form-control" id="amount" name="amount" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="payment_date" class="form-label">Date</label>
                                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="academic_year" class="form-label">Academic Year</label>
                                            <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo getCurrentAcademicYear(); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="d-grid w-100">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-credit-card me-2"></i>Pay
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Supervisor View Only Message -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Payment History (View Only)</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Supervisor Access:</strong> You can view payment history but cannot process new payments. 
                                Only administrators and cashiers can process payments.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Payment History Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Payments</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Receipt No</th>
                                            <th>Student</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Academic Year</th>
                                            <th>Cashier</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($payments)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No payments found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($payments as $payment): ?>
                                                <tr>
                                                    <td><?php echo sanitizeInput($payment['receipt_no']); ?></td>
                                                    <td><?php echo sanitizeInput($payment['index_no'] . ' - ' . $payment['first_name'] . ' ' . $payment['surname']); ?></td>
                                                    <td><?php echo formatCurrency($payment['amount']); ?></td>
                                                    <td><?php echo sanitizeInput($payment['payment_date']); ?></td>
                                                    <td><?php echo sanitizeInput($payment['academic_year']); ?></td>
                                                    <td><?php echo sanitizeInput($payment['cashier']); ?></td>
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