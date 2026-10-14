<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_ADMIN;
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();
$admin = currentUser();
$backupDir = ROOT_PATH . '/database';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_backup') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        redirect('/admin/backups.php');
    }

    // Simple logical backup: dump each table's rows as INSERT statements.
    // For large production databases, prefer running `mysqldump` via a scheduled
    // shell task instead of this in-app export.
    try {
        $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $sql = "-- CampusConnect backup generated " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $rows = $db->query("SELECT * FROM `$table`")->fetchAll();
            if (!$rows) {
                continue;
            }
            $columns = array_keys($rows[0]);
            $colList = '`' . implode('`,`', $columns) . '`';
            $sql .= "-- Table: $table\n";
            foreach ($rows as $row) {
                $values = array_map(function ($v) use ($db) {
                    return $v === null ? 'NULL' : $db->quote((string) $v);
                }, $row);
                $sql .= "INSERT INTO `$table` ($colList) VALUES (" . implode(',', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $filename = 'database_backup_' . date('Ymd_His') . '.sql';
        file_put_contents($backupDir . '/' . $filename, $sql);

        logActivity((int) $admin['user_id'], 'create_backup', "Generated backup: $filename");
        setFlash('success', "Backup created: $filename");
    } catch (Exception $e) {
        setFlash('error', 'Backup failed: ' . $e->getMessage());
    }
    redirect('/admin/backups.php');
}

$backups = glob($backupDir . '/database_backup_*.sql') ?: [];
rsort($backups);

$pageTitle = 'Backups';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <div><h1>Backups</h1><p>Generate and download database backups.</p></div>
    <form method="POST" data-confirm="Generate a new backup now?">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="create_backup">
        <button type="submit" class="btn btn-primary">+ Generate Backup</button>
    </form>
</div>

<div class="card">
    <?php if (!$backups): ?>
    <p class="stat-label">No backups generated yet.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Filename</th><th>Size</th><th>Created</th></tr></thead>
        <tbody>
        <?php foreach ($backups as $b): ?>
        <tr>
            <td><?= e(basename($b)) ?></td>
            <td><?= number_format(filesize($b) / 1024, 1) ?> KB</td>
            <td><?= e(date('M d, Y g:i A', filemtime($b))) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="stat-label" style="margin-top:.75rem">Backup files are stored in the <code>/database</code> directory on the server. Download them via your hosting file manager or FTP client.</p>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
