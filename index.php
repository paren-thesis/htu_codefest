<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Main Entry Point
 * Departmental Dues Management System
 */

require_once 'includes/config.php';

// Auto-fix admin password on first run
autoFixAdminPassword();

// Redirect to login if not authenticated
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Get current user data
$user = get_current_user_data();

// Redirect to appropriate dashboard based on role
switch ($user['role_name']) {
    case 'administrator':
        header('Location: pages/admin_dashboard.php');
        break;
    case 'supervisor':
        header('Location: pages/supervisor_dashboard.php');
        break;
    case 'cashier':
        header('Location: pages/cashier_dashboard.php');
        break;
    case 'lecturer':
        header('Location: pages/lecturer_dashboard.php');
        break;
    case 'student':
        header('Location: pages/student_dashboard.php');
        break;
    default:
        header('Location: pages/dashboard.php');
        break;
}
exit();
?> 