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
        redirect('/admin/system_settings.php');
    }

    $settings = [
        'site_name' => trim($_POST['site_name'] ?? ''),
        'school_year' => trim($_POST['school_year'] ?? ''),
        'proposal_submission_open' => isset($_POST['proposal_submission_open']) ? '1' : '0',
        'max_group_size' => (string) max(1, (int) ($_POST['max_group_size'] ?? 5)),
    ];

    $stmt = $db->prepare('INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($settings as $key => $value) {
        $stmt->execute([$key, $value]);
    }

    logActivity((int) $admin['user_id'], 'update_settings', 'System settings updated');
    setFlash('success', 'Settings saved.');
    redirect('/admin/system_settings.php');
}

$rows = $db->query('SELECT * FROM system_settings')->fetchAll();
$settings = [];
foreach ($rows as $r) {
    $settings[$r['setting_key']] = $r['setting_value'];
}

$pageTitle = 'System Settings';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>System Settings</h1></div></div>
<div class="card" style="max-width:600px">
    <form method="POST" novalidate>
        <?= csrfField() ?>
        <div class="form-group">
            <label>Site Name</label>
            <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? APP_NAME) ?>">
        </div>
        <div class="form-group">
            <label>Current School Year</label>
            <input type="text" name="school_year" value="<?= e($settings['school_year'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Max Group Size</label>
            <input type="number" name="max_group_size" min="1" value="<?= e($settings['max_group_size'] ?? '5') ?>">
        </div>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="proposal_submission_open" <?= ($settings['proposal_submission_open'] ?? '1') === '1' ? 'checked' : '' ?>>
                Proposal Submission Window Open
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
