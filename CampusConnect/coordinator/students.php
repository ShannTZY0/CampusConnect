<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$search = trim($_GET['q'] ?? '');

$sql = "SELECT u.*, gm.group_id FROM users u
        LEFT JOIN group_members gm ON gm.user_id = u.user_id
        WHERE u.role_id = ?";
$params = [ROLE_STUDENT];
if ($search !== '') {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.id_number LIKE ?)";
    $like = "%$search%";
    array_push($params, $like, $like, $like, $like);
}
$sql .= " ORDER BY u.last_name";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$pageTitle = 'Students';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Students</h1><p>All registered students.</p></div></div>

<form method="GET" class="card" style="max-width:400px">
    <div class="form-group">
        <label for="q">Search</label>
        <input type="text" id="q" name="q" value="<?= e($search) ?>" placeholder="Name, email, or ID number">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
</form>

<div class="card">
    <table>
        <thead><tr><th>ID Number</th><th>Name</th><th>Email</th><th>Program</th><th>Group Status</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($students as $s): ?>
            <tr>
                <td><?= e($s['id_number']) ?></td>
                <td><?= e($s['first_name'] . ' ' . $s['last_name']) ?></td>
                <td><?= e($s['email']) ?></td>
                <td><?= e($s['program']) ?></td>
                <td><?= $s['group_id'] ? 'In a group' : '<span class="stat-label">No group</span>' ?></td>
                <td><?= statusBadge($s['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$students): ?>
            <tr><td colspan="6" class="empty-state">No students found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
