<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if (!isset($_GET['id'])) {
    safe_redirect('/browse.php');
}

$projectId = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT file_path, title FROM projects WHERE project_id = ? AND approval_status = 'approved'");
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project || empty($project['file_path'])) {
    http_response_code(404);
    echo 'File not found or access denied.';
    exit();
}

$storedPath = trim(str_replace('\\', '/', $project['file_path']));
$storedPath = ltrim($storedPath, '/');

if ($storedPath === '') {
    http_response_code(404);
    echo 'File not found.';
    exit();
}

$candidates = [];
$candidates[] = PROJECT_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storedPath);

if (str_starts_with($storedPath, 'storage/uploads/')) {
    $legacyPath = 'uploads/' . substr($storedPath, strlen('storage/uploads/'));
    $candidates[] = PROJECT_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $legacyPath);
}

if (str_starts_with($storedPath, 'uploads/')) {
    $securePath = 'storage/' . $storedPath;
    $candidates[] = PROJECT_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $securePath);
    $candidates[] = PROJECT_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storedPath);
}

$resolvedPath = null;
foreach ($candidates as $candidate) {
    $real = realpath($candidate);
    if ($real !== false) {
        $resolvedPath = $real;
        break;
    }
}

if ($resolvedPath === null) {
    http_response_code(404);
    echo 'File not found.';
    exit();
}

$logStmt = $pdo->prepare("INSERT INTO access_logs (user_id, project_id, access_type) VALUES (?, ?, 'download_full')");
$logStmt->execute([$_SESSION['user_id'], $projectId]);

safe_file_download($resolvedPath, preg_replace('/[^A-Za-z0-9_-]+/', '_', $project['title']) . '.pdf');
?>
