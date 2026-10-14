<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$groupId = currentStudentGroupId();
$submissions = [];
$project = null;

if ($groupId) {
    $stmt = $db->prepare('SELECT * FROM projects WHERE group_id = ? ORDER BY project_id DESC LIMIT 1');
    $stmt->execute([$groupId]);
    $project = $stmt->fetch();

    if ($project) {
        $stmt = $db->prepare('SELECT * FROM submissions WHERE project_id = ? ORDER BY submitted_at DESC');
        $stmt->execute([$project['project_id']]);
        $submissions = $stmt->fetchAll();
    }
}

$pageTitle = 'Submissions';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div><h1>Submissions</h1><p>Upload and track your project documents.</p></div>
    <?php if ($project): ?>
    <a href="<?= BASE_URL ?>/student/submissions/upload.php" class="btn btn-primary">+ Upload Submission</a>
    <?php endif; ?>
</div>

<?php if (!$project): ?>
<div class="card empty-state"><p>Create a project first before uploading submissions.</p></div>
<?php elseif (!$submissions): ?>
<div class="card empty-state"><p>No submissions yet.</p></div>
<?php else: ?>
<div class="card">
    <table>
        <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($submissions as $s): ?>
            <tr>
                <td><?= e($s['title']) ?></td>
                <td><?= e(ucfirst($s['submission_type'])) ?></td>
                <td><?= statusBadge($s['status']) ?></td>
                <td><?= e(formatDate($s['submitted_at'])) ?></td>
                <td><a href="<?= BASE_URL ?>/uploads/submissions/<?= e($s['file_path']) ?>" target="_blank">Download</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<p><a href="<?= BASE_URL ?>/student/submissions/history.php">View full history →</a></p>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
