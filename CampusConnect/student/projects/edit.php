<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
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
        redirect('/student/projects/edit.php?id=' . $id);
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');

    $upd = $db->prepare('UPDATE projects SET title=?, description=?, category=? WHERE project_id=?');
    $upd->execute([$title, $description, $category, $id]);

    setFlash('success', 'Project updated.');
    redirect('/student/projects/view.php?id=' . $id);
}

$pageTitle = 'Edit Project';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>Edit Project</h1></div></div>
<div class="card" style="max-width:700px">
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="form-group">
            <label for="title">Project Title</label>
            <input type="text" id="title" name="title" value="<?= e($project['title']) ?>" required>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" value="<?= e($project['category']) ?>">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"><?= e($project['description']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="<?= BASE_URL ?>/student/projects/view.php?id=<?= $id ?>" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
