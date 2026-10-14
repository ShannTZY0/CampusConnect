<?php
/** includes/navbar.php */
$navUser = currentUser();
$unread  = $navUser ? unreadNotificationCount((int) $navUser['user_id']) : 0;
$roleName = currentRoleName();
?>
<header class="navbar">
    <div class="navbar-brand">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="<?= e(APP_NAME) ?>" class="navbar-logo">
        <span><?= e(APP_NAME) ?></span>
    </div>

    <button class="navbar-toggle" id="sidebarToggle" type="button" aria-label="Toggle menu">☰</button>

    <div class="navbar-right">
        <a class="navbar-icon" href="<?= BASE_URL ?>/<?= e($roleName) ?>/notifications<?= $roleName === 'student' ? '/index.php' : '.php' ?>" title="Notifications">
            🔔<?php if ($unread > 0): ?><span class="badge-dot"><?= $unread ?></span><?php endif; ?>
        </a>
        <div class="navbar-user">
            <img src="<?= BASE_URL ?>/assets/images/<?= e($navUser['profile_photo'] ?? 'default-profile.png') ?>" class="navbar-avatar" alt="Profile">
            <span><?= e(($navUser['first_name'] ?? '') . ' ' . ($navUser['last_name'] ?? '')) ?></span>
            <span class="role-tag"><?= e(ucfirst($roleName ?? '')) ?></span>
        </div>
        <a class="btn btn-sm btn-outline" href="<?= BASE_URL ?>/logout.php">Logout</a>
    </div>
</header>
