<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found.');
}

require_once __DIR__ . '/includes/bootstrap.php';

$stmt = $pdo->query("SELECT d.delivery_id, d.notification_id, d.attempts, n.title, n.message, u.email
    FROM notification_delivery_logs d
    JOIN notifications n ON n.notification_id = d.notification_id
    JOIN users u ON u.user_id = n.user_id
    WHERE d.channel = 'email' AND d.status = 'failed' AND d.attempts < 3
      AND (d.next_attempt_at IS NULL OR d.next_attempt_at <= NOW())
    ORDER BY d.created_at ASC LIMIT 50");
$update = $pdo->prepare("UPDATE notification_delivery_logs SET status = ?, attempts = attempts + 1, last_error = ?, sent_at = CASE WHEN ? = 'sent' THEN NOW() ELSE sent_at END, next_attempt_at = CASE WHEN ? = 'failed' THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE) ELSE NULL END WHERE delivery_id = ?");

$count = 0;
foreach ($stmt->fetchAll() as $delivery) {
    try {
        $sent = send_notification_email($delivery['email'], 'GCTU Project Library: ' . $delivery['title'], $delivery['message']);
        $status = $sent ? 'sent' : 'failed';
        $error = $sent ? null : 'Email provider rejected or could not be reached.';
    } catch (Throwable $e) {
        $status = 'failed';
        $error = substr($e->getMessage(), 0, 500);
    }
    $update->execute([$status, $error, $status, $status, $delivery['delivery_id']]);
    $count++;
}

echo "Processed {$count} notification deliveries.\n";
