<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$groupId = currentStudentGroupId();
$proposals = [];
if ($groupId) {
    $stmt = getDB()->prepare('SELECT * FROM proposals WHERE group_id = ? ORDER BY created_at DESC');
    $stmt->execute([$groupId]);
    $proposals = $stmt->fetchAll();
}

$pageTitle = 'Proposals';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div><h1>Proposals</h1><p>Manage your capstone project proposal.</p></div>
    <?php if ($groupId): ?>
    <a href="<?= BASE_URL ?>/student/proposals/create.php" class="btn btn-primary">+ New Proposal</a>
    <?php endif; ?>
</div>

<?php if (!$groupId): ?>
<div class="card empty-state"><p>Join or create a group first before submitting a proposal.</p></div>
<?php elseif (!$proposals): ?>
<div class="card empty-state"><p>No proposals yet.</p></div>
<?php else: ?>
<div class="card">
    <table>
        <thead><tr><th>Title</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($proposals as $p): ?>
            <tr>
                <td><?= e($p['title']) ?></td>
                <td><?= statusBadge($p['status']) ?></td>
                <td><?= e(formatDate($p['submitted_at'], 'M d, Y')) ?></td>
                <td><a href="<?= BASE_URL ?>/student/proposals/view.php?id=<?= (int)$p['proposal_id'] ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
