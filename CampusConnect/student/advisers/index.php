<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../../includes/auth_check.php';

$db = getDB();
$stmt = $db->prepare('SELECT u.*,
    (SELECT COUNT(*) FROM `groups` g WHERE g.adviser_id = u.user_id) AS advisee_count
    FROM users u WHERE u.role_id = ? AND u.status = "active" ORDER BY u.last_name');
$stmt->execute([ROLE_ADVISER]);
$advisers = $stmt->fetchAll();

$pageTitle = 'Advisers';
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div><h1>Advisers</h1><p>Browse available advisers and submit your preferences.</p></div>
    <a href="<?= BASE_URL ?>/student/advisers/preference.php" class="btn btn-primary">Submit Preferences</a>
</div>

<div class="card">
    <table>
        <thead><tr><th>Name</th><th>Department</th><th>Current Advisees</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($advisers as $a): ?>
            <tr>
                <td><?= e($a['first_name'] . ' ' . $a['last_name']) ?></td>
                <td><?= e($a['department']) ?></td>
                <td><?= (int) $a['advisee_count'] ?> group(s)</td>
                <td><a href="<?= BASE_URL ?>/student/advisers/profile.php?id=<?= (int)$a['user_id'] ?>">View Profile</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
