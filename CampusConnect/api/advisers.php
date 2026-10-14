<?php
/**
 * api/advisers.php
 * GET -> list of active advisers with their advisee group counts.
 * Used by the student adviser-browsing UI for lightweight/AJAX refreshes.
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
$stmt = $db->prepare("SELECT u.user_id, u.first_name, u.last_name, u.department, u.email,
    (SELECT COUNT(*) FROM `groups` g WHERE g.adviser_id = u.user_id) AS advisee_count
    FROM users u WHERE u.role_id = ? AND u.status = 'active' ORDER BY u.last_name");
$stmt->execute([ROLE_ADVISER]);

echo json_encode(['advisers' => $stmt->fetchAll()]);
