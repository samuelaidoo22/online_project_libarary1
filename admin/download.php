<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_role('admin');

$projectId = filter_int($_GET['id'] ?? 0);
if ($projectId < 1) {
    http_response_code(400);
    exit('Invalid project.');
}

$stmt = $pdo->prepare('SELECT file_path, title FROM projects WHERE project_id = ?');
$stmt->execute([$projectId]);
$project = $stmt->fetch();
if (!$project || empty($project['file_path'])) {
    http_response_code(404);
    exit('File not found.');
}

$storedPath = ltrim(str_replace('\\', '/', trim($project['file_path'])), '/');
$candidates = [PROJECT_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storedPath)];
if (str_starts_with($storedPath, 'storage/uploads/')) {
    $candidates[] = PROJECT_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($storedPath);
}
if (str_starts_with($storedPath, 'uploads/')) {
    $candidates[] = PROJECT_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $storedPath;
}

$resolvedPath = null;
foreach ($candidates as $candidate) {
    $realPath = realpath($candidate);
    if ($realPath !== false) {
        $resolvedPath = $realPath;
        break;
    }
}
if ($resolvedPath === null) {
    http_response_code(404);
    exit('File not found.');
}

safe_file_download($resolvedPath, preg_replace('/[^A-Za-z0-9_-]+/', '_', $project['title']) . '.pdf');
