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
        redirect('/adviser/evaluations.php');
    }

    $projectId = (int) ($_POST['project_id'] ?? 0);
    $type = trim($_POST['evaluation_type'] ?? 'progress');
    $score = $_POST['score'] !== '' ? (float) $_POST['score'] : null;
    $comments = trim($_POST['comments'] ?? '');

    $chk = $db->prepare("SELECT p.project_id FROM projects p JOIN `groups` g ON g.group_id = p.group_id WHERE p.project_id = ? AND g.adviser_id = ?");
    $chk->execute([$projectId, $user['user_id']]);

    if ($chk->fetch()) {
        $stmt = $db->prepare('INSERT INTO evaluations (project_id, evaluator_id, evaluation_type, score, comments) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$projectId, $user['user_id'], $type, $score, $comments]);
        setFlash('success', 'Evaluation recorded.');
    }
    redirect('/adviser/evaluations.php');
}

$stmt = $db->prepare("SELECT pr.*, g.group_name FROM projects pr JOIN `groups` g ON g.group_id = pr.group_id
    WHERE g.adviser_id = ? ORDER BY pr.title");
$stmt->execute([$user['user_id']]);
$projects = $stmt->fetchAll();

$stmt = $db->prepare("SELECT e.*, pr.title AS project_title FROM evaluations e
    JOIN projects pr ON pr.project_id = e.project_id
    WHERE e.evaluator_id = ? ORDER BY e.evaluated_at DESC");
$stmt->execute([$user['user_id']]);
$myEvaluations = $stmt->fetchAll();

$pageTitle = 'Evaluations';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Evaluations</h1><p>Record evaluation scores for your advisee projects.</p></div></div>

<?php if ($projects): ?>
<div class="card" style="max-width:700px">
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <div class="form-group">
            <label for="project_id">Project</label>
            <select id="project_id" name="project_id" required>
                <?php foreach ($projects as $p): ?>
                <option value="<?= (int)$p['project_id'] ?>"><?= e($p['group_name'] . ' — ' . $p['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="evaluation_type">Type</label>
                <select id="evaluation_type" name="evaluation_type">
                    <option value="progress">Progress</option>
                    <option value="midterm">Midterm</option>
                    <option value="final">Final</option>
                </select>
            </div>
            <div class="form-group">
                <label for="score">Score (0-100)</label>
                <input type="number" id="score" name="score" min="0" max="100" step="0.01">
            </div>
        </div>
        <div class="form-group">
            <label for="comments">Comments</label>
            <textarea id="comments" name="comments" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Evaluation</button>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h3>My Evaluation History</h3></div>
    <?php if ($myEvaluations): ?>
    <table>
        <thead><tr><th>Project</th><th>Type</th><th>Score</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($myEvaluations as $ev): ?>
        <tr><td><?= e($ev['project_title']) ?></td><td><?= e(ucfirst($ev['evaluation_type'])) ?></td><td><?= $ev['score'] !== null ? e($ev['score']) : '—' ?></td><td><?= e(formatDate($ev['evaluated_at'])) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="stat-label">No evaluations recorded yet.</p>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
