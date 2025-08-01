<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Common Functions
 * Utility functions used throughout the application
 */

require_once '../config/config.php';
require_once '../config/database.php';

/**
 * Sanitize user input to prevent XSS
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a secure password hash
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate a unique receipt number
 */
function generateReceiptNumber() {
    return 'CSD' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Redirect to another page
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Display error message
 */
function showError($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

/**
 * Display success message
 */
function showSuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return 'GH₵ ' . number_format($amount, 2);
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Get current academic year
 */
function getCurrentAcademicYear() {
    $currentYear = date('Y');
    $nextYear = $currentYear + 1;
    return "$currentYear-$nextYear";
}
?> 