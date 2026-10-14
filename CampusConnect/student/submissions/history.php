<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$groupId = currentStudentGroupId();
$submissions = [];

if ($groupId) {
    $stmt = $db->prepare('SELECT s.* FROM submissions s
        JOIN projects p ON p.project_id = s.project_id
        WHERE p.group_id = ? ORDER BY s.submitted_at DESC');
    $stmt->execute([$groupId]);
    $submissions = $stmt->fetchAll();
}

$pageTitle = 'Submission History';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>Submission History</h1></div></div>
<?php if (!$submissions): ?>
<div class="card empty-state"><p>No submissions on record.</p></div>
<?php else: ?>
<div class="card">
    <table>
        <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Remarks</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($submissions as $s): ?>
            <tr>
                <td><?= e($s['title']) ?></td>
                <td><?= e(ucfirst($s['submission_type'])) ?></td>
                <td><?= statusBadge($s['status']) ?></td>
                <td><?= e($s['remarks']) ?></td>
                <td><?= e(formatDate($s['submitted_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
