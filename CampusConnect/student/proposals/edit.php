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

$stmt = $db->prepare('SELECT * FROM proposals WHERE proposal_id = ? AND group_id = ?');
$stmt->execute([$id, $groupId]);
$proposal = $stmt->fetch();

if (!$proposal || !in_array($proposal['status'], ['draft', 'revision'], true)) {
    setFlash('error', 'This proposal cannot be edited.');
    redirect('/student/proposals/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/student/proposals/edit.php?id=' . $id);
    }

    $title = trim($_POST['title'] ?? '');
    $abstract = trim($_POST['abstract'] ?? '');
    $filePath = $proposal['file_path'];

    if (!empty($_FILES['proposal_file']['name'])) {
        $uploaded = handleUpload($_FILES['proposal_file'], UPLOAD_PROPOSALS);
        if ($uploaded) {
            $filePath = $uploaded;
        }
    }

    $upd = $db->prepare('UPDATE proposals SET title=?, abstract=?, file_path=?, status="draft" WHERE proposal_id=?');
    $upd->execute([$title, $abstract, $filePath, $id]);

    setFlash('success', 'Proposal updated.');
    redirect('/student/proposals/view.php?id=' . $id);
}

$pageTitle = 'Edit Proposal';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>Edit Proposal</h1></div></div>
<div class="card" style="max-width:700px">
    <form method="POST" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="form-group">
            <label for="title">Project Title</label>
            <input type="text" id="title" name="title" value="<?= e($proposal['title']) ?>" required>
        </div>
        <div class="form-group">
            <label for="abstract">Abstract</label>
            <textarea id="abstract" name="abstract" rows="6"><?= e($proposal['abstract']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="proposal_file">Replace Attached Document (optional)</label>
            <input type="file" id="proposal_file" name="proposal_file" accept=".pdf,.doc,.docx">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="<?= BASE_URL ?>/student/proposals/view.php?id=<?= $id ?>" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
