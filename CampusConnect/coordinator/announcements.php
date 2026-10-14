<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/coordinator/announcements.php');
    }

    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $audience = $_POST['audience'] ?? 'all';

    if ($title === '' || $body === '') {
        setFlash('error', 'Title and message are required.');
        redirect('/coordinator/announcements.php');
    }

    $stmt = $db->prepare('INSERT INTO announcements (posted_by, title, body, audience) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user['user_id'], $title, $body, $audience]);

    // Notify relevant users
    $roleMap = ['students' => ROLE_STUDENT, 'advisers' => ROLE_ADVISER, 'coordinators' => ROLE_COORDINATOR];
    if ($audience === 'all') {
        $targets = $db->query('SELECT user_id FROM users WHERE role_id IN (' . ROLE_STUDENT . ',' . ROLE_ADVISER . ')')->fetchAll();
    } else {
        $targets = $db->prepare('SELECT user_id FROM users WHERE role_id = ?');
        $targets->execute([$roleMap[$audience] ?? ROLE_STUDENT]);
        $targets = $targets->fetchAll();
    }
    foreach ($targets as $t) {
        notifyUser((int) $t['user_id'], 'Announcement: ' . $title, mb_strimwidth($body, 0, 140, '…'));
    }

    setFlash('success', 'Announcement posted.');
    redirect('/coordinator/announcements.php');
}

$announcements = $db->query('SELECT * FROM announcements ORDER BY created_at DESC LIMIT 30')->fetchAll();

$pageTitle = 'Announcements';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Announcements</h1><p>Broadcast messages to students and advisers.</p></div></div>

<div class="card" style="max-width:700px">
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
        <div class="form-group"><label>Message</label><textarea name="body" rows="4" required></textarea></div>
        <div class="form-group">
            <label>Audience</label>
            <select name="audience">
                <option value="all">Everyone</option>
                <option value="students">Students Only</option>
                <option value="advisers">Advisers Only</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Post Announcement</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><h3>Past Announcements</h3></div>
    <?php if ($announcements): ?>
    <ul class="timeline">
        <?php foreach ($announcements as $a): ?>
        <li><span><strong><?= e($a['title']) ?></strong> (<?= e(ucfirst($a['audience'])) ?>)<br><?= nl2br(e($a['body'])) ?></span><span class="time"><?= e(formatDate($a['created_at'])) ?></span></li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p class="stat-label">No announcements posted yet.</p>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
