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
        redirect('/adviser/progress.php');
    }

    $projectId = (int) ($_POST['project_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $dueDate = $_POST['due_date'] ?? null;

    $chk = $db->prepare("SELECT p.project_id FROM projects p JOIN `groups` g ON g.group_id = p.group_id WHERE p.project_id = ? AND g.adviser_id = ?");
    $chk->execute([$projectId, $user['user_id']]);

    if ($chk->fetch() && $title !== '') {
        $stmt = $db->prepare('INSERT INTO project_milestones (project_id, title, due_date, created_by) VALUES (?, ?, ?, ?)');
        $stmt->execute([$projectId, $title, $dueDate ?: null, $user['user_id']]);
        setFlash('success', 'Milestone added.');
    }
    redirect('/adviser/progress.php?project_id=' . $projectId);
}

$stmt = $db->prepare("SELECT pr.*, g.group_name FROM projects pr JOIN `groups` g ON g.group_id = pr.group_id
    WHERE g.adviser_id = ? ORDER BY pr.title");
$stmt->execute([$user['user_id']]);
$projects = $stmt->fetchAll();

$selectedId = (int) ($_GET['project_id'] ?? ($projects[0]['project_id'] ?? 0));
$selectedProject = null;
$milestones = [];
$logs = [];

foreach ($projects as $p) {
    if ($p['project_id'] == $selectedId) { $selectedProject = $p; break; }
}

if ($selectedId) {
    $stmt = $db->prepare('SELECT * FROM project_milestones WHERE project_id = ? ORDER BY due_date ASC');
    $stmt->execute([$selectedId]);
    $milestones = $stmt->fetchAll();

    $stmt = $db->prepare('SELECT * FROM project_progress_logs WHERE project_id = ? ORDER BY created_at DESC LIMIT 10');
    $stmt->execute([$selectedId]);
    $logs = $stmt->fetchAll();
}

$pageTitle = 'Progress Tracking';
$extraCss = ['dashboard'];
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Progress Tracking</h1></div></div>

<?php if (!$projects): ?>
<div class="card empty-state"><p>No advisee projects yet.</p></div>
<?php else: ?>
<form method="GET" class="card" style="max-width:500px">
    <div class="form-group">
        <label for="project_id">Select Project</label>
        <select id="project_id" name="project_id" onchange="this.form.submit()">
            <?php foreach ($projects as $p): ?>
            <option value="<?= (int)$p['project_id'] ?>" <?= $p['project_id'] == $selectedId ? 'selected' : '' ?>><?= e($p['group_name'] . ' — ' . $p['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<?php if ($selectedProject): ?>
<div class="card">
    <h3><?= (int)$selectedProject['progress_percent'] ?>% Complete</h3>
    <div class="progress-bar"><div class="progress-bar-fill" data-value="<?= (int)$selectedProject['progress_percent'] ?>%" style="width:<?= (int)$selectedProject['progress_percent'] ?>%"></div></div>
</div>

<div class="card">
    <div class="card-header"><h3>Milestones</h3></div>
    <?php if ($milestones): ?>
    <table>
        <thead><tr><th>Title</th><th>Due Date</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($milestones as $m): ?>
        <tr><td><?= e($m['title']) ?></td><td><?= e(formatDate($m['due_date'],'M d, Y')) ?></td><td><?= statusBadge($m['status']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="stat-label">No milestones set.</p>
    <?php endif; ?>

    <form method="POST" style="margin-top:1rem" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="project_id" value="<?= (int)$selectedProject['project_id'] ?>">
        <div class="form-grid">
            <div class="form-group"><label>New Milestone Title</label><input type="text" name="title" required></div>
            <div class="form-group"><label>Due Date</label><input type="date" name="due_date"></div>
        </div>
        <button type="submit" class="btn btn-primary">Add Milestone</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><h3>Recent Progress Logs (from students)</h3></div>
    <?php if ($logs): ?>
    <ul class="timeline">
        <?php foreach ($logs as $l): ?>
        <li><span><?= nl2br(e($l['update_text'])) ?> (<?= (int)$l['progress_percent'] ?>%)</span><span class="time"><?= e(formatDate($l['created_at'])) ?></span></li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p class="stat-label">No progress logs submitted yet.</p>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
