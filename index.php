<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Departmental Dues Management System
 * Main Entry Point
 * 
 * This is the main entry point for the application.
 * It redirects to the login window as specified in the requirements.
 */

// Start session for user management
session_start();

// Redirect to login window (first window shown on startup)
header("Location: windows/login.php");
exit();
?> 