<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = [ROLE_COORDINATOR, ROLE_ADMIN];
require_once __DIR__ . '/../includes/auth_check.php';

$db = getDB();

$byProject = $db->query("SELECT p.title, g.group_name, COUNT(e.evaluation_id) AS eval_count, AVG(e.score) AS avg_score
    FROM projects p
    JOIN `groups` g ON g.group_id = p.group_id
    LEFT JOIN evaluations e ON e.project_id = p.project_id
    GROUP BY p.project_id ORDER BY avg_score DESC")->fetchAll();

$pageTitle = 'Evaluation Report';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Evaluation Report</h1><p>Aggregated evaluation scores by project.</p></div></div>

<div class="card">
    <table>
        <thead><tr><th>Project</th><th>Group</th><th># Evaluations</th><th>Average Score</th></tr></thead>
        <tbody>
        <?php foreach ($byProject as $p): ?>
        <tr>
            <td><?= e($p['title']) ?></td>
            <td><?= e($p['group_name']) ?></td>
            <td><?= (int)$p['eval_count'] ?></td>
            <td><?= $p['avg_score'] !== null ? number_format((float)$p['avg_score'], 2) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$byProject): ?><tr><td colspan="4" class="empty-state">No evaluation data available.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<button onclick="window.print()" class="btn btn-outline">🖨️ Print Report</button>
<?php include __DIR__ . '/../includes/footer.php'; ?>
