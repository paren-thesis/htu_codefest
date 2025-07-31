<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Logout Page
 * Departmental Dues Management System
 */

require_once 'includes/config.php';

// Destroy session
session_destroy();

// Clear session cookies
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page
header('Location: login.php');
exit();
?> 