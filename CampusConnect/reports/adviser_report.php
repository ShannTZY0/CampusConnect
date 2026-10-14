<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = [ROLE_COORDINATOR, ROLE_ADMIN];
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

$advisers = $db->query("SELECT u.user_id, u.first_name, u.last_name, u.department,
    (SELECT COUNT(*) FROM `groups` g WHERE g.adviser_id = u.user_id) AS advisee_groups,
    (SELECT COUNT(*) FROM consultations c WHERE c.adviser_id = u.user_id) AS total_consultations,
    (SELECT COUNT(*) FROM consultations c WHERE c.adviser_id = u.user_id AND c.status = 'completed') AS completed_consultations,
    (SELECT AVG(e.score) FROM evaluations e WHERE e.evaluator_id = u.user_id AND e.score IS NOT NULL) AS avg_score_given
    FROM users u WHERE u.role_id = " . ROLE_ADVISER . " ORDER BY u.last_name")->fetchAll();

$pageTitle = 'Adviser Report';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Adviser Report</h1><p>Workload and activity summary per adviser.</p></div></div>

<div class="card">
    <table>
        <thead><tr><th>Adviser</th><th>Department</th><th>Advisee Groups</th><th>Consultations</th><th>Completed</th><th>Avg Score Given</th></tr></thead>
        <tbody>
        <?php foreach ($advisers as $a): ?>
        <tr>
            <td><?= e($a['first_name'] . ' ' . $a['last_name']) ?></td>
            <td><?= e($a['department']) ?></td>
            <td><?= (int)$a['advisee_groups'] ?></td>
            <td><?= (int)$a['total_consultations'] ?></td>
            <td><?= (int)$a['completed_consultations'] ?></td>
            <td><?= $a['avg_score_given'] !== null ? number_format((float)$a['avg_score_given'], 2) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$advisers): ?><tr><td colspan="6" class="empty-state">No advisers found.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<button onclick="window.print()" class="btn btn-outline">🖨️ Print Report</button>
<?php include __DIR__ . '/../includes/footer.php'; ?>
