<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$groupId = currentStudentGroupId();
$evaluations = [];

if ($groupId) {
    $stmt = $db->prepare('SELECT e.*, u.first_name, u.last_name FROM evaluations e
        JOIN projects p ON p.project_id = e.project_id
        JOIN users u ON u.user_id = e.evaluator_id
        WHERE p.group_id = ? ORDER BY e.evaluated_at DESC');
    $stmt->execute([$groupId]);
    $evaluations = $stmt->fetchAll();
}

$pageTitle = 'Evaluations';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>Evaluations</h1><p>Scores and comments from your evaluators.</p></div></div>
<?php if (!$evaluations): ?>
<div class="card empty-state"><p>No evaluations yet.</p></div>
<?php else: ?>
<div class="card">
    <table>
        <thead><tr><th>Type</th><th>Evaluator</th><th>Score</th><th>Comments</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($evaluations as $ev): ?>
            <tr>
                <td><?= e(ucfirst($ev['evaluation_type'])) ?></td>
                <td><?= e($ev['first_name'] . ' ' . $ev['last_name']) ?></td>
                <td><?= $ev['score'] !== null ? e($ev['score']) : '—' ?></td>
                <td><?= e($ev['comments']) ?></td>
                <td><?= e(formatDate($ev['evaluated_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
