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
$myGroupId = currentStudentGroupId();

// Students may only view their own group
if (!$groupId || $groupId !== $myGroupId) {
    setFlash('error', 'Group not found.');
    redirect('/student/groups/index.php');
}

$stmt = $db->prepare('SELECT g.*, u.first_name AS adviser_first, u.last_name AS adviser_last
    FROM `groups` g LEFT JOIN users u ON u.user_id = g.adviser_id WHERE g.group_id = ?');
$stmt->execute([$groupId]);
$group = $stmt->fetch();

if (!$group) {
    setFlash('error', 'Group not found.');
    redirect('/student/groups/index.php');
}

$stmt = $db->prepare('SELECT gm.*, u.first_name, u.last_name, u.email, u.profile_photo
    FROM group_members gm JOIN users u ON u.user_id = gm.user_id
    WHERE gm.group_id = ? ORDER BY gm.role_in_group DESC');
$stmt->execute([$groupId]);
$members = $stmt->fetchAll();

$pageTitle = e($group['group_name']);
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e($group['group_name']) ?></h1>
        <p>Group Code: <?= e($group['group_code']) ?> · <?= statusBadge($group['status']) ?></p>
    </div>
    <?php if ($group['leader_id'] == $user['user_id']): ?>
    <a href="<?= BASE_URL ?>/student/groups/members.php?id=<?= $groupId ?>" class="btn btn-primary">Manage Members</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header"><h3>Adviser</h3></div>
    <?php if ($group['adviser_id']): ?>
        <p><?= e($group['adviser_first'] . ' ' . $group['adviser_last']) ?></p>
    <?php else: ?>
        <p class="stat-label">No adviser assigned yet. <a href="<?= BASE_URL ?>/student/advisers/preference.php">Submit adviser preferences</a>.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header"><h3>Members (<?= count($members) ?>)</h3></div>
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead>
        <tbody>
        <?php foreach ($members as $m): ?>
            <tr>
                <td><?= e($m['first_name'] . ' ' . $m['last_name']) ?></td>
                <td><?= e($m['email']) ?></td>
                <td><?= e(ucfirst($m['role_in_group'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
