<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Application Configuration
 * Main configuration settings
 */

// Application settings
define('APP_NAME', 'HTU Departmental Dues Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/htu_codefest');

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SECURE_COOKIES', false); // Set to true in production

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['csv']);

// Style Guide Colors (as per Project.txt)
define('COLOR_ORANGE_BROWN', '#FF8B00'); // RGB(255, 139, 0)
define('COLOR_BLUE', '#050589'); // RGB(5, 5, 137)
define('COLOR_YELLOW', '#F5D200'); // RGB(245, 210, 0)
define('COLOR_WHITE', '#FFFFFF');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 