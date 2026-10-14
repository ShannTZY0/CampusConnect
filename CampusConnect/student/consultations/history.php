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
        WHERE c.group_id = ? AND c.status IN ('completed','cancelled')
        ORDER BY c.created_at DESC");
    $stmt->execute([$groupId]);
    $consultations = $stmt->fetchAll();
}

$pageTitle = 'Consultation History';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><div><h1>Consultation History</h1></div></div>
<?php if (!$consultations): ?>
<div class="card empty-state"><p>No past consultations.</p></div>
<?php else: ?>
<div class="card">
    <table>
        <thead><tr><th>Adviser</th><th>Date</th><th>Status</th><th>Notes</th></tr></thead>
        <tbody>
        <?php foreach ($consultations as $c): ?>
            <tr>
                <td><?= e($c['first_name'] . ' ' . $c['last_name']) ?></td>
                <td><?= e(formatDate($c['scheduled_date'] ?? $c['preferred_date'])) ?></td>
                <td><?= statusBadge($c['status']) ?></td>
                <td><?= e($c['notes']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
