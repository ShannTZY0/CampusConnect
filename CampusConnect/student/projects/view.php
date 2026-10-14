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

$stmt = $db->prepare('SELECT * FROM projects WHERE project_id = ? AND group_id = ?');
$stmt->execute([$id, $groupId]);
$project = $stmt->fetch();

if (!$project) {
    setFlash('error', 'Project not found.');
    redirect('/student/projects/index.php');
}

$stmt = $db->prepare('SELECT * FROM project_milestones WHERE project_id = ? ORDER BY due_date ASC');
$stmt->execute([$id]);
$milestones = $stmt->fetchAll();

$stmt = $db->prepare('SELECT f.*, u.first_name, u.last_name FROM feedback f
    JOIN users u ON u.user_id = f.adviser_id WHERE f.project_id = ? ORDER BY f.created_at DESC');
$stmt->execute([$id]);
$feedback = $stmt->fetchAll();

$pageTitle = e($project['title']);
$extraCss = ['dashboard'];
$extraJs = ['dashboard'];
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div><h1><?= e($project['title']) ?></h1><p><?= statusBadge($project['status']) ?></p></div>
    <div>
        <a href="<?= BASE_URL ?>/student/projects/edit.php?id=<?= $id ?>" class="btn btn-outline">Edit</a>
        <a href="<?= BASE_URL ?>/student/projects/progress.php?id=<?= $id ?>" class="btn btn-primary">Log Progress</a>
    </div>
</div>

<div class="card">
    <h3>Progress: <?= (int)$project['progress_percent'] ?>%</h3>
    <div class="progress-bar"><div class="progress-bar-fill" data-value="<?= (int)$project['progress_percent'] ?>%" style="width:<?= (int)$project['progress_percent'] ?>%"></div></div>
    <p style="margin-top:1rem"><?= nl2br(e($project['description'])) ?></p>
</div>

<div class="card">
    <div class="card-header"><h3>Milestones</h3></div>
    <?php if ($milestones): ?>
    <table>
        <thead><tr><th>Title</th><th>Due Date</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($milestones as $m): ?>
            <tr><td><?= e($m['title']) ?></td><td><?= e(formatDate($m['due_date'], 'M d, Y')) ?></td><td><?= statusBadge($m['status']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="stat-label">No milestones set by your coordinator/adviser yet.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header"><h3>Adviser Feedback</h3></div>
    <?php if ($feedback): ?>
    <ul class="timeline">
        <?php foreach ($feedback as $f): ?>
        <li>
            <span><strong><?= e($f['first_name'] . ' ' . $f['last_name']) ?></strong><br><?= nl2br(e($f['feedback_text'])) ?></span>
            <span class="time"><?= e(formatDate($f['created_at'])) ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p class="stat-label">No feedback yet.</p>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
