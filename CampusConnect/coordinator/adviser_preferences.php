<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

$rows = $db->query("SELECT ap.*, g.group_name, u.first_name AS adviser_first, u.last_name AS adviser_last
    FROM adviser_preferences ap
    JOIN `groups` g ON g.group_id = ap.group_id
    JOIN users u ON u.user_id = ap.adviser_id
    ORDER BY g.group_name, ap.preference_rank")->fetchAll();

$byGroup = [];
foreach ($rows as $r) {
    $byGroup[$r['group_name']][] = $r;
}

$pageTitle = 'Adviser Preferences';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Adviser Preferences</h1><p>Preferences submitted by each group's leader.</p></div></div>

<?php if (!$byGroup): ?>
<div class="card empty-state"><p>No preferences submitted yet.</p></div>
<?php else: ?>
<?php foreach ($byGroup as $groupName => $prefs): ?>
<div class="card">
    <div class="card-header"><h3><?= e($groupName) ?></h3></div>
    <table>
        <thead><tr><th>Rank</th><th>Adviser</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($prefs as $p): ?>
        <tr>
            <td>#<?= (int)$p['preference_rank'] ?></td>
            <td><?= e($p['adviser_first'] . ' ' . $p['adviser_last']) ?></td>
            <td><?= statusBadge($p['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>
<?php endif; ?>
<p><a href="<?= BASE_URL ?>/coordinator/adviser_assignments.php">Go to Adviser Assignments →</a></p>
<?php include __DIR__ . '/../includes/footer.php'; ?>
