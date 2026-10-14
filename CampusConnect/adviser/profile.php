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
        redirect('/adviser/profile.php');
    }

    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $department = trim($_POST['department'] ?? '');

    $photo = $user['profile_photo'];
    if (!empty($_FILES['profile_photo']['name'])) {
        $stored = handleUpload($_FILES['profile_photo'], UPLOAD_PROFILE_PHOTOS, ALLOWED_IMAGE_TYPES);
        if ($stored) { $photo = $stored; }
    }

    $stmt = $db->prepare('UPDATE users SET first_name=?, last_name=?, contact_number=?, department=?, profile_photo=? WHERE user_id=?');
    $stmt->execute([$first, $last, $contact, $department, $photo, $user['user_id']]);

    setFlash('success', 'Profile updated.');
    redirect('/adviser/profile.php');
}

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>My Profile</h1></div></div>
<div class="card" style="max-width:640px">
    <form method="POST" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>
        <div class="form-grid">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?= e($user['first_name']) ?>" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?= e($user['last_name']) ?>" required></div>
            <div class="form-group form-span-2"><label>Email</label><input type="email" value="<?= e($user['email']) ?>" disabled></div>
            <div class="form-group"><label>Department</label><input type="text" name="department" value="<?= e($user['department']) ?>"></div>
            <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number" value="<?= e($user['contact_number']) ?>"></div>
            <div class="form-group form-span-2"><label>Profile Photo</label><input type="file" name="profile_photo" accept="image/*"></div>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
