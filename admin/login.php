<?php
/**
 * Admin Login Page (Alternative)
 * Can also use main login.php with role selection
 */

require_once '../config.php';
startSession();

// If already logged in as admin, redirect
if (isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

// Redirect to main login page
header('Location: ../login.php');
exit();

