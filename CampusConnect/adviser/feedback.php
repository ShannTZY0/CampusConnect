<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_ADVISER;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/adviser/feedback.php');
    }

    $projectId = (int) ($_POST['project_id'] ?? 0);
    $text = trim($_POST['feedback_text'] ?? '');

    // verify ownership
    $chk = $db->prepare("SELECT g.leader_id FROM projects p JOIN `groups` g ON g.group_id = p.group_id WHERE p.project_id = ? AND g.adviser_id = ?");
    $chk->execute([$projectId, $user['user_id']]);
    $row = $chk->fetch();

    if ($row && $text !== '') {
        $stmt = $db->prepare('INSERT INTO feedback (project_id, adviser_id, feedback_text) VALUES (?, ?, ?)');
        $stmt->execute([$projectId, $user['user_id'], $text]);
        notifyUser((int) $row['leader_id'], 'New Adviser Feedback', 'Your adviser left new feedback on your project.', '/student/projects/index.php');
        setFlash('success', 'Feedback posted.');
    } else {
        setFlash('error', 'Unable to post feedback.');
    }
    redirect('/adviser/feedback.php' . ($projectId ? '?project_id=' . $projectId : ''));
}

$stmt = $db->prepare("SELECT pr.*, g.group_name FROM projects pr JOIN `groups` g ON g.group_id = pr.group_id
    WHERE g.adviser_id = ? ORDER BY pr.title");
$stmt->execute([$user['user_id']]);
$projects = $stmt->fetchAll();

$selectedId = (int) ($_GET['project_id'] ?? ($projects[0]['project_id'] ?? 0));

$feedbackList = [];
if ($selectedId) {
    $stmt = $db->prepare('SELECT * FROM feedback WHERE project_id = ? ORDER BY created_at DESC');
    $stmt->execute([$selectedId]);
    $feedbackList = $stmt->fetchAll();
}

$pageTitle = 'Feedback';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Feedback</h1><p>Leave feedback for advisee projects.</p></div></div>

<?php if (!$projects): ?>
<div class="card empty-state"><p>No advisee projects yet.</p></div>
<?php else: ?>
<div class="card" style="max-width:700px">
    <form method="GET">
        <div class="form-group">
            <label for="project_id">Select Project</label>
            <select id="project_id" name="project_id" onchange="this.form.submit()">
                <?php foreach ($projects as $p): ?>
                <option value="<?= (int)$p['project_id'] ?>" <?= $p['project_id'] == $selectedId ? 'selected' : '' ?>><?= e($p['group_name'] . ' — ' . $p['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <form method="POST" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="project_id" value="<?= $selectedId ?>">
        <div class="form-group">
            <label for="feedback_text">New Feedback</label>
            <textarea id="feedback_text" name="feedback_text" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Post Feedback</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><h3>Feedback History</h3></div>
    <?php if ($feedbackList): ?>
    <ul class="timeline">
        <?php foreach ($feedbackList as $f): ?>
        <li><span><?= nl2br(e($f['feedback_text'])) ?></span><span class="time"><?= e(formatDate($f['created_at'])) ?></span></li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p class="stat-label">No feedback posted yet.</p>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
