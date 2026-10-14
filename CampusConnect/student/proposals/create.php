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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/student/proposals/create.php');
    }

    $title = trim($_POST['title'] ?? '');
    $abstract = trim($_POST['abstract'] ?? '');

    if ($title === '') {
        setFlash('error', 'Title is required.');
        redirect('/student/proposals/create.php');
    }

    $filePath = null;
    if (!empty($_FILES['proposal_file']['name'])) {
        $filePath = handleUpload($_FILES['proposal_file'], UPLOAD_PROPOSALS);
    }

    $stmt = $db->prepare('INSERT INTO proposals (group_id, title, abstract, file_path, status) VALUES (?, ?, ?, ?, "draft")');
    $stmt->execute([$groupId, $title, $abstract, $filePath]);

    setFlash('success', 'Proposal saved as draft. You can submit it once ready.');
    redirect('/student/proposals/view.php?id=' . $db->lastInsertId());
}

$pageTitle = 'New Proposal';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>New Proposal</h1><p>Draft your capstone project proposal.</p></div></div>
<div class="card" style="max-width:700px">
    <form method="POST" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>
        <div class="form-group">
            <label for="title">Project Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="abstract">Abstract</label>
            <textarea id="abstract" name="abstract" rows="6"></textarea>
        </div>
        <div class="form-group">
            <label for="proposal_file">Attach Document (optional)</label>
            <input type="file" id="proposal_file" name="proposal_file" accept=".pdf,.doc,.docx">
        </div>
        <button type="submit" class="btn btn-primary">Save Draft</button>
        <a href="<?= BASE_URL ?>/student/proposals/index.php" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
