<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

// Auto-mark overdue milestones
$db->exec("UPDATE project_milestones SET status='overdue' WHERE status IN ('pending','in_progress') AND due_date IS NOT NULL AND due_date < CURDATE()");

$milestones = $db->query("SELECT m.*, p.title AS project_title, g.group_name
    FROM project_milestones m
    JOIN projects p ON p.project_id = m.project_id
    JOIN `groups` g ON g.group_id = p.group_id
    ORDER BY m.due_date ASC")->fetchAll();

$pageTitle = 'Milestones';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Milestones</h1><p>Program-wide milestone tracking.</p></div></div>
<div class="card">
    <table>
        <thead><tr><th>Group</th><th>Project</th><th>Milestone</th><th>Due Date</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($milestones as $m): ?>
        <tr>
            <td><?= e($m['group_name']) ?></td>
            <td><?= e($m['project_title']) ?></td>
            <td><?= e($m['title']) ?></td>
            <td><?= e(formatDate($m['due_date'], 'M d, Y')) ?></td>
            <td><?= statusBadge($m['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$milestones): ?><tr><td colspan="5" class="empty-state">No milestones set yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
