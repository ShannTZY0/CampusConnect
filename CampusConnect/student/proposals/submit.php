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

if (!$proposal || !in_array($proposal['status'], ['draft', 'revision'], true)) {
    setFlash('error', 'This proposal cannot be submitted.');
    redirect('/student/proposals/index.php');
}

$upd = $db->prepare('UPDATE proposals SET status="submitted", submitted_at=NOW() WHERE proposal_id=?');
$upd->execute([$id]);

// Notify the coordinator(s)
$coords = $db->query('SELECT user_id FROM users WHERE role_id = ' . ROLE_COORDINATOR)->fetchAll();
foreach ($coords as $c) {
    notifyUser((int) $c['user_id'], 'New Proposal Submitted', 'A proposal titled "' . $proposal['title'] . '" was submitted for review.', '/coordinator/proposals.php');
}

logActivity((int) $_SESSION['user_id'], 'submit_proposal', 'Proposal #' . $id . ' submitted');
setFlash('success', 'Proposal submitted for review.');
redirect('/student/proposals/view.php?id=' . $id);
