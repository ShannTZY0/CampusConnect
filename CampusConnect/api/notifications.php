<?php
/**
 * api/notifications.php
 * GET  ?action=count  -> { unread: number }
 * GET  ?action=list   -> { notifications: [...] }
 * POST action=mark_read, id=<notification_id> -> { success: true }
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
$userId = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'count';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'mark_read') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'list') {
    $stmt = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
    $stmt->execute([$userId]);
    echo json_encode(['notifications' => $stmt->fetchAll()]);
    exit;
}

// default: count
echo json_encode(['unread' => unreadNotificationCount($userId)]);
