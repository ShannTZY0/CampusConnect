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
        redirect('/coordinator/adviser_assignments.php');
    }

    $groupId = (int) ($_POST['group_id'] ?? 0);
    $adviserId = (int) ($_POST['adviser_id'] ?? 0);

    $stmt = $db->prepare('UPDATE `groups` SET adviser_id = ?, status = "active" WHERE group_id = ?');
    $stmt->execute([$adviserId ?: null, $groupId]);

    if ($adviserId) {
        $db->prepare("UPDATE adviser_preferences SET status='approved' WHERE group_id=? AND adviser_id=?")->execute([$groupId, $adviserId]);
        $db->prepare("UPDATE adviser_preferences SET status='rejected' WHERE group_id=? AND adviser_id != ?")->execute([$groupId, $adviserId]);

        $members = $db->prepare('SELECT user_id FROM group_members WHERE group_id = ?');
        $members->execute([$groupId]);
        foreach ($members->fetchAll() as $m) {
            notifyUser((int) $m['user_id'], 'Adviser Assigned', 'An adviser has been assigned to your group.', '/student/groups/index.php');
        }
    }

    setFlash('success', 'Adviser assignment updated.');
    redirect('/coordinator/adviser_assignments.php');
}

$groups = $db->query("SELECT g.*, u.first_name AS adviser_first, u.last_name AS adviser_last
    FROM `groups` g LEFT JOIN users u ON u.user_id = g.adviser_id ORDER BY g.group_name")->fetchAll();
$advisers = $db->query('SELECT user_id, first_name, last_name FROM users WHERE role_id = ' . ROLE_ADVISER . ' ORDER BY last_name')->fetchAll();

$pageTitle = 'Adviser Assignments';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Adviser Assignments</h1><p>Assign or reassign advisers to groups.</p></div></div>

<div class="card">
    <table>
        <thead><tr><th>Group</th><th>Current Adviser</th><th>Assign</th></tr></thead>
        <tbody>
        <?php foreach ($groups as $g): ?>
        <tr>
            <td><?= e($g['group_name']) ?></td>
            <td><?= $g['adviser_id'] ? e($g['adviser_first'] . ' ' . $g['adviser_last']) : '<span class="stat-label">Unassigned</span>' ?></td>
            <td>
                <form method="POST" style="display:flex; gap:.5rem;">
                    <?= csrfField() ?>
                    <input type="hidden" name="group_id" value="<?= (int)$g['group_id'] ?>">
                    <select name="adviser_id">
                        <option value="">— None —</option>
                        <?php foreach ($advisers as $a): ?>
                        <option value="<?= (int)$a['user_id'] ?>" <?= $g['adviser_id'] == $a['user_id'] ? 'selected' : '' ?>><?= e($a['first_name'] . ' ' . $a['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$groups): ?><tr><td colspan="3" class="empty-state">No groups yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
