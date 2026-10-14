<?php
/**
 * api/consultations.php
 * GET ?status=pending|approved|... (optional filter)
 * Returns consultations scoped to the current user's role:
 *  - student  -> consultations for their group
 *  - adviser  -> consultations where they are the adviser
 *  - coordinator/admin -> all consultations
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
$statusFilter = $_GET['status'] ?? null;

$sql = "SELECT c.*, g.group_name, u.first_name AS adviser_first, u.last_name AS adviser_last
        FROM consultations c
        JOIN `groups` g ON g.group_id = c.group_id
        JOIN users u ON u.user_id = c.adviser_id
        WHERE 1=1";
$params = [];

if ($roleId === ROLE_STUDENT) {
    $groupId = currentStudentGroupId();
    $sql .= ' AND c.group_id = ?';
    $params[] = $groupId ?: 0;
} elseif ($roleId === ROLE_ADVISER) {
    $sql .= ' AND c.adviser_id = ?';
    $params[] = $_SESSION['user_id'];
}
// coordinator/admin: no extra filter (see all)

if ($statusFilter) {
    $sql .= ' AND c.status = ?';
    $params[] = $statusFilter;
}

$sql .= ' ORDER BY c.created_at DESC LIMIT 100';

$stmt = $db->prepare($sql);
$stmt->execute($params);

echo json_encode(['consultations' => $stmt->fetchAll()]);
