<?php
require_once 'config/db.php';
$current_page = 'search';
$page_title   = 'Search';
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$projects = [];
$total_projects = 0;
$total_pages = 1;
if ($q) {
    $t = "%$q%";
    $count = $pdo->prepare("SELECT COUNT(*) FROM projects p WHERE p.approval_status='approved' AND (p.title LIKE ? OR p.keywords LIKE ? OR p.abstract LIKE ?)");
    $count->execute([$t, $t, $t]);
    $total_projects = (int)$count->fetchColumn();
    $total_pages = max(1, (int)ceil($total_projects / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;

    $s = $pdo->prepare("SELECT p.*, d.department_name, c.category_name FROM projects p JOIN departments d ON p.department_id=d.department_id JOIN categories c ON p.category_id=c.category_id WHERE p.approval_status='approved' AND (p.title LIKE ? OR p.keywords LIKE ? OR p.abstract LIKE ?) ORDER BY p.upload_date DESC, p.project_id DESC LIMIT $per_page OFFSET $offset");
    $s->execute([$t,$t,$t]);
    $projects = $s->fetchAll();
}
require_once 'includes/header.php';
?>
<style>
.search-top { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 28px 0; }
.big-search {
    display: flex; max-width: 640px;
    border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden;
    background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.big-search input { flex:1; border:none; padding:14px 18px; font-size:15px; font-family:inherit; color:#1e293b; }
.big-search input:focus { outline:none; }
.big-search button { background:#004AAD; color:#fff; border:none; padding:14px 24px; font-weight:700; font-size:14px; cursor:pointer; display:flex; align-items:center; gap:6px; white-space:nowrap; }
.big-search button:hover { background:#003a8c; }
.chips { display:flex; gap:8px; margin-top:14px; flex-wrap:wrap; }
.chip { padding:5px 14px; background:#f0f4f8; border:1px solid #e2e8f0; border-radius:20px; font-size:12px; color:#475569; text-decoration:none; transition:all 0.15s; }
.chip:hover { background:#dbeafe; border-color:#93c5fd; color:#004AAD; }
.results-area { padding: 32px 0 64px; }
.proj-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:20px; margin-bottom:12px; display:block; text-decoration:none; color:inherit; transition:box-shadow 0.2s, border-color 0.2s; }
.proj-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.07); border-color:#93c5fd; }
.proj-card h3 { font-size:15px; font-weight:600; color:#1e293b; margin:8px 0 8px; line-height:1.4; }
.proj-card .abstract { font-size:13px; color:#64748b; line-height:1.6; margin-bottom:12px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.proj-card .card-meta { display:flex; gap:16px; font-size:12px; color:#94a3b8; }
.proj-card .card-meta span { display:flex; align-items:center; gap:4px; }
.no-result { text-align:center; padding:60px 20px; background:#fff; border:1px solid #e2e8f0; border-radius:10px; }
.no-result i { font-size:40px; color:#cbd5e1; margin-bottom:12px; display:block; }
.no-result h3 { font-weight:600; color:#64748b; margin-bottom:6px; }
.no-result p { font-size:13px; color:#94a3b8; }
</style>

<div class="search-top">
    <div class="container">
        <div style="margin-bottom:16px;">
            <button type="button" class="back-button secondary" onclick="goBackSafely();"><i class="fas fa-arrow-left"></i> Back</button>
        </div>
        <h1 style="font-size:22px; font-weight:700; margin-bottom:16px;">Search Projects</h1>
        <form action="search.php" method="GET" class="big-search">
            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search by title, keyword or abstract…" autofocus>
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
        <div class="chips">
            <?php foreach (['AI','blockchain','cloud','IoT','cybersecurity','data science','machine learning'] as $kw): ?>
                <a href="search.php?q=<?php echo urlencode($kw); ?>" class="chip"><?php echo $kw; ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="results-area">
    <div class="container">
        <?php if ($q): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #e2e8f0;">
                <h2 style="font-size:16px; font-weight:700;">Results for "<?php echo htmlspecialchars($q); ?>"</h2>
                <span class="text-muted text-sm"><?php echo $total_projects; ?> found</span>
            </div>
            <?php if (empty($projects)): ?>
                <div class="no-result">
                    <i class="fas fa-search"></i>
                    <h3>No results found</h3>
                    <p>Try different keywords or <a href="browse.php" class="text-blue">browse all projects</a>.</p>
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
                            <span><i class="fas fa-eye"></i> <?php echo $p['view_count']; ?></span>
                            <span style="margin-left:auto; color:#004AAD; font-weight:600;">View &rarr;</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($q && $total_pages > 1): ?>
                <nav aria-label="Search result pages" style="display:flex; justify-content:center; gap:8px; margin-top:24px; flex-wrap:wrap;">
                    <?php for ($page_number = 1; $page_number <= $total_pages; $page_number++): ?>
                        <a href="search.php?<?php echo http_build_query(['q' => $q, 'page' => $page_number]); ?>" class="btn <?php echo $page_number === $page ? 'btn-primary' : 'btn-outline'; ?> btn-sm" aria-current="<?php echo $page_number === $page ? 'page' : 'false'; ?>">
                            <?php echo $page_number; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-result">
                <i class="fas fa-lightbulb"></i>
                <h3>What are you looking for?</h3>
                <p>Enter a keyword above, or click one of the suggestions.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
