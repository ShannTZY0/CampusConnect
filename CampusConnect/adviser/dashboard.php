<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_ADVISER;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$user = currentUser();

$stmt = $db->prepare('SELECT COUNT(*) FROM `groups` WHERE adviser_id = ?');
$stmt->execute([$user['user_id']]);
$groupCount = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM consultations WHERE adviser_id = ? AND status = 'pending'");
$stmt->execute([$user['user_id']]);
$pendingConsults = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT p.* FROM proposals p JOIN `groups` g ON g.group_id = p.group_id
    WHERE g.adviser_id = ? AND p.status IN ('submitted','under_review') ORDER BY p.submitted_at ASC LIMIT 5");
$stmt->execute([$user['user_id']]);
$pendingProposals = $stmt->fetchAll();

$stmt = $db->prepare("SELECT pr.*, g.group_name FROM projects pr JOIN `groups` g ON g.group_id = pr.group_id
    WHERE g.adviser_id = ? ORDER BY pr.updated_at DESC LIMIT 5");
$stmt->execute([$user['user_id']]);
$recentProjects = $stmt->fetchAll();

$stmt = $db->prepare("SELECT c.*, g.group_name FROM consultations c JOIN `groups` g ON g.group_id = c.group_id
    WHERE c.adviser_id = ? AND c.status = 'pending' ORDER BY c.preferred_date ASC LIMIT 5");
$stmt->execute([$user['user_id']]);
$pendingConsultList = $stmt->fetchAll();

$pageTitle = 'Adviser Dashboard';
$extraCss = ['dashboard'];
$extraJs = ['dashboard'];
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Welcome, <?= e($user['first_name']) ?> 👋</h1><p>Overview of your advisee groups.</p></div></div>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-value"><?= $groupCount ?></div><div class="stat-label">Advisee Groups</div></div>
    <div class="stat-card"><div class="stat-value"><?= count($pendingProposals) ?></div><div class="stat-label">Proposals to Review</div></div>
    <div class="stat-card"><div class="stat-value"><?= $pendingConsults ?></div><div class="stat-label">Pending Consultations</div></div>
</div>

<div class="dash-grid">
    <div>
        <div class="card">
            <div class="card-header"><h3>Recent Projects</h3></div>
            <?php if ($recentProjects): ?>
            <table>
                <thead><tr><th>Group</th><th>Project</th><th>Status</th><th>Progress</th></tr></thead>
                <tbody>
                <?php foreach ($recentProjects as $p): ?>
                <tr><td><?= e($p['group_name']) ?></td><td><?= e($p['title']) ?></td><td><?= statusBadge($p['status']) ?></td><td><?= (int)$p['progress_percent'] ?>%</td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="stat-label">No projects yet.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header"><h3>Proposals Awaiting Review</h3></div>
            <?php if ($pendingProposals): ?>
            <table>
                <thead><tr><th>Title</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($pendingProposals as $p): ?>
                <tr><td><?= e($p['title']) ?></td><td><?= statusBadge($p['status']) ?></td>
                <td><a href="<?= BASE_URL ?>/adviser/proposals.php">Review</a></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="stat-label">Nothing to review right now.</p>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header"><h3>Pending Consultation Requests</h3></div>
            <?php if ($pendingConsultList): ?>
            <ul class="timeline">
                <?php foreach ($pendingConsultList as $c): ?>
                <li><span><?= e($c['group_name']) ?></span><span class="time"><?= e(formatDate($c['preferred_date'])) ?></span></li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="stat-label">No pending requests.</p>
            <?php endif; ?>
            <div style="margin-top:.75rem"><a href="<?= BASE_URL ?>/adviser/consultations.php" class="btn btn-sm btn-outline">Manage Consultations</a></div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Quick Links</h3></div>
            <div class="quick-links">
                <a href="<?= BASE_URL ?>/adviser/projects.php">📁 Projects</a>
                <a href="<?= BASE_URL ?>/adviser/proposals.php">📄 Proposals</a>
                <a href="<?= BASE_URL ?>/adviser/feedback.php">💬 Feedback</a>
                <a href="<?= BASE_URL ?>/adviser/evaluations.php">📊 Evaluations</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
