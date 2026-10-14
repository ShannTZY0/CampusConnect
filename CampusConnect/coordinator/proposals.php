<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/coordinator/proposals.php');
    }
    $proposalId = (int) ($_POST['proposal_id'] ?? 0);
    $decision = $_POST['decision'] ?? '';
    $comments = trim($_POST['comments'] ?? '');
    $statusMap = ['approve' => 'approved', 'revise' => 'revision', 'reject' => 'rejected', 'review' => 'under_review'];

    if (isset($statusMap[$decision])) {
        $user = currentUser();
        $stmt = $db->prepare('UPDATE proposals SET status=?, review_comments=?, reviewed_by=? WHERE proposal_id=?');
        $stmt->execute([$statusMap[$decision], $comments, $user['user_id'], $proposalId]);

        $g = $db->prepare('SELECT g.leader_id, p.title FROM proposals p JOIN `groups` g ON g.group_id = p.group_id WHERE p.proposal_id = ?');
        $g->execute([$proposalId]);
        $row = $g->fetch();
        if ($row) {
            notifyUser((int) $row['leader_id'], 'Proposal Update', 'Your proposal "' . $row['title'] . '" is now ' . $statusMap[$decision] . '.', '/student/proposals/index.php');
        }
        setFlash('success', 'Proposal updated.');
    }
    redirect('/coordinator/proposals.php');
}

$proposals = $db->query("SELECT p.*, g.group_name FROM proposals p JOIN `groups` g ON g.group_id = p.group_id ORDER BY p.created_at DESC")->fetchAll();

$pageTitle = 'Proposals';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Proposals</h1><p>All proposal submissions program-wide.</p></div></div>
<?php if (!$proposals): ?>
<div class="card empty-state"><p>No proposals yet.</p></div>
<?php else: ?>
<?php foreach ($proposals as $p): ?>
<div class="card">
    <div class="card-header"><div><h3><?= e($p['title']) ?></h3><p class="stat-label"><?= e($p['group_name']) ?> · <?= statusBadge($p['status']) ?></p></div></div>
    <p><?= nl2br(e($p['abstract'])) ?></p>
    <?php if ($p['file_path']): ?><p><a href="<?= BASE_URL ?>/uploads/proposals/<?= e($p['file_path']) ?>" target="_blank">📎 View Document</a></p><?php endif; ?>
    <?php if (in_array($p['status'], ['submitted','under_review','revision'], true)): ?>
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="proposal_id" value="<?= (int)$p['proposal_id'] ?>">
        <div class="form-group"><label>Comments</label><textarea name="comments" rows="2"></textarea></div>
        <button type="submit" name="decision" value="approve" class="btn btn-success">Approve</button>
        <button type="submit" name="decision" value="revise" class="btn btn-outline">Request Revision</button>
        <button type="submit" name="decision" value="reject" class="btn btn-danger">Reject</button>
    </form>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
