<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$groupId = currentStudentGroupId();
if ($groupId) {
    redirect('/student/groups/view.php?id=' . $groupId);
}

$pageTitle = 'My Group';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div><h1>My Group</h1><p>You have not joined or created a group yet.</p></div>
</div>
<div class="card empty-state">
    <p>Start by creating a new capstone group and inviting your groupmates.</p>
    <a class="btn btn-primary" href="<?= BASE_URL ?>/student/groups/create.php">+ Create Group</a>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
