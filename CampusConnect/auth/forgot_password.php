<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission. Please try again.');
        redirect('/auth/forgot_password.php');
    }

    $email = trim($_POST['email'] ?? '');
    $stmt = getDB()->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Always show the same message whether or not the email exists,
    // to avoid leaking which addresses are registered.
    if ($user) {
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $upd = getDB()->prepare('UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?');
        $upd->execute([$token, $expires, $user['user_id']]);

        // In production this link would be emailed to the user.
        $resetLink = BASE_URL . '/auth/reset_password.php?token=' . $token;
        error_log('Password reset link for ' . $email . ': ' . $resetLink);
    }

    setFlash('success', 'If that email is registered, a password reset link has been sent.');
    redirect('/login.php');
}

$pageTitle = 'Forgot Password';
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card">
        <h1><?= e(APP_NAME) ?></h1>
        <p class="auth-subtitle">Reset your password</p>
        <form method="POST" novalidate>
            <?= csrfField() ?>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>
        <p class="auth-footer-link"><a href="<?= BASE_URL ?>/login.php">Back to login</a></p>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
