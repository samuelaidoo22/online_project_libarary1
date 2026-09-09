<?php
require_once 'config/db.php';
$current_page = 'browse';
$page_title   = 'Browse Projects';

$dept_filter = isset($_GET['dept']) ? (int)$_GET['dept'] : 0;
$cat_filter  = isset($_GET['cat'])  ? (int)$_GET['cat']  : 0;
$year_filter = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;

$conds = ["p.approval_status='approved'"]; $params = [];
if ($dept_filter) { $conds[] = "p.department_id=?"; $params[] = $dept_filter; }
if ($cat_filter)  { $conds[] = "p.category_id=?";  $params[] = $cat_filter; }
if ($year_filter) { $conds[] = "YEAR(p.upload_date)=?"; $params[] = $year_filter; }
$where = implode(' AND ', $conds);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM projects p WHERE $where");
$countStmt->execute($params);
$total_projects = (int)$countStmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_projects / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare("SELECT p.*, d.department_name, c.category_name FROM projects p JOIN departments d ON p.department_id=d.department_id JOIN categories c ON p.category_id=c.category_id WHERE $where ORDER BY p.upload_date DESC, p.project_id DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$projects = $stmt->fetchAll();

$departments = $pdo->query("SELECT * FROM departments")->fetchAll();
$categories  = $pdo->query("SELECT * FROM categories")->fetchAll();

require_once 'includes/header.php';
?>

<style>
.browse-layout {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 24px 0 64px;
}
.filter-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
}
.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
}
.filter-group {
    flex: 1;
    min-width: 200px;
}
.filter-group label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.filter-group label i { margin-right: 4px; }
.filter-select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    color: #1e293b;
    background-color: #f8fafc;
    cursor: pointer;
    transition: all 0.2s;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2364748b%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
    background-repeat: no-repeat;
    background-position: right 14px top 50%;
    background-size: 10px auto;
}
.filter-select:hover { border-color: #94a3b8; }
.filter-select:focus { 
    outline: none; border-color: #004AAD; background-color: #fff; 
    box-shadow: 0 0 0 3px rgba(0,74,173,0.1); 
}
.filter-action { margin-bottom: 2px; }

.results-bar {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.results-bar h2 { font-size: 16px; font-weight: 700; }
.results-bar span { font-size: 13px; color: #64748b; }

.proj-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 12px;
    display: block;
    text-decoration: none;
    color: inherit;
    transition: box-shadow 0.2s, border-color 0.2s;
}
.proj-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); border-color: #93c5fd; }
.proj-card h3 { font-size: 15px; font-weight: 600; color: #1e293b; margin: 8px 0 8px; line-height: 1.4; }
.proj-card .abstract { font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 14px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.proj-card .card-meta { display: flex; gap: 16px; font-size: 12px; color: #94a3b8; align-items: center; }
.proj-card .card-meta span { display: flex; align-items: center; gap: 4px; }

.empty-state { text-align: center; padding: 60px 20px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; }
.empty-state i { font-size: 40px; color: #cbd5e1; margin-bottom: 12px; display: block; }
.empty-state h3 { font-weight: 600; color: #64748b; margin-bottom: 6px; }
.empty-state p  { font-size: 13px; color: #94a3b8; }

@media (max-width: 768px) {
    .browse-layout { grid-template-columns: 1fr; gap: 16px; padding: 20px 0 40px; }
    .filter-panel { position: static; margin-bottom: 16px; }
}
</style>

<div class="page-banner">
    <div class="container">
        <div style="margin-bottom:14px;">
            <button type="button" class="back-button secondary" onclick="goBackSafely();"><i class="fas fa-arrow-left"></i> Back</button>
        </div>
        <h1>Browse Projects</h1>
        <p><?php echo $total_projects; ?> project<?php echo $total_projects!=1?'s':''; ?> found</p>
    </div>
</div>

<div class="container">
    <div class="browse-layout">

        <!-- Horizontal Filter Bar -->
        <div class="filter-panel">
            <form method="GET" action="browse.php" class="filter-form">
                
                <div class="filter-group">
                    <label><i class="fas fa-building text-blue"></i> Department</label>
                    <select name="dept" class="filter-select" onchange="this.form.submit()">
                        <option value="0">All Departments</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?php echo $d['department_id']; ?>" <?php echo $dept_filter==$d['department_id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($d['department_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-tags text-blue"></i> Category</label>
                    <select name="cat" class="filter-select" onchange="this.form.submit()">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo $c['category_id']; ?>" <?php echo $cat_filter==$c['category_id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($c['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt text-blue"></i> Year</label>
                    <select name="year" class="filter-select" onchange="this.form.submit()">
                        <option value="0">All Years</option>
                        <?php foreach ([date('Y'), date('Y')-1, date('Y')-2, date('Y')-3] as $y): ?>
                            <option value="<?php echo $y; ?>" <?php echo $year_filter==$y?'selected':''; ?>><?php echo $y; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-action">
                    <?php if ($dept_filter || $cat_filter || $year_filter): ?>
                        <a href="browse.php" class="btn btn-outline" style="height:40px; padding: 0 16px;"><i class="fas fa-redo-alt"></i> Reset</a>
                    <?php endif; ?>
                </div>

            </form>
        </div>

        <!-- Results -->
        <div>
            <div class="results-bar">
                <h2>All Projects</h2>
                <span><?php echo $total_projects; ?> results</span>
            </div>

            <?php if (empty($projects)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>No projects found</h3>
                    <p>Try a different filter combination.</p>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $p): ?>
                    <a href="project.php?id=<?php echo $p['project_id']; ?>" class="proj-card">
                        <span class="badge badge-blue"><?php echo htmlspecialchars($p['department_name']); ?></span>
                        <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                        <p class="abstract"><?php echo htmlspecialchars($p['abstract']); ?></p>
                        <div class="card-meta">
                            <span><i class="fas fa-calendar-alt"></i> <?php echo date('M Y', strtotime($p['upload_date'])); ?></span>
                            <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($p['category_name']); ?></span>
                            <span><i class="fas fa-eye"></i> <?php echo $p['view_count']; ?> views</span>
                            <span style="margin-left:auto; color:#004AAD; font-weight:600; font-size:12px;">View &rarr;</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($total_pages > 1): ?>
                <nav aria-label="Project pages" style="display:flex; justify-content:center; gap:8px; margin-top:24px; flex-wrap:wrap;">
                    <?php for ($page_number = 1; $page_number <= $total_pages; $page_number++): ?>
                        <a href="browse.php?<?php echo http_build_query(['dept' => $dept_filter, 'cat' => $cat_filter, 'year' => $year_filter, 'page' => $page_number]); ?>" class="btn <?php echo $page_number === $page ? 'btn-primary' : 'btn-outline'; ?> btn-sm" aria-current="<?php echo $page_number === $page ? 'page' : 'false'; ?>">
                            <?php echo $page_number; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
