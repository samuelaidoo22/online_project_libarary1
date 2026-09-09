<?php
require_once __DIR__ . '/includes/bootstrap.php';
$current_page = 'browse';
$project_id = filter_int($_GET['id']);
if ($project_id === 0) {
    safe_redirect('browse.php');
}
$stmt = $pdo->prepare("SELECT p.*, d.department_name, c.category_name, u.username as uploader FROM projects p JOIN departments d ON p.department_id=d.department_id JOIN categories c ON p.category_id=c.category_id JOIN users u ON p.uploader_id=u.user_id WHERE p.project_id = ? AND p.approval_status='approved'");
$stmt->execute([$project_id]);
$project = $stmt->fetch();
if (!$project) {
    safe_redirect('browse.php');
}
$stmt = $pdo->prepare("SELECT * FROM authors WHERE project_id = ?");
$stmt->execute([$project_id]);
$authors = $stmt->fetchAll();
$page_title = $project['title'];
if (is_logged_in()) {
    $pdo->prepare("INSERT INTO access_logs (user_id, project_id, access_type) VALUES (?, ?, 'view_abstract')")->execute([$_SESSION['user_id'], $project_id]);
}
$pdo->prepare("UPDATE projects SET view_count = view_count + 1 WHERE project_id = ?")->execute([$project_id]);
require_once 'includes/header.php';
?>
<div class="page-banner">
    <div class="container">
        <div style="margin-bottom:10px;">
            <button type="button" class="back-button" onclick="goBackSafely();"><i class="fas fa-arrow-left"></i> Back</button>
        </div>
        <h1 style="font-size:22px; line-height:1.3;"><?php echo htmlspecialchars($project['title']); ?></h1>
        <p>
            <span class="badge badge-gold" style="font-size:12px;"><?php echo htmlspecialchars($project['department_name']); ?></span>
            &nbsp; <?php echo htmlspecialchars($project['category_name']); ?> &nbsp;&bull;&nbsp;
            <?php echo date('F Y', strtotime($project['upload_date'])); ?>
        </p>
    </div>
</div>

<div class="container" style="padding:32px 24px 64px;">
    <div style="margin-bottom:16px;">
        <button type="button" class="back-button secondary" onclick="goBackSafely();"><i class="fas fa-arrow-left"></i> Back</button>
    </div>
    <div class="responsive-grid" style="display:grid; grid-template-columns:1fr 300px; gap:24px; align-items:start;">

        <!-- Main content -->
        <div>
            <!-- Stats row -->
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:20px;">
                <div class="card" style="text-align:center; padding:16px;">
                    <div style="font-size:22px; font-weight:700; color:#004AAD;"><?php echo number_format($project['view_count']); ?></div>
                    <div class="text-muted text-sm">Total Views</div>
                </div>
                <div class="card" style="text-align:center; padding:16px;">
                    <div style="font-size:22px; font-weight:700; color:#004AAD;"><?php echo date('Y', strtotime($project['upload_date'])); ?></div>
                    <div class="text-muted text-sm">Year</div>
                </div>
                <div class="card" style="text-align:center; padding:16px;">
                    <div style="font-size:22px; font-weight:700; color:#004AAD;">PDF</div>
                    <div class="text-muted text-sm">Format</div>
                </div>
            </div>

            <!-- Abstract -->
            <div class="card mb-2">
                <div class="section-title">Abstract</div>
                <p style="line-height:1.9; color:#374151; text-align:justify;"><?php echo nl2br(htmlspecialchars($project['abstract'])); ?></p>
            </div>

            <!-- Keywords -->
            <?php if (!empty($project['keywords'])): ?>
            <div class="card">
                <div class="section-title">Keywords</div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <?php foreach (explode(',', $project['keywords']) as $kw): ?>
                        <span class="badge badge-gray" style="font-size:12px;"><?php echo trim(htmlspecialchars($kw)); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside>
            <div class="card mb-2">
                <div class="section-title">Project Info</div>
                <table style="width:100%; font-size:13px; border-collapse:collapse;">
                    <tr><td style="padding:8px 0; color:#64748b; font-weight:600;">Author(s)</td><td style="padding:8px 0;"><?php foreach($authors as $a) echo htmlspecialchars($a['author_name']).'<br>'; ?></td></tr>
                    <tr><td style="padding:8px 0; color:#64748b; font-weight:600;">Department</td><td style="padding:8px 0; color:#004AAD; font-weight:600;"><?php echo htmlspecialchars($project['department_name']); ?></td></tr>
                    <tr><td style="padding:8px 0; color:#64748b; font-weight:600;">Category</td><td style="padding:8px 0;"><?php echo htmlspecialchars($project['category_name']); ?></td></tr>
                    <tr><td style="padding:8px 0; color:#64748b; font-weight:600;">Submitted</td><td style="padding:8px 0;"><?php echo date('d M Y', strtotime($project['upload_date'])); ?></td></tr>
                    <tr><td style="padding:8px 0; color:#64748b; font-weight:600;">By</td><td style="padding:8px 0;"><?php echo htmlspecialchars($project['uploader']); ?></td></tr>
                </table>
                <div style="margin-top:20px;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="download.php?id=<?php echo $project['project_id']; ?>" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                    <?php else: ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px; text-align:center;">
                            <p style="font-size:13px; color:#64748b; margin-bottom:10px;">Login to download the full document.</p>
                            <a href="login.php" class="btn btn-primary btn-sm" style="width:100%; justify-content:center;">
                                <i class="fas fa-lock"></i> Login to Access
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </aside>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
