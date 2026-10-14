<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_ADMIN;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$total = (int) $db->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn();

$stmt = $db->prepare("SELECT l.*, u.first_name, u.last_name FROM activity_logs l
    LEFT JOIN users u ON u.user_id = l.user_id
    ORDER BY l.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute();
$logs = $stmt->fetchAll();

$totalPages = max(1, (int) ceil($total / $perPage));

$pageTitle = 'Activity Logs';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Activity Logs</h1><p>System-wide audit trail.</p></div></div>
<div class="card">
    <table>
        <thead><tr><th>User</th><th>Action</th><th>Details</th><th>IP</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $l): ?>
        <tr>
            <td><?= $l['first_name'] ? e($l['first_name'] . ' ' . $l['last_name']) : 'System' ?></td>
            <td><?= e($l['action']) ?></td>
            <td><?= e($l['details']) ?></td>
            <td><?= e($l['ip_address']) ?></td>
            <td><?= e(formatDate($l['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$logs): ?><tr><td colspan="5" class="empty-state">No activity recorded.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php if ($totalPages > 1): ?>
<div style="display:flex; gap:.5rem; margin-top:1rem;">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline' ?>" href="?page=<?= $p ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
