<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/login.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Invalid form submission. Please try again.');
    redirect('/login.php');
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    setFlash('error', 'Please enter both email and password.');
    redirect('/login.php');
}

$stmt = getDB()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    setFlash('error', 'Invalid email or password.');
    redirect('/login.php');
}

if ($user['status'] !== 'active') {
    setFlash('error', 'Your account is not active. Please contact the administrator.');
    redirect('/login.php');
}

// Regenerate session id on privilege change
session_regenerate_id(true);

$_SESSION['user_id']  = (int) $user['user_id'];
$_SESSION['role_id']  = (int) $user['role_id'];
$_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];

logActivity((int) $user['user_id'], 'login', 'User logged in');

setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');
redirect('/dashboard.php');
