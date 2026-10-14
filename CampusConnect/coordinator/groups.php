<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$groups = $db->query("SELECT g.*, u.first_name AS adviser_first, u.last_name AS adviser_last,
    (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.group_id) AS member_count
    FROM `groups` g LEFT JOIN users u ON u.user_id = g.adviser_id
    ORDER BY g.created_at DESC")->fetchAll();

$pageTitle = 'Groups';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Groups</h1><p>All capstone groups.</p></div></div>
<div class="card">
    <table>
        <thead><tr><th>Group</th><th>Code</th><th>Members</th><th>Adviser</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($groups as $g): ?>
        <tr>
            <td><?= e($g['group_name']) ?></td>
            <td><?= e($g['group_code']) ?></td>
            <td><?= (int)$g['member_count'] ?></td>
            <td><?= $g['adviser_id'] ? e($g['adviser_first'] . ' ' . $g['adviser_last']) : '<span class="stat-label">Unassigned</span>' ?></td>
            <td><?= statusBadge($g['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$groups): ?><tr><td colspan="5" class="empty-state">No groups yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
