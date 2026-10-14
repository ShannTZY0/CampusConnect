<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$user = currentUser();
$groupId = currentStudentGroupId();

$stmt = $db->prepare('SELECT * FROM projects WHERE group_id = ? ORDER BY project_id DESC LIMIT 1');
$stmt->execute([$groupId]);
$project = $stmt->fetch();

if (!$project) {
    setFlash('error', 'Create a project first.');
    redirect('/student/projects/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/student/submissions/upload.php');
    }

    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['submission_type'] ?? 'document');

    if ($title === '' || empty($_FILES['file']['name'])) {
        setFlash('error', 'Title and file are required.');
        redirect('/student/submissions/upload.php');
    }

    $stored = handleUpload($_FILES['file'], UPLOAD_SUBMISSIONS);
    if (!$stored) {
        setFlash('error', 'File upload failed. Check file type/size (max 10MB).');
        redirect('/student/submissions/upload.php');
    }

    $stmt = $db->prepare('INSERT INTO submissions (project_id, submitted_by, title, file_path, submission_type, status)
        VALUES (?, ?, ?, ?, ?, "pending")');
    $stmt->execute([$project['project_id'], $user['user_id'], $title, $stored, $type]);

    setFlash('success', 'Submission uploaded successfully.');
    redirect('/student/submissions/index.php');
}

$pageTitle = 'Upload Submission';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>Upload Submission</h1></div></div>
<div class="card" style="max-width:600px">
    <form method="POST" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>
        <div class="form-group">
            <label for="title">Document Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="submission_type">Type</label>
            <select id="submission_type" name="submission_type">
                <option value="document">Document</option>
                <option value="chapter">Thesis Chapter</option>
                <option value="source_code">Source Code</option>
                <option value="presentation">Presentation</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="file">File</label>
            <input type="file" id="file" name="file" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="<?= BASE_URL ?>/student/submissions/index.php" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
