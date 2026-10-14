<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = [ROLE_COORDINATOR, ROLE_ADMIN];
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

$byStatus = $db->query("SELECT status, COUNT(*) AS total FROM consultations GROUP BY status")->fetchAll();
$byGroup = $db->query("SELECT g.group_name, COUNT(*) AS total,
    SUM(c.status = 'completed') AS completed
    FROM consultations c JOIN `groups` g ON g.group_id = c.group_id
    GROUP BY g.group_name ORDER BY total DESC")->fetchAll();

$pageTitle = 'Consultation Report';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Consultation Report</h1><p>Consultation frequency and completion rates.</p></div></div>

<div class="stat-grid">
    <?php foreach ($byStatus as $s): ?>
    <div class="stat-card"><div class="stat-value"><?= (int)$s['total'] ?></div><div class="stat-label"><?= e(ucfirst($s['status'])) ?></div></div>
    <?php endforeach; ?>
</div>

<div class="card">
    <table>
        <thead><tr><th>Group</th><th>Total Requests</th><th>Completed</th></tr></thead>
        <tbody>
        <?php foreach ($byGroup as $g): ?>
        <tr><td><?= e($g['group_name']) ?></td><td><?= (int)$g['total'] ?></td><td><?= (int)$g['completed'] ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$byGroup): ?><tr><td colspan="3" class="empty-state">No consultation data available.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<button onclick="window.print()" class="btn btn-outline">🖨️ Print Report</button>
<?php include __DIR__ . '/../includes/footer.php'; ?>
