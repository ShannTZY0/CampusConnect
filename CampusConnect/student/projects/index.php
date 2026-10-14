<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$groupId = currentStudentGroupId();
$projects = [];
if ($groupId) {
    $stmt = getDB()->prepare('SELECT * FROM projects WHERE group_id = ? ORDER BY created_at DESC');
    $stmt->execute([$groupId]);
    $projects = $stmt->fetchAll();
}

$pageTitle = 'Projects';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div><h1>Projects</h1><p>Your group's capstone project(s).</p></div>
    <?php if ($groupId): ?>
    <a href="<?= BASE_URL ?>/student/projects/create.php" class="btn btn-primary">+ New Project</a>
    <?php endif; ?>
</div>

<?php if (!$groupId): ?>
<div class="card empty-state"><p>Join or create a group first.</p></div>
<?php elseif (!$projects): ?>
<div class="card empty-state"><p>No projects yet. Once your proposal is approved, create a project to start tracking progress.</p></div>
<?php else: ?>
<div class="card">
    <table>
        <thead><tr><th>Title</th><th>Status</th><th>Progress</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($projects as $p): ?>
            <tr>
                <td><?= e($p['title']) ?></td>
                <td><?= statusBadge($p['status']) ?></td>
                <td><?= (int)$p['progress_percent'] ?>%</td>
                <td>
                    <a href="<?= BASE_URL ?>/student/projects/view.php?id=<?= (int)$p['project_id'] ?>">View</a> ·
                    <a href="<?= BASE_URL ?>/student/projects/edit.php?id=<?= (int)$p['project_id'] ?>">Edit</a> ·
                    <a href="<?= BASE_URL ?>/student/projects/progress.php?id=<?= (int)$p['project_id'] ?>">Log Progress</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
