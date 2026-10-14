<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_STUDENT;
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();
$groupId = currentStudentGroupId();

$project = null;
$proposal = null;
$upcomingConsultations = [];
$recentSubmissions = [];
$milestones = [];

if ($groupId) {
    $stmt = $db->prepare('SELECT * FROM projects WHERE group_id = ? ORDER BY project_id DESC LIMIT 1');
    $stmt->execute([$groupId]);
    $project = $stmt->fetch();

    $stmt = $db->prepare('SELECT * FROM proposals WHERE group_id = ? ORDER BY proposal_id DESC LIMIT 1');
    $stmt->execute([$groupId]);
    $proposal = $stmt->fetch();

    $stmt = $db->prepare("SELECT c.*, u.first_name, u.last_name FROM consultations c
        JOIN users u ON u.user_id = c.adviser_id
        WHERE c.group_id = ? AND c.status IN ('pending','approved')
        ORDER BY COALESCE(c.scheduled_date, c.preferred_date) ASC LIMIT 5");
    $stmt->execute([$groupId]);
    $upcomingConsultations = $stmt->fetchAll();

    if ($project) {
        $stmt = $db->prepare('SELECT * FROM submissions WHERE project_id = ? ORDER BY submitted_at DESC LIMIT 5');
        $stmt->execute([$project['project_id']]);
        $recentSubmissions = $stmt->fetchAll();

        $stmt = $db->prepare('SELECT * FROM project_milestones WHERE project_id = ? ORDER BY due_date ASC LIMIT 5');
        $stmt->execute([$project['project_id']]);
        $milestones = $stmt->fetchAll();
    }
}

$pageTitle = 'Student Dashboard';
$extraCss = ['dashboard'];
$extraJs = ['dashboard'];
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1>Welcome, <?= e($user['first_name']) ?> 👋</h1>
        <p>Here's what's happening with your capstone project.</p>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $project ? e(ucwords(str_replace('_',' ',$project['status']))) : '—' ?></div>
        <div class="stat-label">Project Status</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $project ? (int)$project['progress_percent'] . '%' : '0%' ?></div>
        <div class="stat-label">Progress</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $proposal ? e(ucwords(str_replace('_',' ',$proposal['status']))) : 'No Proposal' ?></div>
        <div class="stat-label">Proposal Status</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= count($upcomingConsultations) ?></div>
        <div class="stat-label">Upcoming Consultations</div>
    </div>
</div>

<?php if (!$groupId): ?>
<div class="card empty-state">
    <p>You are not part of any group yet.</p>
    <a class="btn btn-primary" href="<?= BASE_URL ?>/student/groups/create.php">Create a Group</a>
</div>
<?php else: ?>
<div class="dash-grid">
    <div>
        <div class="card">
            <div class="card-header"><h3>Project Progress</h3></div>
            <?php if ($project): ?>
                <p><strong><?= e($project['title']) ?></strong></p>
                <div class="progress-bar"><div class="progress-bar-fill" data-value="<?= (int)$project['progress_percent'] ?>%" style="width: <?= (int)$project['progress_percent'] ?>%"></div></div>
                <p style="margin-top:.5rem" class="stat-label"><?= (int)$project['progress_percent'] ?>% complete</p>
            <?php else: ?>
                <p class="stat-label">No active project yet. Submit a proposal to get started.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header"><h3>Upcoming Milestones</h3></div>
            <?php if ($milestones): ?>
                <table>
                    <thead><tr><th>Milestone</th><th>Due Date</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($milestones as $m): ?>
                        <tr>
                            <td><?= e($m['title']) ?></td>
                            <td><?= e(formatDate($m['due_date'], 'M d, Y')) ?></td>
                            <td><?= statusBadge($m['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="stat-label">No milestones set yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header"><h3>Upcoming Consultations</h3></div>
            <?php if ($upcomingConsultations): ?>
                <ul class="timeline">
                    <?php foreach ($upcomingConsultations as $c): ?>
                        <li>
                            <span>With <?= e($c['first_name'] . ' ' . $c['last_name']) ?><br><?= statusBadge($c['status']) ?></span>
                            <span class="time"><?= e(formatDate($c['scheduled_date'] ?? $c['preferred_date'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="stat-label">No upcoming consultations.</p>
            <?php endif; ?>
            <div style="margin-top:.75rem"><a href="<?= BASE_URL ?>/student/consultations/request.php" class="btn btn-sm btn-outline">Request Consultation</a></div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Quick Links</h3></div>
            <div class="quick-links">
                <a href="<?= BASE_URL ?>/student/proposals/index.php">📄 Proposals</a>
                <a href="<?= BASE_URL ?>/student/projects/index.php">📁 Projects</a>
                <a href="<?= BASE_URL ?>/student/submissions/upload.php">📤 Upload Submission</a>
                <a href="<?= BASE_URL ?>/student/advisers/index.php">🎓 Find Adviser</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
