<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Header Component
 * Common header for all windows with logo and title
 */

require_once '../config/config.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header with Logo and Title (as per Style Guide) -->
    <header class="app-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img src="../assets/Logo_Worldskills_Ghana.png" alt="HTU Logo" class="logo">
                </div>
                <div class="col-md-8 text-center">
                    <h1 class="app-title"><?php echo APP_NAME; ?></h1>
                </div>
                <div class="col-md-2 text-end">
                    <?php if (isLoggedIn()): ?>
                        <span class="user-info">
                            Welcome, <?php echo sanitizeInput($_SESSION['username'] ?? 'User'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="main-content">
        <div class="container-fluid"> 