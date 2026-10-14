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
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM projects WHERE project_id = ? AND group_id = ?');
$stmt->execute([$id, $groupId]);
$project = $stmt->fetch();

if (!$project) {
    setFlash('error', 'Project not found.');
    redirect('/student/projects/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/student/projects/progress.php?id=' . $id);
    }

    $update = trim($_POST['update_text'] ?? '');
    $percent = max(0, min(100, (int) ($_POST['progress_percent'] ?? $project['progress_percent'])));

    if ($update === '') {
        setFlash('error', 'Please describe your progress update.');
        redirect('/student/projects/progress.php?id=' . $id);
    }

    $db->beginTransaction();
    $ins = $db->prepare('INSERT INTO project_progress_logs (project_id, update_text, progress_percent, logged_by) VALUES (?, ?, ?, ?)');
    $ins->execute([$id, $update, $percent, $user['user_id']]);

    $status = $percent >= 100 ? 'for_evaluation' : ($percent > 0 ? 'in_progress' : $project['status']);
    $upd = $db->prepare('UPDATE projects SET progress_percent = ?, status = ? WHERE project_id = ?');
    $upd->execute([$percent, $status, $id]);
    $db->commit();

    setFlash('success', 'Progress logged.');
    redirect('/student/projects/view.php?id=' . $id);
}

$stmt = $db->prepare('SELECT * FROM project_progress_logs WHERE project_id = ? ORDER BY created_at DESC');
$stmt->execute([$id]);
$logs = $stmt->fetchAll();

$pageTitle = 'Log Progress';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>Log Progress</h1><p><?= e($project['title']) ?></p></div></div>

<div class="card" style="max-width:700px">
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="form-group">
            <label for="update_text">Progress Update</label>
            <textarea id="update_text" name="update_text" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label for="progress_percent">Overall Progress (%)</label>
            <input type="number" id="progress_percent" name="progress_percent" min="0" max="100" value="<?= (int)$project['progress_percent'] ?>">
        </div>
        <button type="submit" class="btn btn-primary">Submit Update</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><h3>History</h3></div>
    <?php if ($logs): ?>
    <ul class="timeline">
        <?php foreach ($logs as $l): ?>
        <li>
            <span><?= nl2br(e($l['update_text'])) ?> (<?= (int)$l['progress_percent'] ?>%)</span>
            <span class="time"><?= e(formatDate($l['created_at'])) ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p class="stat-label">No progress logs yet.</p>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
