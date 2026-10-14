<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$requiredRole = ROLE_COORDINATOR;
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Reports';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><div><h1>Reports</h1><p>Generate program-wide reports.</p></div></div>

<div class="stat-grid">
    <div class="card">
        <h3>📁 Project Report</h3>
        <p class="stat-label">Status and progress of all capstone projects.</p>
        <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/reports/project_report.php">Generate</a>
    </div>
    <div class="card">
        <h3>🧑‍🏫 Adviser Report</h3>
        <p class="stat-label">Adviser workload and advisee performance.</p>
        <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/reports/adviser_report.php">Generate</a>
    </div>
    <div class="card">
        <h3>📅 Consultation Report</h3>
        <p class="stat-label">Consultation frequency and completion rates.</p>
        <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/reports/consultation_report.php">Generate</a>
    </div>
    <div class="card">
        <h3>📊 Evaluation Report</h3>
        <p class="stat-label">Aggregated evaluation scores by project.</p>
        <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/reports/evaluation_report.php">Generate</a>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
