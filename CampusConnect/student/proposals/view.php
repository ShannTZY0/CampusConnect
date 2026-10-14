<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$groupId = currentStudentGroupId();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM proposals WHERE proposal_id = ? AND group_id = ?');
$stmt->execute([$id, $groupId]);
$proposal = $stmt->fetch();

if (!$proposal) {
    setFlash('error', 'Proposal not found.');
    redirect('/student/proposals/index.php');
}

$pageTitle = 'Proposal Details';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div><h1><?= e($proposal['title']) ?></h1><p><?= statusBadge($proposal['status']) ?></p></div>
    <div>
        <?php if (in_array($proposal['status'], ['draft', 'revision'], true)): ?>
        <a href="<?= BASE_URL ?>/student/proposals/edit.php?id=<?= $id ?>" class="btn btn-outline">Edit</a>
        <a href="<?= BASE_URL ?>/student/proposals/submit.php?id=<?= $id ?>" class="btn btn-primary" data-confirm="Submit this proposal for review?">Submit for Review</a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h3>Abstract</h3>
    <p><?= nl2br(e($proposal['abstract'])) ?></p>
    <?php if ($proposal['file_path']): ?>
        <p><a href="<?= BASE_URL ?>/uploads/proposals/<?= e($proposal['file_path']) ?>" target="_blank">📎 View attached document</a></p>
    <?php endif; ?>
</div>

<?php if ($proposal['review_comments']): ?>
<div class="card">
    <h3>Coordinator/Adviser Feedback</h3>
    <p><?= nl2br(e($proposal['review_comments'])) ?></p>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
