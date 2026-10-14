<?php
/**
 * config/constants.php
 * Global constants used throughout CampusConnect.
 */

define('APP_NAME', 'CampusConnect');
define('APP_VERSION', '1.0.0');

// BASE_URL: adjust to match your deployment folder, e.g. '/CampusConnect'
define('BASE_URL', '/CampusConnect');

// Filesystem root of the app
define('ROOT_PATH', dirname(__DIR__));

// Upload directories
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_PROPOSALS', UPLOAD_PATH . '/proposals');
define('UPLOAD_PROJECT_DOCS', UPLOAD_PATH . '/project_documents');
define('UPLOAD_SUBMISSIONS', UPLOAD_PATH . '/submissions');
define('UPLOAD_PROFILE_PHOTOS', UPLOAD_PATH . '/profile_photos');
define('UPLOAD_EVALUATION_FILES', UPLOAD_PATH . '/evaluation_files');

define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx', 'ppt', 'pptx']);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Roles (must match role_id values seeded in roles table)
define('ROLE_STUDENT', 1);
define('ROLE_ADVISER', 2);
define('ROLE_COORDINATOR', 3);
define('ROLE_ADMIN', 4);

define('ROLE_NAMES', [
    ROLE_STUDENT     => 'student',
    ROLE_ADVISER     => 'adviser',
    ROLE_COORDINATOR => 'coordinator',
    ROLE_ADMIN       => 'admin',
]);

date_default_timezone_set('Asia/Manila');

// Basic error display toggle - set to false in production
define('APP_DEBUG', true);
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
