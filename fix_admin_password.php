<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Admin Password Fix
 * This script fixes the admin password to work with the login system
 */

require_once 'includes/config.php';

$success = false;
$message = '';

try {
    $db = Database::getInstance();
    
    // Generate correct password hash for 'admin123'
    $correct_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Update the admin user with the correct password hash
    $update_sql = "UPDATE users SET password_hash = ? WHERE username = 'admin'";
    $stmt = $db->query($update_sql, [$correct_password_hash]);
    
    // Check if the update was successful
    $check_sql = "SELECT user_id FROM users WHERE username = 'admin'";
    $check_stmt = $db->query($check_sql);
    $admin_user = $check_stmt->fetch();
    
    if ($admin_user) {
        $success = true;
        $message = "Admin password has been successfully updated!<br><br>";
        $message .= "<strong>Login Credentials:</strong><br>";
        $message .= "Username: admin<br>";
        $message .= "Password: admin123<br><br>";
        $message .= "You can now login to the system.";
    } else {
        $message = "Error: Admin user not found in database.";
    }
    
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin Password - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, <?php echo COLOR_ORANGE_BROWN; ?> 0%, <?php echo COLOR_BLUE; ?> 100%);
            min-height: 100vh;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .fix-container {
            background-color: <?php echo COLOR_WHITE; ?>;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 600px;
            width: 90%;
        }
        
        .success-icon {
            font-size: 4rem;
            color: <?php echo COLOR_BLUE; ?>;
            margin-bottom: 20px;
        }
        
        .error-icon {
            font-size: 4rem;
            color: <?php echo COLOR_ORANGE_BROWN; ?>;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background-color: <?php echo COLOR_BLUE; ?>;
            border-color: <?php echo COLOR_BLUE; ?>;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #040470;
            border-color: #040470;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="fix-container">
        <?php if ($success): ?>
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="text-success mb-4">Password Fixed Successfully!</h2>
        <?php else: ?>
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="text-danger mb-4">Error Occurred</h2>
        <?php endif; ?>
        
        <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
            <?php echo $message; ?>
        </div>
        
        <div class="mt-4">
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Go to Login
            </a>
        </div>
        
        <div class="mt-3">
            <small class="text-muted">
                <strong>Note:</strong> This script should be deleted after use for security purposes.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 