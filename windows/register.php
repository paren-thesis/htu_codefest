<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Registration Window
 * Account creation for new users
 * 
 * Features:
 * - New user registration
 * - Role selection
 * - Encrypted password storage
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

$error_message = '';
$success_message = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    handleRegistration();
}

function handleRegistration() {
    global $error_message, $success_message;
    
    $username = sanitizeInput($_POST['new_username'] ?? '');
    $email = sanitizeInput($_POST['new_email'] ?? '');
    $password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitizeInput($_POST['role'] ?? 'student');
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields.';
        return;
    }
    
    if ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
        return;
    }
    
    if (!isValidEmail($email)) {
        $error_message = 'Please enter a valid email address.';
        return;
    }
    
    if (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error_message = 'Username already exists.';
            return;
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_message = 'Email already exists.';
            return;
        }
        
        // Get role ID
        $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE role_name = ?");
        $stmt->execute([$role]);
        $roleData = $stmt->fetch();
        
        if (!$roleData) {
            $error_message = 'Invalid role selected.';
            return;
        }
        
        // Create new user
        $password_hash = hashPassword($password);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, role_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password_hash, $email, $roleData['role_id']]);
        
        $success_message = 'Account created successfully! You can now login.';
        
    } catch (PDOException $e) {
        $error_message = 'Registration failed. Please try again.';
    }
}

// Get available roles for registration
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT role_name, description FROM roles ORDER BY role_name");
    $stmt->execute();
    $roles = $stmt->fetchAll();
} catch (PDOException $e) {
    $roles = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Register</title>
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
                    
                    <!-- Registration Form -->
                    <div class="form-container">
                        <h2 class="form-title">Create New Account</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="register">
                            <div class="mb-3">
                                <label for="new_username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="new_username" name="new_username" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="new_email" name="new_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo $role['role_name']; ?>">
                                            <?php echo ucfirst($role['role_name']); ?> - <?php echo $role['description']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">Create Account</button>
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