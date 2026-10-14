<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect('/dashboard.php');
}

$pageTitle = 'Login';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="<?= e(APP_NAME) ?>" class="auth-logo">
        <h1><?= e(APP_NAME) ?></h1>
        <p class="auth-subtitle">Sign in to your account</p>

        <form action="<?= BASE_URL ?>/auth/login_process.php" method="POST" novalidate>
            <?= csrfField() ?>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group form-row-between">
                <label class="checkbox-label"><input type="checkbox" name="remember"> Remember me</label>
                <a href="<?= BASE_URL ?>/auth/forgot_password.php">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <p class="auth-footer-link">Don't have an account? <a href="<?= BASE_URL ?>/register.php">Register here</a></p>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
