<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT p.*, g.group_name, u.first_name AS adviser_first, u.last_name AS adviser_last
        FROM projects p JOIN `groups` g ON g.group_id = p.group_id
        LEFT JOIN users u ON u.user_id = g.adviser_id";
$params = [];
if ($statusFilter !== '') {
    $sql .= " WHERE p.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY p.updated_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$pageTitle = 'Projects';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Projects</h1><p>All capstone projects across the program.</p></div></div>

<form method="GET" class="card" style="max-width:300px">
    <div class="form-group">
        <label for="status">Filter by Status</label>
        <select id="status" name="status" onchange="this.form.submit()">
            <option value="">All</option>
            <?php foreach (['proposed','in_progress','for_evaluation','completed','dropped'] as $s): ?>
            <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= e(ucwords(str_replace('_',' ',$s))) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<div class="card">
    <table>
        <thead><tr><th>Title</th><th>Group</th><th>Adviser</th><th>Status</th><th>Progress</th></tr></thead>
        <tbody>
        <?php foreach ($projects as $p): ?>
        <tr>
            <td><?= e($p['title']) ?></td>
            <td><?= e($p['group_name']) ?></td>
            <td><?= $p['adviser_first'] ? e($p['adviser_first'] . ' ' . $p['adviser_last']) : '—' ?></td>
            <td><?= statusBadge($p['status']) ?></td>
            <td><?= (int)$p['progress_percent'] ?>%</td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$projects): ?><tr><td colspan="5" class="empty-state">No projects found.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
