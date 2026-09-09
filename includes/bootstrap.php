<?php
/**
 * Shared application bootstrap for the GCTU Project Library.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', PROJECT_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads');
}

if (!defined('UPLOAD_URL')) {
    define('UPLOAD_URL', 'storage/uploads');
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0775, true);
}

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
?>
