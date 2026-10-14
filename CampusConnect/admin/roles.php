<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_ADMIN;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$roles = $db->query('SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.role_id = r.role_id) AS user_count
    FROM roles r ORDER BY r.role_id')->fetchAll();

$pageTitle = 'Roles';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Roles</h1><p>System roles and permission levels.</p></div></div>

<div class="card">
    <table>
        <thead><tr><th>Role</th><th>Description</th><th>Users</th></tr></thead>
        <tbody>
        <?php
        $descriptions = [
            'student'     => 'Can manage their group, proposals, projects, and submissions.',
            'adviser'     => 'Supervises advisee groups, reviews proposals, tracks progress, gives feedback.',
            'coordinator' => 'Oversees the whole program: assigns advisers, manages announcements and reports.',
            'admin'       => 'Full system access: manages users, roles, and system settings.',
        ];
        foreach ($roles as $r): ?>
        <tr>
            <td><?= e(ucfirst($r['role_name'])) ?></td>
            <td><?= e($descriptions[$r['role_name']] ?? '') ?></td>
            <td><?= (int)$r['user_count'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="stat-label">Roles are fixed for system integrity. To change a user's role, use the Users page.</p>
<?php include __DIR__ . '/../includes/footer.php'; ?>
