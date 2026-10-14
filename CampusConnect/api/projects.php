<?php
/**
 * api/projects.php
 * GET -> role-scoped list of projects.
 *  - student  -> their group's project(s)
 *  - adviser  -> advisee projects
 *  - coordinator/admin -> all projects
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = getDB();
$roleId = currentRoleId();

$sql = "SELECT p.*, g.group_name FROM projects p JOIN `groups` g ON g.group_id = p.group_id WHERE 1=1";
$params = [];

if ($roleId === ROLE_STUDENT) {
    $groupId = currentStudentGroupId();
    $sql .= ' AND p.group_id = ?';
    $params[] = $groupId ?: 0;
} elseif ($roleId === ROLE_ADVISER) {
    $sql .= ' AND g.adviser_id = ?';
    $params[] = $_SESSION['user_id'];
}

$sql .= ' ORDER BY p.updated_at DESC LIMIT 100';

$stmt = $db->prepare($sql);
$stmt->execute($params);

echo json_encode(['projects' => $stmt->fetchAll()]);
