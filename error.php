<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Error Page
 * Departmental Dues Management System
 */

require_once 'includes/config.php';

$error_code = $_GET['code'] ?? '404';
$error_message = $_GET['message'] ?? 'Page not found';

// Define error messages
$error_messages = [
    '404' => 'Page Not Found',
    '403' => 'Access Denied',
    '500' => 'Internal Server Error',
    'access_denied' => 'Access Denied',
    'database_error' => 'Database Connection Error',
    'file_not_found' => 'File Not Found'
];

$error_title = $error_messages[$error_code] ?? 'Error';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - <?php echo APP_NAME; ?></title>
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
        
        .error-container {
            background-color: <?php echo COLOR_WHITE; ?>;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        
        .error-icon {
            font-size: 4rem;
            color: <?php echo COLOR_ORANGE_BROWN; ?>;
            margin-bottom: 20px;
        }
        
        .error-code {
            font-size: 3rem;
            font-weight: bold;
            color: <?php echo COLOR_BLUE; ?>;
            margin-bottom: 10px;
        }
        
        .error-title {
            font-size: 1.5rem;
            color: <?php echo COLOR_DARK_GRAY; ?>;
            margin-bottom: 15px;
        }
        
        .error-message {
            color: #6c757d;
            margin-bottom: 30px;
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
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <?php if ($error_code === '404'): ?>
                <i class="fas fa-search"></i>
            <?php elseif ($error_code === '403' || $error_code === 'access_denied'): ?>
                <i class="fas fa-ban"></i>
            <?php elseif ($error_code === '500' || $error_code === 'database_error'): ?>
                <i class="fas fa-exclamation-triangle"></i>
            <?php else: ?>
                <i class="fas fa-exclamation-circle"></i>
            <?php endif; ?>
        </div>
        
        <div class="error-code"><?php echo htmlspecialchars($error_code); ?></div>
        <div class="error-title"><?php echo htmlspecialchars($error_title); ?></div>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        
        <div class="d-grid gap-2">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Go to Home
            </a>
            <a href="login.php" class="btn btn-secondary">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                If you believe this is an error, please contact the system administrator.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 