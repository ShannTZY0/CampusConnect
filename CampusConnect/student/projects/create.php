<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$groupId = currentStudentGroupId();
if (!$groupId) {
    setFlash('error', 'Join or create a group first.');
    redirect('/student/groups/index.php');
}

$stmt = $db->prepare("SELECT * FROM proposals WHERE group_id = ? AND status = 'approved' ORDER BY proposal_id DESC");
$stmt->execute([$groupId]);
$approvedProposals = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/student/projects/create.php');
    }

    $proposalId = (int) ($_POST['proposal_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if ($title === '') {
        setFlash('error', 'Title is required.');
        redirect('/student/projects/create.php');
    }

    $stmt = $db->prepare('INSERT INTO projects (group_id, proposal_id, title, description, category, status, start_date)
        VALUES (?, ?, ?, ?, ?, "proposed", CURDATE())');
    $stmt->execute([$groupId, $proposalId ?: null, $title, $description, $category]);

    setFlash('success', 'Project created.');
    redirect('/student/projects/view.php?id=' . $db->lastInsertId());
}

$pageTitle = 'New Project';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>New Project</h1></div></div>
<div class="card" style="max-width:700px">
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <?php if ($approvedProposals): ?>
        <div class="form-group">
            <label for="proposal_id">Based on Approved Proposal</label>
            <select id="proposal_id" name="proposal_id">
                <option value="">— None —</option>
                <?php foreach ($approvedProposals as $p): ?>
                <option value="<?= (int)$p['proposal_id'] ?>"><?= e($p['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="title">Project Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" placeholder="e.g. Web App, Mobile App, IoT">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Project</button>
        <a href="<?= BASE_URL ?>/student/projects/index.php" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
