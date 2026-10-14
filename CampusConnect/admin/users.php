<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_ADMIN;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$admin = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/admin/users.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $first = trim($_POST['first_name'] ?? '');
        $last  = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roleId = (int) ($_POST['role_id'] ?? ROLE_STUDENT);

        if ($first === '' || $last === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Please fill out all required fields correctly.');
            redirect('/admin/users.php');
        }
        $check = $db->prepare('SELECT user_id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            setFlash('error', 'A user with that email already exists.');
            redirect('/admin/users.php');
        }

        $tempPassword = substr(bin2hex(random_bytes(6)), 0, 10);
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (role_id, first_name, last_name, email, password_hash, status) VALUES (?, ?, ?, ?, ?, "active")');
        $stmt->execute([$roleId, $first, $last, $email, $hash]);

        logActivity((int) $admin['user_id'], 'create_user', "Created user $email");
        setFlash('success', "User created. Temporary password: $tempPassword");
    } elseif ($action === 'update_status') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        if ($userId !== (int) $admin['user_id']) {
            $db->prepare('UPDATE users SET status = ? WHERE user_id = ?')->execute([$status, $userId]);
            logActivity((int) $admin['user_id'], 'update_user_status', "User #$userId set to $status");
            setFlash('success', 'User status updated.');
        } else {
            setFlash('error', 'You cannot change your own status.');
        }
    } elseif ($action === 'update_role') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $roleId = (int) ($_POST['role_id'] ?? 0);
        if ($userId !== (int) $admin['user_id']) {
            $db->prepare('UPDATE users SET role_id = ? WHERE user_id = ?')->execute([$roleId, $userId]);
            logActivity((int) $admin['user_id'], 'update_user_role', "User #$userId role changed");
            setFlash('success', 'User role updated.');
        } else {
            setFlash('error', 'You cannot change your own role.');
        }
    }
    redirect('/admin/users.php');
}

$search = trim($_GET['q'] ?? '');
$sql = 'SELECT u.*, r.role_name FROM users u JOIN roles r ON r.role_id = u.role_id';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?';
    $like = "%$search%";
    $params = [$like, $like, $like];
}
$sql .= ' ORDER BY u.created_at DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$roles = $db->query('SELECT * FROM roles ORDER BY role_id')->fetchAll();

$pageTitle = 'Users';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Users</h1><p>Manage all system accounts.</p></div></div>

<form method="GET" class="card" style="max-width:400px">
    <div class="form-group"><label>Search</label><input type="text" name="q" value="<?= e($search) ?>" placeholder="Name or email"></div>
    <button type="submit" class="btn btn-sm btn-primary">Search</button>
</form>

<div class="card">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= e($u['first_name'] . ' ' . $u['last_name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td>
                <form method="POST" style="display:inline-flex; gap:.3rem;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                    <select name="role_id" onchange="this.form.submit()" <?= $u['user_id'] == $admin['user_id'] ? 'disabled' : '' ?>>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= (int)$r['role_id'] ?>" <?= $u['role_id'] == $r['role_id'] ? 'selected' : '' ?>><?= e(ucfirst($r['role_name'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </td>
            <td><?= statusBadge($u['status']) ?></td>
            <td>
                <form method="POST" style="display:inline-flex; gap:.3rem;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                    <select name="status" onchange="this.form.submit()" <?= $u['user_id'] == $admin['user_id'] ? 'disabled' : '' ?>>
                        <option value="active" <?= $u['status']==='active'?'selected':'' ?>>Active</option>
                        <option value="inactive" <?= $u['status']==='inactive'?'selected':'' ?>>Inactive</option>
                        <option value="suspended" <?= $u['status']==='suspended'?'selected':'' ?>>Suspended</option>
                    </select>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$users): ?><tr><td colspan="5" class="empty-state">No users found.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card" style="max-width:600px">
    <div class="card-header"><h3>Create New User</h3></div>
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
            <div class="form-group form-span-2"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group form-span-2">
                <label>Role</label>
                <select name="role_id">
                    <?php foreach ($roles as $r): ?>
                    <option value="<?= (int)$r['role_id'] ?>"><?= e(ucfirst($r['role_name'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Create User</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
