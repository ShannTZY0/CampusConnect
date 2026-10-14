<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$user = currentUser();

if (currentStudentGroupId()) {
    setFlash('info', 'You are already part of a group.');
    redirect('/student/groups/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/student/groups/create.php');
    }

    $name = trim($_POST['group_name'] ?? '');
    $memberEmails = array_filter(array_map('trim', explode(',', $_POST['member_emails'] ?? '')));

    if ($name === '') {
        setFlash('error', 'Group name is required.');
        redirect('/student/groups/create.php');
    }

    $db->beginTransaction();
    try {
        $code = 'GRP-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
        $stmt = $db->prepare('INSERT INTO `groups` (group_name, group_code, leader_id, status) VALUES (?, ?, ?, "forming")');
        $stmt->execute([$name, $code, $user['user_id']]);
        $groupId = (int) $db->lastInsertId();

        $stmt = $db->prepare('INSERT INTO group_members (group_id, user_id, role_in_group) VALUES (?, ?, "leader")');
        $stmt->execute([$groupId, $user['user_id']]);

        foreach ($memberEmails as $email) {
            $find = $db->prepare('SELECT user_id FROM users WHERE email = ? AND role_id = ? LIMIT 1');
            $find->execute([$email, ROLE_STUDENT]);
            $member = $find->fetch();
            if ($member) {
                $chk = $db->prepare('SELECT id FROM group_members WHERE user_id = ?');
                $chk->execute([$member['user_id']]);
                if (!$chk->fetch()) {
                    $ins = $db->prepare('INSERT INTO group_members (group_id, user_id, role_in_group) VALUES (?, ?, "member")');
                    $ins->execute([$groupId, $member['user_id']]);
                    notifyUser((int) $member['user_id'], 'Added to Group', "You've been added to group \"$name\".", '/student/groups/view.php?id=' . $groupId);
                }
            }
        }

        $db->commit();
        logActivity((int) $user['user_id'], 'create_group', 'Created group: ' . $name);
        setFlash('success', 'Group created successfully.');
        redirect('/student/groups/view.php?id=' . $groupId);
    } catch (Exception $e) {
        $db->rollBack();
        setFlash('error', 'Failed to create group. Please try again.');
        redirect('/student/groups/create.php');
    }
}

$pageTitle = 'Create Group';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div><h1>Create a Group</h1><p>You will automatically be set as the group leader.</p></div>
</div>
<div class="card" style="max-width:600px">
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <div class="form-group">
            <label for="group_name">Group Name</label>
            <input type="text" id="group_name" name="group_name" required>
        </div>
        <div class="form-group">
            <label for="member_emails">Groupmates' Emails (comma-separated, optional)</label>
            <input type="text" id="member_emails" name="member_emails" placeholder="member1@school.edu, member2@school.edu">
        </div>
        <button type="submit" class="btn btn-primary">Create Group</button>
        <a href="<?= BASE_URL ?>/student/groups/index.php" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
