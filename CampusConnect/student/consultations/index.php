<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$groupId = currentStudentGroupId();
$consultations = [];
if ($groupId) {
    $stmt = getDB()->prepare("SELECT c.*, u.first_name, u.last_name FROM consultations c
        JOIN users u ON u.user_id = c.adviser_id
        WHERE c.group_id = ? ORDER BY c.created_at DESC");
    $stmt->execute([$groupId]);
    $consultations = $stmt->fetchAll();
}

$pageTitle = 'Consultations';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div><h1>Consultations</h1><p>Request and track consultations with your adviser.</p></div>
    <?php if ($groupId): ?>
    <a href="<?= BASE_URL ?>/student/consultations/request.php" class="btn btn-primary">+ Request Consultation</a>
    <?php endif; ?>
</div>

<?php if (!$groupId): ?>
<div class="card empty-state"><p>Join or create a group first.</p></div>
<?php elseif (!$consultations): ?>
<div class="card empty-state"><p>No consultations requested yet.</p></div>
<?php else: ?>
<div class="card">
    <table>
        <thead><tr><th>Adviser</th><th>Preferred Date</th><th>Scheduled</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($consultations as $c): ?>
            <tr>
                <td><?= e($c['first_name'] . ' ' . $c['last_name']) ?></td>
                <td><?= e(formatDate($c['preferred_date'])) ?></td>
                <td><?= e(formatDate($c['scheduled_date'])) ?></td>
                <td><?= statusBadge($c['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<p><a href="<?= BASE_URL ?>/student/consultations/history.php">View full history →</a></p>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
