<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    safe_redirect('/login.php');
}

$current_page = 'upload';
$page_title = 'Upload Project';
$error = '';
$success = '';

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid or expired session. Please try again.';
    } elseif (!isset($_FILES['project_file']) || $_FILES['project_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please attach a PDF file.';
    } else {
        [$isValid, $validationError] = validate_pdf_upload($_FILES['project_file']);
        if (!$isValid) {
            $error = $validationError;
        } else {
            $title = trim($_POST['title'] ?? '');
            $abstract = trim($_POST['abstract'] ?? '');
            $keywords = normalize_keywords(trim($_POST['keywords'] ?? ''));
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $departmentId = (int)($_POST['department_id'] ?? 0);
            $submittedAuthors = normalize_authors(request_array('authors', []));

            if ($title === '' || strlen($title) > 255) {
                $error = 'Project title is required and must not exceed 255 characters.';
            } elseif ($abstract === '' || strlen($abstract) > 10000) {
                $error = 'A project abstract is required and must not exceed 10,000 characters.';
            } elseif (strlen($keywords) > 255) {
                $error = 'Keywords must not exceed 255 characters.';
            } elseif ($categoryId < 1 || $departmentId < 1) {
                $error = 'Please select a valid department and category.';
            } elseif (!$pdo->query("SELECT COUNT(*) FROM categories WHERE category_id = " . $categoryId)->fetchColumn() || !$pdo->query("SELECT COUNT(*) FROM departments WHERE department_id = " . $departmentId)->fetchColumn()) {
                $error = 'The selected department or category is not available.';
            } elseif (empty($submittedAuthors)) {
                $error = 'At least one author is required.';
            } elseif (count($submittedAuthors) > 10 || count(array_filter($submittedAuthors, static fn($author) => strlen($author) > 150)) > 0) {
                $error = 'Provide no more than 10 authors, with each name limited to 150 characters.';
            } else {
                $fileMoved = false;
            try {
                ensure_upload_directory();
                $filename = generate_upload_filename();
                $destination = UPLOAD_DIR . DIRECTORY_SEPARATOR . $filename;
                $storedPath = 'storage/uploads/' . $filename;

                if (!move_uploaded_file($_FILES['project_file']['tmp_name'], $destination)) {
                    $error = 'Could not save file. Check upload folder permissions.';
                } else {
                    $fileMoved = true;
                    $pdo->beginTransaction();

                    $pdo->prepare("INSERT INTO projects (title, abstract, keywords, category_id, department_id, file_path, upload_date, uploader_id, approval_status) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, 'pending')")
                        ->execute([$title, $abstract, $keywords, $categoryId, $departmentId, $storedPath, $_SESSION['user_id']]);

                    $projectId = (int)$pdo->lastInsertId();
                    $authorStmt = $pdo->prepare("INSERT INTO authors (project_id, author_name) VALUES (?, ?)");

                    foreach ($submittedAuthors as $authorName) {
                        $authorStmt->execute([$projectId, $authorName]);
                    }

                    $pdo->commit();
                    $success = 'Project submitted successfully! It is pending department review.';
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($fileMoved && isset($destination) && is_file($destination)) {
                    unlink($destination);
                }
                $error = 'Database error. Please try again.';
            }
            }
        }
    }
}

require_once 'includes/header.php';
?>
<div class="page-banner">
    <div class="container">
        <div style="margin-bottom:14px;">
            <button type="button" class="back-button secondary" onclick="goBackSafely();"><i class="fas fa-arrow-left"></i> Back</button>
        </div>
        <h1>Submit Project</h1>
        <p>Upload your final-year project, dissertation, or thesis for academic review.</p>
    </div>
</div>
<div class="container" style="padding:32px 24px 64px;">
    <div class="responsive-grid" style="display:grid; grid-template-columns:1fr 290px; gap:24px; align-items:start;">

        <!-- Form -->
        <div class="card" style="padding:28px;">
            <?php if ($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; color:#0b3d91; margin-bottom:18px; padding-bottom:10px; border-bottom:1px solid #e2e8f0;">Project Details</div>

                <div class="form-group">
                    <label class="form-label">Full Project Title *</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter the complete project title" required>
                </div>
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Department *</label>
                        <select name="department_id" class="form-control" required>
                            <option value="" disabled selected>Select department</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['department_id']; ?>" <?php echo (isset($_SESSION['dept_id'])&&$_SESSION['dept_id']==$d['department_id'])?'selected':''; ?>>
                                    <?php echo $d['department_code'].' — '.$d['department_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="" disabled selected>Select category</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c['category_id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Abstract *</label>
                    <textarea name="abstract" class="form-control" rows="5" placeholder="Provide a detailed summary of your research…" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Keywords</label>
                    <input type="text" name="keywords" class="form-control" placeholder="e.g. AI, Machine Learning, Ghana (comma-separated)">
                </div>

                <div style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; color:#0b3d91; margin:22px 0 14px; padding-bottom:10px; border-bottom:1px solid #e2e8f0;">Author(s)</div>
                <div id="author-list" class="form-group">
                    <input type="text" name="authors[]" class="form-control" placeholder="Full Name of Author 1" required style="margin-bottom:8px;">
                    <input type="text" name="authors[]" class="form-control" placeholder="Full Name of Author 2 (optional)">
                </div>
                <button type="button" id="add-author" class="btn btn-outline btn-sm mb-2">
                    <i class="fas fa-plus"></i> Add Author
                </button>

                <div style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; color:#0b3d91; margin:22px 0 14px; padding-bottom:10px; border-bottom:1px solid #e2e8f0;">Project File</div>
                <div class="form-group">
                    <label class="form-label">Upload PDF *</label>
                    <input type="file" name="project_file" class="form-control" accept=".pdf" required id="file-input">
                    <p style="font-size:12px; color:#64748b; margin-top:6px;"><i class="fas fa-info-circle"></i> PDF format only. Maximum file size: 20MB.</p>
                </div>

                <button type="submit" class="btn btn-primary" style="padding:12px 32px;">
                    <i class="fas fa-paper-plane"></i> Submit for Review
                </button>
            </form>
        </div>

        <!-- Guide -->
        <aside>
            <div class="card" style="background:linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);">
                <div style="font-size:15px; font-weight:700; color:#0b3d91; margin-bottom:16px;"><i class="fas fa-info-circle"></i> Submission Guide</div>
                <ul style="list-style:none; font-size:13px; color:#475569; line-height:1.9;">
                    <li style="margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #f1f5f9;">
                        <strong style="display:block; color:#1e293b;">PDF Format Only</strong>
                        Export your document as a standard PDF, max 20MB.
                    </li>
                    <li style="margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #f1f5f9;">
                        <strong style="display:block; color:#1e293b;">Detailed Abstract</strong>
                        A clear abstract improves discoverability in searches.
                    </li>
                    <li style="margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #f1f5f9;">
                        <strong style="display:block; color:#1e293b;">Use Keywords</strong>
                        Add 4–6 relevant keywords separated by commas.
                    </li>
                    <li>
                        <strong style="display:block; color:#1e293b;">Review Process</strong>
                        Submitted projects are reviewed by department heads within 1–3 working days.
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</div>
<script>
document.getElementById('add-author').addEventListener('click', function() {
    const n = document.querySelectorAll('#author-list input').length + 1;
    const i = document.createElement('input');
    i.type='text'; i.name='authors[]'; i.className='form-control';
    i.placeholder='Full Name of Author '+n+' (optional)'; i.style.marginTop='8px';
    document.getElementById('author-list').appendChild(i);
});
</script>
<?php require_once 'includes/footer.php'; ?>
