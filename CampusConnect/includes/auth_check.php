<?php
/**
 * includes/auth_check.php
 * Include at the top of any protected page (after config bootstrap).
 * Optionally set $requiredRole (int or array) before including this file
 * to restrict access to specific roles.
 */

requireLogin();

if (isset($requiredRole)) {
    requireRole($requiredRole);
}
