<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$evaluations = $db->query("SELECT e.*, p.title AS project_title, g.group_name, u.first_name, u.last_name
    FROM evaluations e
    JOIN projects p ON p.project_id = e.project_id
    JOIN `groups` g ON g.group_id = p.group_id
    JOIN users u ON u.user_id = e.evaluator_id
    ORDER BY e.evaluated_at DESC")->fetchAll();

$pageTitle = 'Evaluations';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Evaluations</h1><p>All evaluation records program-wide.</p></div></div>
<div class="card">
    <table>
        <thead><tr><th>Group</th><th>Project</th><th>Evaluator</th><th>Type</th><th>Score</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($evaluations as $ev): ?>
        <tr>
            <td><?= e($ev['group_name']) ?></td>
            <td><?= e($ev['project_title']) ?></td>
            <td><?= e($ev['first_name'] . ' ' . $ev['last_name']) ?></td>
            <td><?= e(ucfirst($ev['evaluation_type'])) ?></td>
            <td><?= $ev['score'] !== null ? e($ev['score']) : '—' ?></td>
            <td><?= e(formatDate($ev['evaluated_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$evaluations): ?><tr><td colspan="6" class="empty-state">No evaluations recorded.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
