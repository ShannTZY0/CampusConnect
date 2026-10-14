<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$groupId = currentStudentGroupId();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT c.*, u.first_name, u.last_name FROM consultations c
    JOIN users u ON u.user_id = c.adviser_id WHERE c.consultation_id = ? AND c.group_id = ?");
$stmt->execute([$id, $groupId]);
$consultation = $stmt->fetch();

if (!$consultation) {
    setFlash('error', 'Consultation not found.');
    redirect('/student/consultations/index.php');
}

$pageTitle = 'Consultation Details';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>Consultation with <?= e($consultation['first_name'] . ' ' . $consultation['last_name']) ?></h1><p><?= statusBadge($consultation['status']) ?></p></div></div>
<div class="card">
    <p><strong>Preferred Date:</strong> <?= e(formatDate($consultation['preferred_date'])) ?></p>
    <p><strong>Scheduled Date:</strong> <?= e(formatDate($consultation['scheduled_date'])) ?></p>
    <p><strong>Mode:</strong> <?= e(ucfirst(str_replace('_', ' ', $consultation['mode']))) ?></p>
    <p><strong>Agenda:</strong><br><?= nl2br(e($consultation['agenda'])) ?></p>
    <?php if ($consultation['notes']): ?>
    <p><strong>Adviser Notes:</strong><br><?= nl2br(e($consultation['notes'])) ?></p>
    <?php endif; ?>
</div>
<a href="<?= BASE_URL ?>/student/consultations/index.php" class="btn btn-outline">Back</a>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
