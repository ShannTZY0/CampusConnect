<?php
/**
 * includes/sidebar.php
 * Renders navigation links based on the current user's role.
 */
$roleName   = currentRoleName();
$currentUri = $_SERVER['REQUEST_URI'] ?? '';

function navLink(string $href, string $label, string $icon, string $currentUri): string
{
    $active = (strpos($currentUri, $href) !== false) ? ' active' : '';
    return '<a class="sidebar-link' . $active . '" href="' . BASE_URL . e($href) . '">'
        . '<span class="icon">' . $icon . '</span><span class="label">' . e($label) . '</span></a>';
}

$menus = [
    'student' => [
        ['/student/dashboard.php', 'Dashboard', '🏠'],
        ['/student/profile.php', 'My Profile', '👤'],
        ['/student/groups/index.php', 'My Group', '👥'],
        ['/student/proposals/index.php', 'Proposals', '📄'],
        ['/student/projects/index.php', 'Projects', '📁'],
        ['/student/advisers/index.php', 'Advisers', '🎓'],
        ['/student/consultations/index.php', 'Consultations', '📅'],
        ['/student/submissions/index.php', 'Submissions', '📤'],
        ['/student/evaluations/index.php', 'Evaluations', '📊'],
        ['/student/notifications/index.php', 'Notifications', '🔔'],
    ],
    'adviser' => [
        ['/adviser/dashboard.php', 'Dashboard', '🏠'],
        ['/adviser/profile.php', 'My Profile', '👤'],
        ['/adviser/projects.php', 'Advisee Projects', '📁'],
        ['/adviser/proposals.php', 'Proposals', '📄'],
        ['/adviser/consultations.php', 'Consultations', '📅'],
        ['/adviser/feedback.php', 'Feedback', '💬'],
        ['/adviser/progress.php', 'Progress Tracking', '📈'],
        ['/adviser/evaluations.php', 'Evaluations', '📊'],
        ['/adviser/notifications.php', 'Notifications', '🔔'],
    ],
    'coordinator' => [
        ['/coordinator/dashboard.php', 'Dashboard', '🏠'],
        ['/coordinator/students.php', 'Students', '🎓'],
        ['/coordinator/groups.php', 'Groups', '👥'],
        ['/coordinator/projects.php', 'Projects', '📁'],
        ['/coordinator/proposals.php', 'Proposals', '📄'],
        ['/coordinator/advisers.php', 'Advisers', '🧑‍🏫'],
        ['/coordinator/adviser_preferences.php', 'Adviser Preferences', '📝'],
        ['/coordinator/adviser_assignments.php', 'Adviser Assignments', '🔗'],
        ['/coordinator/consultations.php', 'Consultations', '📅'],
        ['/coordinator/milestones.php', 'Milestones', '🏁'],
        ['/coordinator/evaluations.php', 'Evaluations', '📊'],
        ['/coordinator/announcements.php', 'Announcements', '📢'],
        ['/coordinator/reports.php', 'Reports', '📑'],
        ['/coordinator/notifications.php', 'Notifications', '🔔'],
    ],
    'admin' => [
        ['/admin/dashboard.php', 'Dashboard', '🏠'],
        ['/admin/users.php', 'Users', '👤'],
        ['/admin/roles.php', 'Roles', '🛡️'],
        ['/admin/system_settings.php', 'System Settings', '⚙️'],
        ['/admin/activity_logs.php', 'Activity Logs', '🗒️'],
        ['/admin/backups.php', 'Backups', '💾'],
    ],
];

$items = $menus[$roleName] ?? [];
?>
<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <?php foreach ($items as [$href, $label, $icon]): ?>
            <?= navLink($href, $label, $icon, $currentUri) ?>
        <?php endforeach; ?>
    </nav>
</aside>
