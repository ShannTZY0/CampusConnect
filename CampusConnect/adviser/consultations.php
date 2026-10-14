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
        redirect('/adviser/consultations.php');
    }

    $id = (int) ($_POST['consultation_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $scheduledDate = $_POST['scheduled_date'] ?? null;
    $notes = trim($_POST['notes'] ?? '');

    $stmt = $db->prepare('SELECT * FROM consultations WHERE consultation_id = ? AND adviser_id = ?');
    $stmt->execute([$id, $user['user_id']]);
    $consult = $stmt->fetch();

    if ($consult) {
        $statusMap = ['approve' => 'approved', 'reschedule' => 'rescheduled', 'complete' => 'completed', 'cancel' => 'cancelled'];
        if (isset($statusMap[$action])) {
            $upd = $db->prepare('UPDATE consultations SET status=?, scheduled_date=?, notes=? WHERE consultation_id=?');
            $upd->execute([$statusMap[$action], $scheduledDate ?: $consult['scheduled_date'], $notes ?: $consult['notes'], $id]);
            notifyUser((int) $consult['requested_by'], 'Consultation ' . ucfirst($statusMap[$action]), 'Your consultation request was updated.', '/student/consultations/index.php');
            setFlash('success', 'Consultation updated.');
        }
    }
    redirect('/adviser/consultations.php');
}

$stmt = $db->prepare("SELECT c.*, g.group_name FROM consultations c
    JOIN `groups` g ON g.group_id = c.group_id
    WHERE c.adviser_id = ? ORDER BY FIELD(c.status,'pending','approved','rescheduled','completed','cancelled'), c.preferred_date ASC");
$stmt->execute([$user['user_id']]);
$consultations = $stmt->fetchAll();

$pageTitle = 'Consultations';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Consultations</h1><p>Approve, reschedule, or complete consultation requests.</p></div></div>
<?php if (!$consultations): ?>
<div class="card empty-state"><p>No consultation requests.</p></div>
<?php else: ?>
<?php foreach ($consultations as $c): ?>
<div class="card">
    <div class="card-header">
        <div><h3><?= e($c['group_name']) ?></h3><p class="stat-label"><?= statusBadge($c['status']) ?> · Preferred: <?= e(formatDate($c['preferred_date'])) ?></p></div>
    </div>
    <p><strong>Agenda:</strong> <?= nl2br(e($c['agenda'])) ?></p>
    <?php if (in_array($c['status'], ['pending', 'approved'], true)): ?>
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="consultation_id" value="<?= (int)$c['consultation_id'] ?>">
        <div class="form-grid">
            <div class="form-group"><label>Scheduled Date/Time</label><input type="datetime-local" name="scheduled_date"></div>
            <div class="form-group"><label>Notes</label><input type="text" name="notes"></div>
        </div>
        <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
        <button type="submit" name="action" value="reschedule" class="btn btn-outline">Reschedule</button>
        <button type="submit" name="action" value="complete" class="btn btn-primary">Mark Completed</button>
        <button type="submit" name="action" value="cancel" class="btn btn-danger">Cancel</button>
    </form>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
