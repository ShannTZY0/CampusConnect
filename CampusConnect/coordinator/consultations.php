<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$consultations = $db->query("SELECT c.*, g.group_name, u.first_name AS adviser_first, u.last_name AS adviser_last
    FROM consultations c
    JOIN `groups` g ON g.group_id = c.group_id
    JOIN users u ON u.user_id = c.adviser_id
    ORDER BY c.created_at DESC LIMIT 100")->fetchAll();

$pageTitle = 'Consultations';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Consultations</h1><p>Program-wide consultation activity.</p></div></div>
<div class="card">
    <table>
        <thead><tr><th>Group</th><th>Adviser</th><th>Preferred</th><th>Scheduled</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($consultations as $c): ?>
        <tr>
            <td><?= e($c['group_name']) ?></td>
            <td><?= e($c['adviser_first'] . ' ' . $c['adviser_last']) ?></td>
            <td><?= e(formatDate($c['preferred_date'])) ?></td>
            <td><?= e(formatDate($c['scheduled_date'])) ?></td>
            <td><?= statusBadge($c['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$consultations): ?><tr><td colspan="5" class="empty-state">No consultations yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
