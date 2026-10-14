<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_ADMIN;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

$stats = [
    'total_users' => (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'students'    => (int) $db->query('SELECT COUNT(*) FROM users WHERE role_id = ' . ROLE_STUDENT)->fetchColumn(),
    'advisers'    => (int) $db->query('SELECT COUNT(*) FROM users WHERE role_id = ' . ROLE_ADVISER)->fetchColumn(),
    'coordinators'=> (int) $db->query('SELECT COUNT(*) FROM users WHERE role_id = ' . ROLE_COORDINATOR)->fetchColumn(),
    'groups'      => (int) $db->query('SELECT COUNT(*) FROM `groups`')->fetchColumn(),
];

$recentLogs = $db->query("SELECT l.*, u.first_name, u.last_name FROM activity_logs l
    LEFT JOIN users u ON u.user_id = l.user_id ORDER BY l.created_at DESC LIMIT 10")->fetchAll();

$pageTitle = 'Admin Dashboard';
$extraCss = ['dashboard'];
$extraJs = ['dashboard'];
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>System Administration</h1><p>Manage users, roles, and system-wide settings.</p></div></div>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-value"><?= $stats['total_users'] ?></div><div class="stat-label">Total Users</div></div>
    <div class="stat-card"><div class="stat-value"><?= $stats['students'] ?></div><div class="stat-label">Students</div></div>
    <div class="stat-card"><div class="stat-value"><?= $stats['advisers'] ?></div><div class="stat-label">Advisers</div></div>
    <div class="stat-card"><div class="stat-value"><?= $stats['coordinators'] ?></div><div class="stat-label">Coordinators</div></div>
    <div class="stat-card"><div class="stat-value"><?= $stats['groups'] ?></div><div class="stat-label">Groups</div></div>
</div>

<div class="dash-grid">
    <div>
        <div class="card">
            <div class="card-header"><h3>Recent Activity</h3></div>
            <?php if ($recentLogs): ?>
            <ul class="timeline">
                <?php foreach ($recentLogs as $l): ?>
                <li>
                    <span><?= $l['first_name'] ? e($l['first_name'] . ' ' . $l['last_name']) : 'System' ?> — <?= e($l['action']) ?><?php if ($l['details']): ?><br><span class="stat-label"><?= e($l['details']) ?></span><?php endif; ?></span>
                    <span class="time"><?= e(formatDate($l['created_at'])) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?><p class="stat-label">No activity recorded yet.</p><?php endif; ?>
            <div style="margin-top:.75rem"><a href="<?= BASE_URL ?>/admin/activity_logs.php" class="btn btn-sm btn-outline">View All Logs</a></div>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card-header"><h3>Quick Links</h3></div>
            <div class="quick-links">
                <a href="<?= BASE_URL ?>/admin/users.php">👤 Manage Users</a>
                <a href="<?= BASE_URL ?>/admin/roles.php">🛡️ Roles</a>
                <a href="<?= BASE_URL ?>/admin/system_settings.php">⚙️ Settings</a>
                <a href="<?= BASE_URL ?>/admin/backups.php">💾 Backups</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
