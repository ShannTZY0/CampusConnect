<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_ADVISER;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$user = currentUser();
$db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$user['user_id']]);

$stmt = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$user['user_id']]);
$notifications = $stmt->fetchAll();

$pageTitle = 'Notifications';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Notifications</h1></div></div>
<?php if (!$notifications): ?>
<div class="card empty-state"><p>You're all caught up!</p></div>
<?php else: ?>
<div class="card">
    <ul class="timeline">
        <?php foreach ($notifications as $n): ?>
        <li>
            <span><strong><?= e($n['title']) ?></strong><br><?= e($n['message']) ?><?php if ($n['link']): ?> — <a href="<?= BASE_URL . e($n['link']) ?>">View</a><?php endif; ?></span>
            <span class="time"><?= e(formatDate($n['created_at'])) ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
