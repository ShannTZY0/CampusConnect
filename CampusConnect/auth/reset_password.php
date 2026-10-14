<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission. Please try again.');
        redirect('/login.php');
    }

    $token    = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8 || $password !== $confirm) {
        setFlash('error', 'Passwords must match and be at least 8 characters.');
        redirect('/auth/reset_password.php?token=' . urlencode($token));
    }

    $stmt = $db->prepare('SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        setFlash('error', 'This reset link is invalid or has expired.');
        redirect('/auth/forgot_password.php');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $upd = $db->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE user_id = ?');
    $upd->execute([$hash, $user['user_id']]);

    setFlash('success', 'Your password has been reset. You may now log in.');
    redirect('/login.php');
}

$token = $_GET['token'] ?? '';
$stmt = $db->prepare('SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1');
$stmt->execute([$token]);
if (!$stmt->fetch()) {
    setFlash('error', 'This reset link is invalid or has expired.');
    redirect('/auth/forgot_password.php');
}

$pageTitle = 'Reset Password';
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card">
        <h1><?= e(APP_NAME) ?></h1>
        <p class="auth-subtitle">Set a new password</p>
        <form method="POST" novalidate>
            <?= csrfField() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" minlength="8" required autofocus>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
