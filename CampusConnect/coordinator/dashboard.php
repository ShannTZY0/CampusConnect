<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

$stats = [
    'students'  => (int) $db->query('SELECT COUNT(*) FROM users WHERE role_id = ' . ROLE_STUDENT)->fetchColumn(),
    'advisers'  => (int) $db->query('SELECT COUNT(*) FROM users WHERE role_id = ' . ROLE_ADVISER)->fetchColumn(),
    'groups'    => (int) $db->query('SELECT COUNT(*) FROM `groups`')->fetchColumn(),
    'proposals_pending' => (int) $db->query("SELECT COUNT(*) FROM proposals WHERE status IN ('submitted','under_review')")->fetchColumn(),
    'projects_active'   => (int) $db->query("SELECT COUNT(*) FROM projects WHERE status = 'in_progress'")->fetchColumn(),
];

$recentAnnouncements = $db->query('SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5')->fetchAll();
$recentProposals = $db->query("SELECT p.*, g.group_name FROM proposals p JOIN `groups` g ON g.group_id = p.group_id ORDER BY p.created_at DESC LIMIT 5")->fetchAll();

$pageTitle = 'Coordinator Dashboard';
$extraCss = ['dashboard'];
$extraJs = ['dashboard'];
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Program Coordinator Dashboard</h1><p>System-wide overview of capstone activities.</p></div></div>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-value"><?= $stats['students'] ?></div><div class="stat-label">Students</div></div>
    <div class="stat-card"><div class="stat-value"><?= $stats['advisers'] ?></div><div class="stat-label">Advisers</div></div>
    <div class="stat-card"><div class="stat-value"><?= $stats['groups'] ?></div><div class="stat-label">Groups</div></div>
    <div class="stat-card"><div class="stat-value"><?= $stats['proposals_pending'] ?></div><div class="stat-label">Proposals Pending</div></div>
    <div class="stat-card"><div class="stat-value"><?= $stats['projects_active'] ?></div><div class="stat-label">Active Projects</div></div>
</div>

<div class="dash-grid">
    <div>
        <div class="card">
            <div class="card-header"><h3>Recent Proposals</h3></div>
            <?php if ($recentProposals): ?>
            <table>
                <thead><tr><th>Title</th><th>Group</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recentProposals as $p): ?>
                <tr><td><?= e($p['title']) ?></td><td><?= e($p['group_name']) ?></td><td><?= statusBadge($p['status']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?><p class="stat-label">No proposals yet.</p><?php endif; ?>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card-header"><h3>Recent Announcements</h3></div>
            <?php if ($recentAnnouncements): ?>
            <ul class="timeline">
                <?php foreach ($recentAnnouncements as $a): ?>
                <li><span><strong><?= e($a['title']) ?></strong></span><span class="time"><?= e(formatDate($a['created_at'], 'M d')) ?></span></li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?><p class="stat-label">No announcements yet.</p><?php endif; ?>
            <div style="margin-top:.75rem"><a href="<?= BASE_URL ?>/coordinator/announcements.php" class="btn btn-sm btn-outline">Post Announcement</a></div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Quick Links</h3></div>
            <div class="quick-links">
                <a href="<?= BASE_URL ?>/coordinator/adviser_assignments.php">🔗 Assign Advisers</a>
                <a href="<?= BASE_URL ?>/coordinator/proposals.php">📄 Proposals</a>
                <a href="<?= BASE_URL ?>/coordinator/reports.php">📑 Reports</a>
                <a href="<?= BASE_URL ?>/coordinator/milestones.php">🏁 Milestones</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
