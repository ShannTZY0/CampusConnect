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

if (!$groupId) {
    setFlash('error', 'Join or create a group first.');
    redirect('/student/groups/index.php');
}

$stmt = $db->prepare('SELECT adviser_id FROM `groups` WHERE group_id = ?');
$stmt->execute([$groupId]);
$group = $stmt->fetch();

if (!$group['adviser_id']) {
    setFlash('error', 'Your group does not have an assigned adviser yet.');
    redirect('/student/advisers/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/student/consultations/request.php');
    }

    $preferredDate = $_POST['preferred_date'] ?? '';
    $mode = $_POST['mode'] ?? 'face_to_face';
    $agenda = trim($_POST['agenda'] ?? '');

    $stmt = $db->prepare('INSERT INTO consultations (group_id, adviser_id, requested_by, preferred_date, mode, agenda, status)
        VALUES (?, ?, ?, ?, ?, ?, "pending")');
    $stmt->execute([$groupId, $group['adviser_id'], $user['user_id'], $preferredDate ?: null, $mode, $agenda]);

    notifyUser((int) $group['adviser_id'], 'New Consultation Request', 'A group has requested a consultation.', '/adviser/consultations.php');

    setFlash('success', 'Consultation request sent.');
    redirect('/student/consultations/index.php');
}

$pageTitle = 'Request Consultation';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>Request Consultation</h1></div></div>
<div class="card" style="max-width:600px">
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <div class="form-group">
            <label for="preferred_date">Preferred Date/Time</label>
            <input type="datetime-local" id="preferred_date" name="preferred_date" required>
        </div>
        <div class="form-group">
            <label for="mode">Mode</label>
            <select id="mode" name="mode">
                <option value="face_to_face">Face-to-Face</option>
                <option value="online">Online</option>
            </select>
        </div>
        <div class="form-group">
            <label for="agenda">Agenda / Topics to Discuss</label>
            <textarea id="agenda" name="agenda" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Request</button>
        <a href="<?= BASE_URL ?>/student/consultations/index.php" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
