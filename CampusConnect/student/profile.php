<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/student/profile.php');
    }

    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $program = trim($_POST['program'] ?? '');

    $photo = $user['profile_photo'];
    if (!empty($_FILES['profile_photo']['name'])) {
        $stored = handleUpload($_FILES['profile_photo'], UPLOAD_PROFILE_PHOTOS, ALLOWED_IMAGE_TYPES);
        if ($stored) {
            $photo = $stored;
        } else {
            setFlash('error', 'Profile photo upload failed. Please check file type/size.');
        }
    }

    $stmt = $db->prepare('UPDATE users SET first_name=?, last_name=?, contact_number=?, program=?, profile_photo=? WHERE user_id=?');
    $stmt->execute([$first, $last, $contact, $program, $photo, $user['user_id']]);

    setFlash('success', 'Profile updated successfully.');
    redirect('/student/profile.php');
}

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <div><h1>My Profile</h1><p>Manage your personal information.</p></div>
</div>

<div class="card" style="max-width:640px">
    <form method="POST" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>
        <div class="form-grid">
            <div class="form-group form-span-2">
                <label>Student ID</label>
                <input type="text" value="<?= e($user['id_number']) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?= e($user['first_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?= e($user['last_name']) ?>" required>
            </div>
            <div class="form-group form-span-2">
                <label>Email</label>
                <input type="email" value="<?= e($user['email']) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="program">Program</label>
                <input type="text" id="program" name="program" value="<?= e($user['program']) ?>">
            </div>
            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" value="<?= e($user['contact_number']) ?>">
            </div>
            <div class="form-group form-span-2">
                <label for="profile_photo">Profile Photo</label>
                <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
