<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/register.php');
}

if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Invalid form submission. Please try again.');
    redirect('/register.php');
}

$idNumber = trim($_POST['id_number'] ?? '');
$program  = trim($_POST['program'] ?? '');
$first    = trim($_POST['first_name'] ?? '');
$last     = trim($_POST['last_name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

$errors = [];
if ($idNumber === '' || $program === '' || $first === '' || $last === '' || $email === '') {
    $errors[] = 'Please fill out all required fields.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long.';
}
if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

if ($errors) {
    setFlash('error', implode(' ', $errors));
    redirect('/register.php');
}

$db = getDB();

$check = $db->prepare('SELECT user_id FROM users WHERE email = ? OR id_number = ? LIMIT 1');
$check->execute([$email, $idNumber]);
if ($check->fetch()) {
    setFlash('error', 'An account with that email or ID number already exists.');
    redirect('/register.php');
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare(
    'INSERT INTO users (role_id, id_number, first_name, last_name, email, password_hash, program, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, "active")'
);
$stmt->execute([ROLE_STUDENT, $idNumber, $first, $last, $email, $hash, $program]);

logActivity((int) $db->lastInsertId(), 'register', 'New student account created');

setFlash('success', 'Account created successfully. You may now log in.');
redirect('/login.php');
