<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Login Page
 * Departmental Dues Management System
 */

require_once 'includes/config.php';

// Auto-fix admin password on first run
$password_fixed = autoFixAdminPassword();

$error_message = '';
$success_message = '';

// Show success message if password was fixed
if ($password_fixed) {
    $success_message = 'System initialized successfully! You can now login with admin/admin123.';
}

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        try {
            $db = Database::getInstance();
            
            // Get user data
            $sql = "SELECT u.*, r.role_name FROM users u 
                    JOIN roles r ON u.role_id = r.role_id 
                    WHERE u.username = ? AND u.is_active = 1";
            $stmt = $db->query($sql, [$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role_name'];
                
                // Update last login
                $update_sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?";
                $db->query($update_sql, [$user['user_id']]);
                
                // Redirect to appropriate dashboard
                header('Location: index.php');
                exit();
            } else {
                $error_message = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error_message = 'Login failed. Please try again.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}

// Handle registration form submission
if (isset($_POST['register'])) {
    $reg_username = sanitize_input($_POST['reg_username'] ?? '');
    $reg_email = sanitize_input($_POST['reg_email'] ?? '');
    $reg_password = $_POST['reg_password'] ?? '';
    $reg_confirm_password = $_POST['reg_confirm_password'] ?? '';
    
    if (empty($reg_username) || empty($reg_email) || empty($reg_password)) {
        $error_message = 'Please fill in all registration fields.';
    } elseif ($reg_password !== $reg_confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($reg_password) < PASSWORD_MIN_LENGTH) {
        $error_message = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
    } elseif (!validate_email($reg_email)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        try {
            $db = Database::getInstance();
            
            // Check if username or email already exists
            $check_sql = "SELECT user_id FROM users WHERE username = ? OR email = ?";
            $check_stmt = $db->query($check_sql, [$reg_username, $reg_email]);
            $existing = $check_stmt->fetch();
            
            if ($existing) {
                $error_message = 'Username or email already exists.';
            } else {
                // Create new user (default role: student)
                $password_hash = password_hash($reg_password, PASSWORD_DEFAULT);
                $insert_sql = "INSERT INTO users (username, password_hash, email, role_id) VALUES (?, ?, ?, 4)";
                $db->query($insert_sql, [$reg_username, $password_hash, $reg_email]);
                
                $success_message = 'Registration successful! You can now login.';
            }
        } catch (Exception $e) {
            $error_message = 'Registration failed. Please try again.';
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, <?php echo COLOR_ORANGE_BROWN; ?> 0%, <?php echo COLOR_BLUE; ?> 100%);
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }
        
        .login-container {
            background-color: <?php echo COLOR_WHITE; ?>;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header-section {
            background-color: <?php echo COLOR_ORANGE_BROWN; ?>;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header-section h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .header-section p {
            font-size: 16px;
            margin-bottom: 0;
        }
        
        .form-section {
            padding: 40px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: <?php echo COLOR_BLUE; ?>;
            box-shadow: 0 0 0 0.2rem rgba(5, 5, 137, 0.25);
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
        
        .btn-secondary {
            background-color: <?php echo COLOR_YELLOW; ?>;
            border-color: <?php echo COLOR_YELLOW; ?>;
            color: #000;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background-color: #e6c200;
            border-color: #e6c200;
            color: #000;
            transform: translateY(-2px);
        }
        
        .nav-tabs {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 30px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: bold;
            padding: 15px 25px;
            border-radius: 0;
        }
        
        .nav-tabs .nav-link.active {
            color: <?php echo COLOR_BLUE; ?>;
            background-color: transparent;
            border-bottom: 3px solid <?php echo COLOR_BLUE; ?>;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
        }
        
        .logo {
            max-height: 80px;
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
        }
        
        .input-group .form-control {
            border-left: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="login-container">
                    <!-- Header Section -->
                    <div class="header-section">
                        <img src="assets/Logo_Worldskills_Ghana.png" alt="Logo" class="logo">
                        <h1><?php echo APP_NAME; ?></h1>
                        <p>Departmental Dues Management System</p>
                    </div>
                    
                    <!-- Form Section -->
                    <div class="form-section">
                        <!-- Navigation Tabs -->
                        <ul class="nav nav-tabs" id="authTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                                    <i class="fas fa-user-plus"></i> Register
                                </button>
                            </li>
                        </ul>
                        
                        <!-- Tab Content -->
                        <div class="tab-content" id="authTabsContent">
                            <!-- Error/Success Messages -->
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success_message): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Login Tab -->
                            <div class="tab-pane fade show active" id="login" role="tabpanel">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-sign-in-alt"></i> Login
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="text-center mt-4">
                                    <small class="text-muted">
                                        <strong>Demo Credentials:</strong><br>
                                        Username: admin | Password: admin123
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Register Tab -->
                            <div class="tab-pane fade" id="register" role="tabpanel">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="reg_username" class="form-label">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" class="form-control" id="reg_username" name="reg_username" 
                                                   value="<?php echo htmlspecialchars($_POST['reg_username'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="reg_email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" class="form-control" id="reg_email" name="reg_email" 
                                                   value="<?php echo htmlspecialchars($_POST['reg_email'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="reg_password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" id="reg_password" name="reg_password" 
                                                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>
                                        </div>
                                        <small class="text-muted">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="reg_confirm_password" class="form-label">Confirm Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" id="reg_confirm_password" name="reg_confirm_password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" name="register" class="btn btn-secondary">
                                            <i class="fas fa-user-plus"></i> Register
                                        </button>
                                    </div>
                                </form>
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