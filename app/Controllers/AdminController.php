<?php
namespace App\Controllers;

class AdminController
{
    public function dashboard($pdo): void
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            safe_redirect('/login.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_id'], $_POST['action'])) {
            if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                http_response_code(400);
                exit('Invalid or expired request.');
            }

            $projectId = (int) ($_POST['project_id'] ?? 0);
            $requestedAction = $_POST['action'] ?? '';
            if (!in_array($requestedAction, ['approve', 'reject'], true)) {
                http_response_code(400);
                exit('Invalid review action.');
            }
            $action = $requestedAction === 'approve' ? 'approved' : 'rejected';

            if ($projectId > 0) {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE projects SET approval_status = ? WHERE project_id = ? AND approval_status = 'pending'");
                $stmt->execute([$action, $projectId]);
                if ($stmt->rowCount() === 1) {
                    $auditStmt = $pdo->prepare("INSERT INTO review_audit_logs (project_id, admin_id, action) VALUES (?, ?, ?)");
                    $auditStmt->execute([$projectId, (int)$_SESSION['user_id'], $action]);
                    $ownerStmt = $pdo->prepare("SELECT p.title, u.user_id, u.email FROM projects p LEFT JOIN users u ON p.uploader_id = u.user_id WHERE p.project_id = ?");
                    $ownerStmt->execute([$projectId]);
                    $owner = $ownerStmt->fetch();
                    if ($owner && $owner['user_id']) {
                        $statusText = $action === 'approved' ? 'approved' : 'rejected';
                        create_notification(
                            $pdo,
                            (int)$owner['user_id'],
                            $projectId,
                            'Project ' . ucfirst($statusText),
                            'Your project "' . $owner['title'] . '" has been ' . $statusText . ' by the library administrator.',
                            $owner['email'] ?? null
                        );
                    }
                }
                $pdo->commit();
            }

            safe_redirect('/admin/dashboard.php');
        }

        $pendingProjects = $pdo->query(
            "SELECT p.*, d.department_name, c.category_name, u.username AS uploader_name
             FROM projects p
             LEFT JOIN departments d ON p.department_id = d.department_id
             LEFT JOIN categories c ON p.category_id = c.category_id
             LEFT JOIN users u ON p.uploader_id = u.user_id
             WHERE p.approval_status = 'pending'
             ORDER BY p.upload_date DESC"
        )->fetchAll();

        $authorsByProject = [];
        if ($pendingProjects) {
            $authorRows = $pdo->query("SELECT project_id, author_name FROM authors WHERE project_id IN (" . implode(',', array_map('intval', array_column($pendingProjects, 'project_id'))) . ") ORDER BY author_id")->fetchAll();
            foreach ($authorRows as $authorRow) {
                $authorsByProject[(int)$authorRow['project_id']][] = $authorRow['author_name'];
            }
        }

        $reviewedProjects = $pdo->query(
            "SELECT p.*, d.department_name, c.category_name, u.username AS uploader_name
             FROM projects p
             LEFT JOIN departments d ON p.department_id = d.department_id
             LEFT JOIN categories c ON p.category_id = c.category_id
             LEFT JOIN users u ON p.uploader_id = u.user_id
             WHERE p.approval_status IN ('approved', 'rejected')
             ORDER BY p.upload_date DESC LIMIT 10"
        )->fetchAll();

        $auditHistory = $pdo->query(
            "SELECT r.created_at, r.action, p.title, u.username AS admin_name
             FROM review_audit_logs r
             JOIN projects p ON p.project_id = r.project_id
             JOIN users u ON u.user_id = r.admin_id
             ORDER BY r.created_at DESC LIMIT 20"
        )->fetchAll();

        $stats = [
            'pending' => count($pendingProjects),
            'approved' => (int) $pdo->query("SELECT COUNT(*) FROM projects WHERE approval_status = 'approved'")->fetchColumn(),
            'rejected' => (int) $pdo->query("SELECT COUNT(*) FROM projects WHERE approval_status = 'rejected'")->fetchColumn(),
        ];

        $page_title = 'Admin Dashboard';
        $current_page = 'admin';

        require_once __DIR__ . '/../../includes/header.php';
        ?>
        <style>
            .admin-shell { padding: 32px 0 60px; }
            .admin-grid { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap: 18px; margin-bottom: 28px; }
            .stat-box {
                background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
                border: 1px solid #dfeaf3;
                border-left: 4px solid #004AAD;
                border-radius: 12px;
                padding: 20px 22px;
                box-shadow: 0 10px 18px rgba(15, 23, 42, 0.03);
            }
            .stat-box h3 { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; }
            .stat-box .value { font-size: 28px; font-weight: 700; color: #0b3d91; margin-top: 8px; }
            .admin-card {
                background: #fff;
                border: 1px solid #dfeaf3;
                border-radius: 14px;
                padding: 22px;
                box-shadow: 0 10px 18px rgba(15, 23, 42, 0.03);
            }
            .table-wrap { overflow-x: auto; }
            .admin-actions { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
            .admin-actions form { display: inline; }
            .preview-box { margin-top: 12px; padding: 14px; background: #f8fbff; border: 1px solid #dfeaf3; border-radius: 10px; }
            .preview-box summary { cursor: pointer; color: #0b3d91; font-weight: 700; }
            .preview-box p { margin-top: 10px; color: #475569; line-height: 1.7; }
            .thesis-badge {
                display: inline-block;
                background: #e0f2fe;
                color: #0f4c81;
                padding: 5px 9px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }
            @media (max-width: 768px) { .admin-grid { grid-template-columns: 1fr; } }
        </style>

        <div class="page-banner">
            <div class="container">
                <div style="margin-bottom:14px;">
                    <button type="button" class="back-button secondary" onclick="goBackSafely();"><i class="fas fa-arrow-left"></i> Back</button>
                </div>
                <h1>Admin Dashboard</h1>
                <p>Review and manage submitted student projects.</p>
            </div>
        </div>

        <div class="container admin-shell">
            <div class="admin-grid">
                <div class="stat-box">
                    <h3>Pending</h3>
                    <div class="value"><?php echo $stats['pending']; ?></div>
                </div>
                <div class="stat-box">
                    <h3>Approved</h3>
                    <div class="value"><?php echo $stats['approved']; ?></div>
                </div>
                <div class="stat-box">
                    <h3>Rejected</h3>
                    <div class="value"><?php echo $stats['rejected']; ?></div>
                </div>
            </div>

            <div class="admin-card mb-3">
                <div class="flex-between mb-3" style="gap:12px; flex-wrap:wrap;">
                    <div class="section-title" style="margin-bottom:0;">Pending Review</div>
                    <span class="thesis-badge">Thesis Queue</span>
                </div>
                <?php if (empty($pendingProjects)): ?>
                    <p class="text-muted">No project submissions are awaiting approval.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Uploader</th>
                                    <th>Department</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingProjects as $project): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                                            <details class="preview-box">
                                                <summary><i class="fas fa-file-alt"></i> Preview submission</summary>
                                                <p><?php echo nl2br(htmlspecialchars($project['abstract'])); ?></p>
                                                <p><strong>Authors:</strong> <?php echo htmlspecialchars(implode(', ', $authorsByProject[(int)$project['project_id']] ?? ['Not provided'])); ?></p>
                                                <p><strong>Keywords:</strong> <?php echo htmlspecialchars($project['keywords'] ?: 'Not provided'); ?></p>
                                                <?php if (!empty($project['file_path'])): ?>
                                                    <p><a class="btn btn-outline btn-sm" href="download.php?id=<?php echo (int)$project['project_id']; ?>"><i class="fas fa-file-download"></i> Preview PDF</a></p>
                                                <?php endif; ?>
                                            </details>
                                        </td>
                                        <td><?php echo htmlspecialchars($project['uploader_name'] ?? 'Unknown'); ?></td>
                                        <td><?php echo htmlspecialchars($project['department_name'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($project['category_name'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($project['upload_date']))); ?></td>
                                        <td>
                                            <div class="admin-actions">
                                                <form method="POST" action="dashboard.php">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                    <input type="hidden" name="project_id" value="<?php echo (int) $project['project_id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
                                                </form>
                                                <form method="POST" action="dashboard.php">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                    <input type="hidden" name="project_id" value="<?php echo (int) $project['project_id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Reject</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="admin-card">
                <div class="flex-between mb-3" style="gap:12px; flex-wrap:wrap;">
                    <div class="section-title" style="margin-bottom:0;">Recent Decisions</div>
                    <span class="thesis-badge" style="background:#dcfce7; color:#166534;">Archive</span>
                </div>
                <?php if (empty($reviewedProjects)): ?>
                    <p class="text-muted">No recent decisions yet.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Uploader</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reviewedProjects as $project): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($project['title']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $project['approval_status'] === 'approved' ? 'badge-green' : 'badge-red'; ?>"><?php echo ucfirst($project['approval_status']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($project['uploader_name'] ?? 'Unknown'); ?></td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($project['upload_date']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="admin-card" style="margin-top:24px;">
                <div class="flex-between mb-3" style="gap:12px; flex-wrap:wrap;">
                    <div class="section-title" style="margin-bottom:0;">Review Audit History</div>
                    <span class="thesis-badge" style="background:#fef3c7; color:#92400e;">Accountability</span>
                </div>
                <?php if (empty($auditHistory)): ?>
                    <p class="text-muted">No approval decisions have been recorded yet.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table">
                            <thead><tr><th>Project</th><th>Action</th><th>Admin</th><th>Time</th></tr></thead>
                            <tbody>
                                <?php foreach ($auditHistory as $audit): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($audit['title']); ?></td>
                                        <td><span class="badge <?php echo $audit['action'] === 'approved' ? 'badge-green' : 'badge-red'; ?>"><?php echo ucfirst($audit['action']); ?></span></td>
                                        <td><?php echo htmlspecialchars($audit['admin_name']); ?></td>
                                        <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($audit['created_at']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        require_once __DIR__ . '/../../includes/footer.php';
    }
}
