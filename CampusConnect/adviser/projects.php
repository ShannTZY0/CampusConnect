<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_ADVISER;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$user = currentUser();

$stmt = $db->prepare("SELECT pr.*, g.group_name, g.group_id FROM projects pr
    JOIN `groups` g ON g.group_id = pr.group_id
    WHERE g.adviser_id = ? ORDER BY pr.updated_at DESC");
$stmt->execute([$user['user_id']]);
$projects = $stmt->fetchAll();

$pageTitle = 'Advisee Projects';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Advisee Projects</h1><p>Projects under your supervision.</p></div></div>
<?php if (!$projects): ?>
<div class="card empty-state"><p>No advisee projects yet.</p></div>
<?php else: ?>
<div class="card">
    <table>
        <thead><tr><th>Group</th><th>Project</th><th>Status</th><th>Progress</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($projects as $p): ?>
            <tr>
                <td><?= e($p['group_name']) ?></td>
                <td><?= e($p['title']) ?></td>
                <td><?= statusBadge($p['status']) ?></td>
                <td><?= (int)$p['progress_percent'] ?>%</td>
                <td>
                    <a href="<?= BASE_URL ?>/adviser/progress.php?project_id=<?= (int)$p['project_id'] ?>">Track Progress</a> ·
                    <a href="<?= BASE_URL ?>/adviser/feedback.php?project_id=<?= (int)$p['project_id'] ?>">Give Feedback</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
