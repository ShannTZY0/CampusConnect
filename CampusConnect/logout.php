<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    logActivity((int) $_SESSION['user_id'], 'logout', 'User logged out');
}

$_SESSION = [];
session_unset();
session_destroy();

// Start a fresh session just to hold the flash message on the login page
session_start();
setFlash('success', 'You have been logged out successfully.');

header('Location: ' . BASE_URL . '/login.php');
exit;
