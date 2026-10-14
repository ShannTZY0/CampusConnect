<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$user = currentUser();
$groupId = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM `groups` WHERE group_id = ?');
$stmt->execute([$groupId]);
$group = $stmt->fetch();

if (!$group || $group['leader_id'] != $user['user_id']) {
    setFlash('error', 'Only the group leader can manage members.');
    redirect('/student/groups/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/student/groups/members.php?id=' . $groupId);
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $email = trim($_POST['email'] ?? '');
        $find = $db->prepare('SELECT user_id FROM users WHERE email = ? AND role_id = ? LIMIT 1');
        $find->execute([$email, ROLE_STUDENT]);
        $member = $find->fetch();
        if (!$member) {
            setFlash('error', 'No student account found with that email.');
        } else {
            $chk = $db->prepare('SELECT id FROM group_members WHERE user_id = ?');
            $chk->execute([$member['user_id']]);
            if ($chk->fetch()) {
                setFlash('error', 'That student is already in a group.');
            } else {
                $ins = $db->prepare('INSERT INTO group_members (group_id, user_id, role_in_group) VALUES (?, ?, "member")');
                $ins->execute([$groupId, $member['user_id']]);
                notifyUser((int) $member['user_id'], 'Added to Group', 'You have been added to a capstone group.', '/student/groups/index.php');
                setFlash('success', 'Member added.');
            }
        }
    } elseif ($action === 'remove') {
        $memberId = (int) ($_POST['user_id'] ?? 0);
        if ($memberId !== (int) $group['leader_id']) {
            $del = $db->prepare('DELETE FROM group_members WHERE group_id = ? AND user_id = ?');
            $del->execute([$groupId, $memberId]);
            setFlash('success', 'Member removed.');
        } else {
            setFlash('error', 'The group leader cannot be removed.');
        }
    }

    redirect('/student/groups/members.php?id=' . $groupId);
}

$stmt = $db->prepare('SELECT gm.*, u.first_name, u.last_name, u.email
    FROM group_members gm JOIN users u ON u.user_id = gm.user_id
    WHERE gm.group_id = ? ORDER BY gm.role_in_group DESC');
$stmt->execute([$groupId]);
$members = $stmt->fetchAll();

$pageTitle = 'Manage Members';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div><h1>Manage Members</h1><p><?= e($group['group_name']) ?></p></div>
    <a href="<?= BASE_URL ?>/student/groups/view.php?id=<?= $groupId ?>" class="btn btn-outline">Back to Group</a>
</div>

<div class="card">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($members as $m): ?>
            <tr>
                <td><?= e($m['first_name'] . ' ' . $m['last_name']) ?></td>
                <td><?= e($m['email']) ?></td>
                <td><?= e(ucfirst($m['role_in_group'])) ?></td>
                <td>
                    <?php if ($m['role_in_group'] !== 'leader'): ?>
                    <form method="POST" style="display:inline" data-confirm="Remove this member?">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="user_id" value="<?= (int) $m['user_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card" style="max-width:480px">
    <div class="card-header"><h3>Add Member</h3></div>
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label for="email">Student Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Add to Group</button>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
