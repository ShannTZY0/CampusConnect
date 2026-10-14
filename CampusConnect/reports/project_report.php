<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = [ROLE_COORDINATOR, ROLE_ADMIN];
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

$byStatus = $db->query("SELECT status, COUNT(*) AS total FROM projects GROUP BY status")->fetchAll();
$projects = $db->query("SELECT p.*, g.group_name, u.first_name AS adviser_first, u.last_name AS adviser_last
    FROM projects p JOIN `groups` g ON g.group_id = p.group_id
    LEFT JOIN users u ON u.user_id = g.adviser_id
    ORDER BY p.status, p.title")->fetchAll();

$pageTitle = 'Project Report';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Project Report</h1><p>Status breakdown of all capstone projects.</p></div></div>

<div class="stat-grid">
    <?php foreach ($byStatus as $s): ?>
    <div class="stat-card"><div class="stat-value"><?= (int)$s['total'] ?></div><div class="stat-label"><?= e(ucwords(str_replace('_',' ',$s['status']))) ?></div></div>
    <?php endforeach; ?>
</div>

<div class="card">
    <table>
        <thead><tr><th>Title</th><th>Group</th><th>Adviser</th><th>Status</th><th>Progress</th></tr></thead>
        <tbody>
        <?php foreach ($projects as $p): ?>
        <tr>
            <td><?= e($p['title']) ?></td>
            <td><?= e($p['group_name']) ?></td>
            <td><?= $p['adviser_first'] ? e($p['adviser_first'].' '.$p['adviser_last']) : '—' ?></td>
            <td><?= statusBadge($p['status']) ?></td>
            <td><?= (int)$p['progress_percent'] ?>%</td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$projects): ?><tr><td colspan="5" class="empty-state">No project data available.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<button onclick="window.print()" class="btn btn-outline">🖨️ Print Report</button>
<?php include __DIR__ . '/../includes/footer.php'; ?>
