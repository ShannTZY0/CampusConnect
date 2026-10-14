<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/coordinator/advisers.php');
    }

    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $idNumber = trim($_POST['id_number'] ?? '');

    if ($first === '' || $last === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Please fill out all required fields correctly.');
        redirect('/coordinator/advisers.php');
    }

    $check = $db->prepare('SELECT user_id FROM users WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        setFlash('error', 'An account with that email already exists.');
        redirect('/coordinator/advisers.php');
    }

    $tempPassword = substr(bin2hex(random_bytes(6)), 0, 10);
    $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

    $stmt = $db->prepare('INSERT INTO users (role_id, id_number, first_name, last_name, email, password_hash, department, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, "active")');
    $stmt->execute([ROLE_ADVISER, $idNumber, $first, $last, $email, $hash, $department]);

    setFlash('success', "Adviser account created. Temporary password: $tempPassword (share this securely).");
    redirect('/coordinator/advisers.php');
}

$advisers = $db->query('SELECT u.*, (SELECT COUNT(*) FROM `groups` g WHERE g.adviser_id = u.user_id) AS advisee_count
    FROM users u WHERE u.role_id = ' . ROLE_ADVISER . ' ORDER BY u.last_name')->fetchAll();

$pageTitle = 'Advisers';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Advisers</h1><p>Manage adviser accounts.</p></div></div>

<div class="card">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Advisee Groups</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($advisers as $a): ?>
        <tr>
            <td><?= e($a['first_name'] . ' ' . $a['last_name']) ?></td>
            <td><?= e($a['email']) ?></td>
            <td><?= e($a['department']) ?></td>
            <td><?= (int)$a['advisee_count'] ?></td>
            <td><?= statusBadge($a['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$advisers): ?><tr><td colspan="5" class="empty-state">No advisers yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card" style="max-width:600px">
    <div class="card-header"><h3>Add New Adviser</h3></div>
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <div class="form-grid">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
            <div class="form-group form-span-2"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Department</label><input type="text" name="department"></div>
            <div class="form-group"><label>ID Number</label><input type="text" name="id_number"></div>
        </div>
        <button type="submit" class="btn btn-primary">Create Adviser Account</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
