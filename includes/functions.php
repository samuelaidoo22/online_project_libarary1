<?php
/**
 * Shared utility helpers.
 */

function h($value) {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function safe_redirect($url) {
    $url = str_replace(["\r", "\n"], '', $url);
    header('Location: ' . $url);
    exit();
}

function filter_int($value, $default = 0) {
    $value = filter_var($value, FILTER_VALIDATE_INT);
    return $value === false ? $default : $value;
}

function request_get($key, $default = '') {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

function request_post($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

function request_array($key, array $default = []): array {
    return isset($_REQUEST[$key]) && is_array($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
}

function old($key, $default = '') {
    return isset($_POST[$key]) ? htmlspecialchars(trim($_POST[$key]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $default;
}

function normalize_keywords(string $keywords): string {
    $parts = array_filter(array_map('trim', explode(',', $keywords)), 'strlen');
    $parts = array_unique($parts);
    return implode(', ', $parts);
}

function normalize_authors(array $authors): array {
    $result = [];
    foreach ($authors as $author) {
        $author = trim($author);
        if ($author !== '') {
            $result[] = preg_replace('/\s+/', ' ', $author);
        }
    }
    return $result;
}

function create_notification(PDO $pdo, int $userId, ?int $projectId, string $title, string $message, ?string $email = null): void {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, project_id, title, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $projectId, $title, $message]);
    $notificationId = (int)$pdo->lastInsertId();

    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $delivery = $pdo->prepare("INSERT INTO notification_delivery_logs (notification_id, channel, status, next_attempt_at) VALUES (?, 'email', 'queued', NOW())");
        $delivery->execute([$notificationId]);
        try {
            $sent = send_notification_email($email, 'GCTU Project Library: ' . $title, $message);
            $status = $sent ? 'sent' : 'failed';
            $error = $sent ? null : 'Email provider rejected or could not be reached.';
            $update = $pdo->prepare("UPDATE notification_delivery_logs SET status = ?, attempts = attempts + 1, last_error = ?, sent_at = CASE WHEN ? = 'sent' THEN NOW() ELSE sent_at END, next_attempt_at = CASE WHEN ? = 'failed' THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE) ELSE NULL END WHERE notification_id = ? AND channel = 'email'");
            $update->execute([$status, $error, $status, $status, $notificationId]);
        } catch (Throwable $e) {
            $update = $pdo->prepare("UPDATE notification_delivery_logs SET status = 'failed', attempts = attempts + 1, last_error = ?, next_attempt_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE notification_id = ? AND channel = 'email'");
            $update->execute([substr($e->getMessage(), 0, 500), $notificationId]);
        }
    }
}

function send_notification_email(string $recipient, string $subject, string $message): bool {
    $env = file_exists(__DIR__ . '/../config/env.php') ? require __DIR__ . '/../config/env.php' : [];
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (empty($env['MAIL_ENABLED']) || !file_exists($autoload)) {
        return false;
    }

    require_once $autoload;
    $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = (string)$env['MAIL_HOST'];
    $mailer->Port = (int)($env['MAIL_PORT'] ?? 587);
    $mailer->SMTPAuth = !empty($env['MAIL_USERNAME']);
    $mailer->Username = (string)($env['MAIL_USERNAME'] ?? '');
    $mailer->Password = (string)($env['MAIL_PASSWORD'] ?? '');
    $mailer->SMTPSecure = strtolower((string)($env['MAIL_ENCRYPTION'] ?? 'tls')) === 'ssl'
        ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
        : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->setFrom((string)$env['MAIL_FROM_ADDRESS'], (string)($env['MAIL_FROM_NAME'] ?? 'GCTU Project Library'));
    $mailer->addAddress($recipient);
    $mailer->Subject = $subject;
    $mailer->Body = $message;
    $mailer->AltBody = $message;
    return $mailer->send();
}

function validate_pdf_upload(array $file): array {
    if (!isset($file['error']) || is_array($file['error'])) {
        return [false, 'Invalid upload request.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'File upload failed.'];
    }

    if ($file['size'] > 20 * 1024 * 1024) {
        return [false, 'File size exceeds the 20MB limit.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        return [false, 'Only PDF files are accepted.'];
    }

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $mime = mime_content_type($file['tmp_name']);
    } else {
        $handle = fopen($file['tmp_name'], 'rb');
        $header = $handle ? fread($handle, 4) : '';
        if ($handle) {
            fclose($handle);
        }
        if ($header !== '%PDF') {
            return [false, 'Only genuine PDF files are accepted.'];
        }
        return [true, ''];
    }

    if ($mime !== 'application/pdf' && $mime !== 'application/x-pdf') {
        return [false, 'Only genuine PDF files are accepted.'];
    }

    return [true, ''];
}

function generate_upload_filename(string $prefix = 'GCTU_'): string {
    return $prefix . bin2hex(random_bytes(16)) . '.pdf';
}

function resolve_upload_path(string $filename): string {
    return UPLOAD_DIR . '/' . basename($filename);
}

function ensure_upload_directory(): void {
    if (!defined('UPLOAD_DIR')) {
        throw new RuntimeException('UPLOAD_DIR is not configured.');
    }

    $path = UPLOAD_DIR;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

function safe_file_download(string $filePath, string $downloadName): void {
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo 'File not found.';
        exit();
    }

    $realPath = realpath($filePath);
    if ($realPath === false) {
        http_response_code(403);
        echo 'Access denied.';
        exit();
    }

    $allowedRoots = [];
    $secureRoot = realpath(UPLOAD_DIR);
    if ($secureRoot !== false) {
        $allowedRoots[] = $secureRoot;
    }

    $legacyRoot = realpath(PROJECT_ROOT . DIRECTORY_SEPARATOR . 'uploads');
    if ($legacyRoot !== false) {
        $allowedRoots[] = $legacyRoot;
    }

    $isAllowed = false;
    foreach ($allowedRoots as $root) {
        $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strncmp($realPath, $root, strlen($root)) === 0) {
            $isAllowed = true;
            break;
        }
    }

    if (!$isAllowed) {
        http_response_code(403);
        echo 'Access denied.';
        exit();
    }

    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . rawurlencode(basename($downloadName)) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($realPath));
    readfile($realPath);
    exit();
}
