<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Database Configuration
 * Departmental Dues Management System
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'htu_codefest_25');
define('DB_USER', 'root');  // Default XAMPP MySQL user
define('DB_PASS', '');      // Default XAMPP MySQL password (empty)
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'HTU COMPSSA CODEFEST 2025');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/htu_codefest_25/');

// Session configuration
define('SESSION_NAME', 'HTU_CODEFEST_SESSION');
define('SESSION_LIFETIME', 3600); // 1 hour

// File upload configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['csv', 'txt']);
define('UPLOAD_DIR', 'uploads/');

// Security configuration
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Style guide colors (from project requirements)
define('COLOR_ORANGE_BROWN', '#FF8B00'); // RGB(255, 139, 0)
define('COLOR_BLUE', '#050589');         // RGB(5, 5, 137)
define('COLOR_YELLOW', '#F5D200');       // RGB(245, 210, 0)
define('COLOR_WHITE', '#FFFFFF');        // RGB(255, 255, 255)

// Error reporting (set to 0 for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

/**
 * Database connection class
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw new Exception("Database operation failed");
        }
    }
}

/**
 * Utility functions
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generate_receipt_number() {
    return 'CSD' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

function format_currency($amount) {
    return 'GH₵ ' . number_format($amount, 2);
}

function format_date($date) {
    return date('d.m.Y', strtotime($date));
}

/**
 * Authentication helper functions
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_current_user_data() {
    if (!is_logged_in()) {
        return null;
    }
    
    $db = Database::getInstance();
    $sql = "SELECT u.*, r.role_name FROM users u 
            JOIN roles r ON u.role_id = r.role_id 
            WHERE u.user_id = ? AND u.is_active = 1";
    $stmt = $db->query($sql, [$_SESSION['user_id']]);
    return $stmt->fetch();
}

function has_permission($required_role) {
    $user = get_current_user_data();
    if (!$user) {
        return false;
    }
    
    $role_hierarchy = [
        'administrator' => 5,
        'supervisor' => 4,
        'cashier' => 3,
        'lecturer' => 2,
        'student' => 1
    ];
    
    $user_level = $role_hierarchy[$user['role_name']] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function require_permission($role) {
    require_login();
    if (!has_permission($role)) {
        header('Location: error.php?message=access_denied');
        exit();
    }
}

/**
 * Automatically fix admin password if needed
 * This function checks if the admin user has the old password hash and fixes it
 * Returns true if password was fixed, false otherwise
 */
function autoFixAdminPassword() {
    try {
        $db = Database::getInstance();
        
        // Check if admin user exists and has the old password hash
        $check_sql = "SELECT user_id, password_hash FROM users WHERE username = 'admin'";
        $check_stmt = $db->query($check_sql);
        $admin_user = $check_stmt->fetch();
        
        if ($admin_user) {
            // Check if the password hash is the old one (from database_setup.sql)
            $old_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
            
            if ($admin_user['password_hash'] === $old_hash) {
                // Generate correct password hash for 'admin123'
                $correct_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
                
                // Update the admin user with the correct password hash
                $update_sql = "UPDATE users SET password_hash = ? WHERE username = 'admin'";
                $db->query($update_sql, [$correct_password_hash]);
                
                // Log the fix (optional)
                error_log("HTU Codefest: Admin password automatically fixed");
                
                return true; // Password was fixed
            }
        }
        
        return false; // No fix was needed
    } catch (Exception $e) {
        // Silently handle any database errors
        error_log("HTU Codefest: Auto-fix admin password failed: " . $e->getMessage());
        return false;
    }
}
?> 