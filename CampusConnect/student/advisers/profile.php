<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM users WHERE user_id = ? AND role_id = ?');
$stmt->execute([$id, ROLE_ADVISER]);
$adviser = $stmt->fetch();

if (!$adviser) {
    setFlash('error', 'Adviser not found.');
    redirect('/student/advisers/index.php');
}

$stmt = $db->prepare('SELECT group_name FROM `groups` WHERE adviser_id = ?');
$stmt->execute([$id]);
$advisees = $stmt->fetchAll();

$pageTitle = e($adviser['first_name'] . ' ' . $adviser['last_name']);
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e($adviser['first_name'] . ' ' . $adviser['last_name']) ?></h1>
        <p><?= e($adviser['department']) ?></p>
    </div>
</div>
<div class="card">
    <h3>Contact</h3>
    <p>Email: <?= e($adviser['email']) ?></p>
    <p>Contact Number: <?= e($adviser['contact_number'] ?: '—') ?></p>
</div>
<div class="card">
    <h3>Current Advisee Groups (<?= count($advisees) ?>)</h3>
    <?php if ($advisees): ?>
    <ul>
        <?php foreach ($advisees as $g): ?>
        <li><?= e($g['group_name']) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p class="stat-label">No advisee groups yet.</p>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
