<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect('/dashboard.php');
}

$pageTitle = 'Register';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrapper">
    <div class="auth-card auth-card-wide">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="<?= e(APP_NAME) ?>" class="auth-logo">
        <h1><?= e(APP_NAME) ?></h1>
        <p class="auth-subtitle">Create a student account</p>

        <form action="<?= BASE_URL ?>/auth/register_process.php" method="POST" novalidate>
            <?= csrfField() ?>
            <div class="form-grid">
                <div class="form-group">
                    <label for="id_number">Student ID Number</label>
                    <input type="text" id="id_number" name="id_number" required>
                </div>
                <div class="form-group">
                    <label for="program">Program</label>
                    <input type="text" id="program" name="program" placeholder="e.g. BS Computer Science" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group form-span-2">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" minlength="8" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <p class="auth-footer-link">Already have an account? <a href="<?= BASE_URL ?>/login.php">Login here</a></p>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
