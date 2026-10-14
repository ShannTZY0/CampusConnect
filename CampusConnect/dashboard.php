<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

switch (currentRoleId()) {
    case ROLE_STUDENT:
        redirect('/student/dashboard.php');
        break;
    case ROLE_ADVISER:
        redirect('/adviser/dashboard.php');
        break;
    case ROLE_COORDINATOR:
        redirect('/coordinator/dashboard.php');
        break;
    case ROLE_ADMIN:
        redirect('/admin/dashboard.php');
        break;
    default:
        redirect('/login.php');
}
