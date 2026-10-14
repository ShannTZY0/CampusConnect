<?php
/**
 * config/session.php
 * Secure session bootstrap. Included at the very top of every entry file
 * BEFORE any HTML output.
 */

if (session_status() === PHP_SESSION_NONE) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name('campusconnect_sid');
    session_start();
}

// Simple idle timeout (30 minutes)
$timeoutSeconds = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeoutSeconds) {
    $_SESSION = [];
    session_unset();
    session_destroy();
}
$_SESSION['last_activity'] = time();

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
