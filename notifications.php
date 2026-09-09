<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if (is_admin()) {
    safe_redirect('admin/dashboard.php');
}

$current_page = 'notifications';
$page_title = 'Notifications';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        http_response_code(400);
        exit('Invalid or expired request.');
    }

    $notificationId = (int)$_POST['notification_id'];
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?');
    $stmt->execute([$notificationId, (int)$_SESSION['user_id']]);
    safe_redirect('notifications.php');
}

$stmt = $pdo->prepare('SELECT n.*, p.title AS project_title FROM notifications n LEFT JOIN projects p ON n.project_id = p.project_id WHERE n.user_id = ? ORDER BY n.created_at DESC');
$stmt->execute([(int)$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-banner">
    <div class="container">
        <div style="margin-bottom:14px;"><button type="button" class="back-button secondary" onclick="goBackSafely();"><i class="fas fa-arrow-left"></i> Back</button></div>
        <h1>Notifications</h1>
        <p>Updates about your project submissions and reviews.</p>
    </div>
</div>
<div class="container" style="padding:32px 24px 64px; max-width:900px;">
    <?php if (!$notifications): ?>
        <div class="card" style="text-align:center; padding:52px 24px;">
            <i class="fas fa-bell-slash" style="font-size:36px; color:#cbd5e1; margin-bottom:14px;"></i>
            <h2 style="font-size:18px; margin-bottom:6px;">No notifications yet</h2>
            <p class="text-muted">You will see approval updates here.</p>
        </div>
    <?php else: ?>
        <div style="display:grid; gap:12px;">
            <?php foreach ($notifications as $notification): ?>
                <article class="card" style="border-left:4px solid <?php echo $notification['is_read'] ? '#cbd5e1' : '#004AAD'; ?>; opacity:<?php echo $notification['is_read'] ? '0.78' : '1'; ?>;">
                    <div class="flex-between" style="gap:16px; align-items:flex-start;">
                        <div>
                            <h2 style="font-size:16px; margin-bottom:5px;"><?php echo htmlspecialchars($notification['title']); ?></h2>
                            <p style="color:#475569; line-height:1.7;"><?php echo htmlspecialchars($notification['message']); ?></p>
                            <time class="text-muted text-sm" datetime="<?php echo htmlspecialchars($notification['created_at']); ?>"><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($notification['created_at']))); ?></time>
                        </div>
                        <?php if (!$notification['is_read']): ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="notification_id" value="<?php echo (int)$notification['notification_id']; ?>">
                                <button type="submit" class="btn btn-outline btn-sm">Mark read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
