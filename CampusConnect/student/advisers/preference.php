<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$user = currentUser();
$groupId = currentStudentGroupId();

if (!$groupId) {
    setFlash('error', 'Join or create a group first.');
    redirect('/student/groups/index.php');
}

$stmt = $db->prepare('SELECT * FROM `groups` WHERE group_id = ?');
$stmt->execute([$groupId]);
$group = $stmt->fetch();

if ($group['leader_id'] != $user['user_id']) {
    setFlash('error', 'Only the group leader can submit adviser preferences.');
    redirect('/student/advisers/index.php');
}

$advisers = $db->query('SELECT user_id, first_name, last_name, department FROM users WHERE role_id = ' . ROLE_ADVISER . ' ORDER BY last_name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/student/advisers/preference.php');
    }

    $choices = array_filter([$_POST['choice_1'] ?? '', $_POST['choice_2'] ?? '', $_POST['choice_3'] ?? '']);

    $db->prepare('DELETE FROM adviser_preferences WHERE group_id = ?')->execute([$groupId]);
    $rank = 1;
    foreach ($choices as $adviserId) {
        $ins = $db->prepare('INSERT INTO adviser_preferences (group_id, adviser_id, preference_rank) VALUES (?, ?, ?)');
        $ins->execute([$groupId, (int) $adviserId, $rank]);
        $rank++;
    }

    setFlash('success', 'Adviser preferences submitted to the coordinator.');
    redirect('/student/advisers/index.php');
}

$pageTitle = 'Adviser Preferences';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>Adviser Preferences</h1><p>Rank up to 3 preferred advisers for your group.</p></div></div>
<div class="card" style="max-width:600px">
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <?php for ($i = 1; $i <= 3; $i++): ?>
        <div class="form-group">
            <label>Choice <?= $i ?><?= $i === 1 ? ' (required)' : ' (optional)' ?></label>
            <select name="choice_<?= $i ?>" <?= $i === 1 ? 'required' : '' ?>>
                <option value="">— Select Adviser —</option>
                <?php foreach ($advisers as $a): ?>
                <option value="<?= (int)$a['user_id'] ?>"><?= e($a['first_name'] . ' ' . $a['last_name'] . ' (' . $a['department'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endfor; ?>
        <button type="submit" class="btn btn-primary">Submit Preferences</button>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
