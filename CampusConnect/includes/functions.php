<?php
/**
 * includes/functions.php
 * Shared helper functions.
 */

/** Escape output for safe HTML rendering */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirect helper */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/** Is a user currently logged in? */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Get current logged-in user's role id */
function currentRoleId(): ?int
{
    return $_SESSION['role_id'] ?? null;
}

/** Get current logged-in user's role name */
function currentRoleName(): ?string
{
    $roleId = currentRoleId();
    return $roleId ? (ROLE_NAMES[$roleId] ?? null) : null;
}

/** Require login or redirect to login page */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

/** Require a specific role (or array of roles) or redirect to dashboard */
function requireRole($roles): void
{
    requireLogin();
    $roles = is_array($roles) ? $roles : [$roles];
    if (!in_array(currentRoleId(), $roles, true)) {
        redirect('/dashboard.php');
    }
}

/** Verify a submitted CSRF token against the session token */
function verifyCsrf(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && $token !== null && hash_equals($_SESSION['csrf_token'], $token);
}

/** Output a hidden CSRF input field */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token'] ?? '') . '">';
}

/** Flash message helpers (stored in session, shown once) */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/** Fetch the logged-in user's full record */
function currentUser(): ?array
{
    static $user = null;
    if ($user !== null) {
        return $user;
    }
    if (!isLoggedIn()) {
        return null;
    }
    $stmt = getDB()->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;
    return $user;
}

/** Insert a notification for a user */
function notifyUser(int $userId, string $title, string $message, ?string $link = null): void
{
    $stmt = getDB()->prepare(
        'INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $title, $message, $link]);
}

/** Count unread notifications for a user */
function unreadNotificationCount(int $userId): int
{
    $stmt = getDB()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

/** Log an admin/system activity */
function logActivity(?int $userId, string $action, string $details = ''): void
{
    $stmt = getDB()->prepare(
        'INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
}

/** Format a datetime string nicely */
function formatDate(?string $datetime, string $format = 'M d, Y g:i A'): string
{
    if (!$datetime) {
        return '—';
    }
    return date($format, strtotime($datetime));
}

/** Build a status badge span (Bootstrap-style classes) */
function statusBadge(string $status): string
{
    $map = [
        'pending'      => 'secondary',
        'approved'     => 'success',
        'rejected'     => 'danger',
        'draft'        => 'secondary',
        'submitted'    => 'info',
        'under_review' => 'warning',
        'revision'     => 'warning',
        'in_progress'  => 'primary',
        'completed'    => 'success',
        'active'       => 'success',
        'inactive'     => 'secondary',
        'cancelled'    => 'danger',
        'overdue'      => 'danger',
    ];
    $class = $map[$status] ?? 'secondary';
    $label = ucwords(str_replace('_', ' ', $status));
    return '<span class="badge badge-' . $class . '">' . e($label) . '</span>';
}

/** Handle a single file upload, returns stored filename or null on failure */
function handleUpload(array $file, string $destDir, array $allowedExt = ALLOWED_DOC_TYPES): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return null;
    }
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $filename = uniqid('file_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename)) {
        return null;
    }
    return $filename;
}

/** Return the group id the current student belongs to, or null */
function currentStudentGroupId(): ?int
{
    $user = currentUser();
    if (!$user) {
        return null;
    }
    $stmt = getDB()->prepare('SELECT group_id FROM group_members WHERE user_id = ? LIMIT 1');
    $stmt->execute([$user['user_id']]);
    $row = $stmt->fetch();
    return $row ? (int) $row['group_id'] : null;
}
