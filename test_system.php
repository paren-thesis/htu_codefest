<?php
/**
 * HTU COMPSSA CODEFEST 2025 - System Test
 * This script tests the basic functionality of the system
 */

require_once 'includes/config.php';

$tests = [];
$overall_success = true;

// Test 1: Database Connection
try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    $tests['Database Connection'] = ['status' => 'PASS', 'message' => 'Database connection successful'];
} catch (Exception $e) {
    $tests['Database Connection'] = ['status' => 'FAIL', 'message' => 'Database connection failed: ' . $e->getMessage()];
    $overall_success = false;
}

// Test 2: Check if admin user exists
if ($overall_success) {
    try {
        $check_sql = "SELECT user_id, username, role_id FROM users WHERE username = 'admin'";
        $check_stmt = $db->query($check_sql);
        $admin_user = $check_stmt->fetch();
        
        if ($admin_user) {
            $tests['Admin User'] = ['status' => 'PASS', 'message' => 'Admin user exists (ID: ' . $admin_user['user_id'] . ')'];
        } else {
            $tests['Admin User'] = ['status' => 'FAIL', 'message' => 'Admin user not found in database'];
            $overall_success = false;
        }
    } catch (Exception $e) {
        $tests['Admin User'] = ['status' => 'FAIL', 'message' => 'Error checking admin user: ' . $e->getMessage()];
        $overall_success = false;
    }
}

// Test 3: Check if roles exist
if ($overall_success) {
    try {
        $roles_sql = "SELECT COUNT(*) as count FROM roles";
        $roles_stmt = $db->query($roles_sql);
        $roles_count = $roles_stmt->fetch()['count'];
        
        if ($roles_count >= 5) {
            $tests['User Roles'] = ['status' => 'PASS', 'message' => 'User roles configured (' . $roles_count . ' roles)'];
        } else {
            $tests['User Roles'] = ['status' => 'FAIL', 'message' => 'Expected 5 roles, found ' . $roles_count];
            $overall_success = false;
        }
    } catch (Exception $e) {
        $tests['User Roles'] = ['status' => 'FAIL', 'message' => 'Error checking roles: ' . $e->getMessage()];
        $overall_success = false;
    }
}

// Test 4: Check if programmes exist
if ($overall_success) {
    try {
        $programmes_sql = "SELECT COUNT(*) as count FROM programmes";
        $programmes_stmt = $db->query($programmes_sql);
        $programmes_count = $programmes_stmt->fetch()['count'];
        
        if ($programmes_count >= 4) {
            $tests['Programmes'] = ['status' => 'PASS', 'message' => 'Programmes configured (' . $programmes_count . ' programmes)'];
        } else {
            $tests['Programmes'] = ['status' => 'FAIL', 'message' => 'Expected 4 programmes, found ' . $programmes_count];
            $overall_success = false;
        }
    } catch (Exception $e) {
        $tests['Programmes'] = ['status' => 'FAIL', 'message' => 'Error checking programmes: ' . $e->getMessage()];
        $overall_success = false;
    }
}

// Test 5: Test password hash generation
try {
    $test_hash = password_hash('test123', PASSWORD_DEFAULT);
    $verify_result = password_verify('test123', $test_hash);
    
    if ($verify_result) {
        $tests['Password Hashing'] = ['status' => 'PASS', 'message' => 'Password hashing and verification working'];
    } else {
        $tests['Password Hashing'] = ['status' => 'FAIL', 'message' => 'Password verification failed'];
        $overall_success = false;
    }
} catch (Exception $e) {
    $tests['Password Hashing'] = ['status' => 'FAIL', 'message' => 'Password hashing error: ' . $e->getMessage()];
    $overall_success = false;
}

// Test 6: Check file permissions
$required_files = [
    'includes/config.php',
    'login.php',
    'index.php',
    'assets/Logo_Worldskills_Ghana.png'
];

$file_tests = [];
foreach ($required_files as $file) {
    if (file_exists($file)) {
        $file_tests[] = $file . ' ✓';
    } else {
        $file_tests[] = $file . ' ✗';
        $overall_success = false;
    }
}

$tests['Required Files'] = [
    'status' => $overall_success ? 'PASS' : 'FAIL',
    'message' => implode('<br>', $file_tests)
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Test - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, <?php echo COLOR_ORANGE_BROWN; ?> 0%, <?php echo COLOR_BLUE; ?> 100%);
            min-height: 100vh;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        
        .test-container {
            background-color: <?php echo COLOR_WHITE; ?>;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .test-result {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .test-pass {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .test-fail {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn-primary {
            background-color: <?php echo COLOR_BLUE; ?>;
            border-color: <?php echo COLOR_BLUE; ?>;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="text-center mb-4">
            <i class="fas fa-cogs"></i> System Test Results
        </h1>
        
        <div class="alert alert-<?php echo $overall_success ? 'success' : 'danger'; ?>">
            <h4 class="alert-heading">
                <?php if ($overall_success): ?>
                    <i class="fas fa-check-circle"></i> All Tests Passed!
                <?php else: ?>
                    <i class="fas fa-exclamation-triangle"></i> Some Tests Failed
                <?php endif; ?>
            </h4>
            <p class="mb-0">
                <?php if ($overall_success): ?>
                    Your system is ready to use! You can now login with admin/admin123.
                <?php else: ?>
                    Please fix the failed tests before using the system.
                <?php endif; ?>
            </p>
        </div>
        
        <h5>Test Results:</h5>
        <?php foreach ($tests as $test_name => $result): ?>
            <div class="test-result test-<?php echo strtolower($result['status']); ?>">
                <strong><?php echo $test_name; ?>:</strong> 
                <?php echo $result['status']; ?> - <?php echo $result['message']; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="mt-4 text-center">
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Go to Login
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Go to Home
            </a>
        </div>
        
        <div class="mt-3">
            <small class="text-muted">
                <strong>Note:</strong> This test script should be deleted after confirming the system works.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 