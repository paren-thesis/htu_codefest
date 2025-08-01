<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Password Change Window
 * Password change functionality for existing users
 * 
 * Features:
 * - Current password verification
 * - New password with confirmation
 * - Encrypted password storage
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

$error_message = '';
$success_message = '';

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    handlePasswordChange();
}

function handlePasswordChange() {
    global $error_message, $success_message;
    
    $username = sanitizeInput($_POST['change_username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password_change'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';
    
    if (empty($username) || empty($current_password) || empty($new_password)) {
        $error_message = 'Please fill in all fields.';
        return;
    }
    
    if ($new_password !== $confirm_new_password) {
        $error_message = 'New passwords do not match.';
        return;
    }
    
    if (strlen($new_password) < 6) {
        $error_message = 'New password must be at least 6 characters long.';
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Verify current password
        $stmt = $pdo->prepare("SELECT user_id, password_hash FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user || !verifyPassword($current_password, $user['password_hash'])) {
            $error_message = 'Invalid username or current password.';
            return;
        }
        
        // Update password
        $new_password_hash = hashPassword($new_password);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$new_password_hash, $user['user_id']]);
        
        $success_message = 'Password changed successfully!';
        
    } catch (PDOException $e) {
        $error_message = 'Password change failed. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Change Password</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <div class="col-md-2"></div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    
                    <!-- Display Messages -->
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <!-- Password Change Form -->
                    <div class="form-container">
                        <h2 class="form-title">Change Password</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label for="change_username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="change_username" name="change_username" required>
                            </div>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password_change" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password_change" name="new_password_change" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </div>
                        </form>
                        
                        <!-- Navigation Button -->
                        <div class="d-grid">
                            <a href="login.php" class="btn btn-secondary">Back to Login</a>
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