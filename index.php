<?php
require_once 'config/db.php';
$current_page = 'home';
$page_title   = 'Home';

$stats = [
    'projects'    => $pdo->query("SELECT COUNT(*) FROM projects WHERE approval_status='approved'")->fetchColumn(),
    'students'    => $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
    'departments' => $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn(),
];
$recent = $pdo->query("SELECT p.*, d.department_name FROM projects p JOIN departments d ON p.department_id=d.department_id WHERE p.approval_status='approved' ORDER BY p.upload_date DESC LIMIT 5")->fetchAll();
$depts  = $pdo->query("SELECT * FROM departments")->fetchAll();

require_once 'includes/header.php';
?>

<style>
.hero {
    background: #004AAD;
    background-image: linear-gradient(135deg, rgba(6, 30, 66, 0.92), rgba(11, 61, 145, 0.82)), url('gt_pic/gatehouse.jpg');
    background-size: cover;
    background-position: center;
    position: relative;
    padding: 72px 0;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.hero::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(90deg, rgba(6,30,66,0.88) 0%, rgba(11,61,145,0.72) 55%, rgba(11,61,145,0.2) 100%);
}
.hero-inner { position: relative; z-index: 1; }
.hero h1 { font-size: 34px; font-weight: 700; color: #fff; margin-bottom: 12px; letter-spacing: -0.02em; }
.hero p  { color: rgba(255,255,255,0.82); font-size: 16px; margin-bottom: 28px; max-width: 560px; }

.search-bar {
    display: flex;
    background: rgba(255,255,255,0.96);
    border-radius: 10px;
    overflow: hidden;
    max-width: 580px;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
    border: 1px solid rgba(255,255,255,0.15);
}
.search-bar input {
    flex: 1; border: none; padding: 15px 18px;
    font-size: 14px; font-family: inherit; color: #1e293b;
    background: transparent;
}
.search-bar input:focus { outline: none; }
.search-bar button {
    background: #FCD12A; color: #1e293b;
    border: none; padding: 14px 24px;
    font-weight: 700; font-size: 14px;
    cursor: pointer; white-space: nowrap;
    display: flex; align-items: center; gap: 6px;
}
.search-bar button:hover { background: #f0c520; }

.hero-stats {
    display: flex; gap: 40px; margin-top: 32px;
}
.hero-stat .num { font-size: 26px; font-weight: 700; color: #FCD12A; }
.hero-stat .lbl { font-size: 12px; color: rgba(255,255,255,0.65); margin-top: 2px; }

/* Content area */
.content-area { padding: 48px 0 64px; }

.project-list { display: flex; flex-direction: column; gap: 12px; }
.project-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 18px 20px;
    border: 1px solid #dfeaf3;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 12px 20px rgba(15, 23, 42, 0.02);
    transition: box-shadow 0.2s, transform 0.2s, border-color 0.2s;
}
.project-row:hover { 
    background: #fff; 
    box-shadow: 0 18px 26px rgba(11, 61, 145, 0.08); 
    transform: translateY(-2px);
    border-color: #93c5fd;
}

.proj-title { font-weight: 600; color: #1e293b; font-size: 14px; margin-bottom: 3px; }
.proj-meta  { font-size: 12px; color: #64748b; display: flex; gap: 12px; }

.dept-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-top: 3px solid #004AAD;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    text-decoration: none;
    color: inherit;
    display: block;
    transition: box-shadow 0.2s, transform 0.2s;
}
.dept-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-2px); }
.dept-card i { font-size: 28px; color: #004AAD; margin-bottom: 10px; display: block; }
.dept-card h3 { font-size: 14px; font-weight: 600; margin-bottom: 4px; }
.dept-card .count { font-size: 12px; color: #64748b; }

.section-hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.section-hdr h2 { font-size: 20px; font-weight: 700; }
</style>

<!-- Hero -->
<div class="hero">
    <div class="container hero-inner">
        <h1>GCTU Online Project Library</h1>
        <p>Discover and access student research projects, dissertations and theses from all departments at Ghana Communication Technology University.</p>

        <form action="search.php" method="GET" class="search-bar">
            <input type="text" name="q" placeholder="Search by title, keyword or department…" autocomplete="off">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>

        <div class="hero-stats">
            <div class="hero-stat">
                <div class="num"><?php echo number_format($stats['projects']); ?>+</div>
                <div class="lbl">Projects</div>
            </div>
            <div class="hero-stat">
                <div class="num"><?php echo number_format($stats['students']); ?>+</div>
                <div class="lbl">Students</div>
            </div>
            <div class="hero-stat">
                <div class="num"><?php echo $stats['departments']; ?></div>
                <div class="lbl">Departments</div>
            </div>
            <div class="hero-stat">
                <div class="num">Free</div>
                <div class="lbl">Open Access</div>
            </div>
        </div>
    </div>
</div>

<!-- Content -->
<div class="content-area">
    <div class="container">
        <div class="responsive-grid" style="display: grid; grid-template-columns: 1fr 340px; gap: 32px; align-items: start;">

            <!-- Recent Projects -->
            <div>
                <div class="section-hdr">
                    <h2>Recent Projects</h2>
                    <a href="browse.php" class="btn btn-outline btn-sm">View All &rarr;</a>
                </div>

                <div class="project-list">
                    <?php if (empty($recent)): ?>
                        <div class="project-row">
                            <span class="text-muted">No projects available yet.</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent as $p): ?>
                            <a href="project.php?id=<?php echo $p['project_id']; ?>" style="text-decoration:none; color:inherit; display:block;">
                                <div class="project-row">
                                    <div>
                                        <div class="proj-title"><?php echo htmlspecialchars($p['title']); ?></div>
                                        <div class="proj-meta">
                                            <span><i class="fas fa-building" style="color:#004AAD;"></i> <?php echo htmlspecialchars($p['department_name']); ?></span>
                                            <span><i class="fas fa-calendar-alt"></i> <?php echo date('M Y', strtotime($p['upload_date'])); ?></span>
                                            <span><i class="fas fa-eye"></i> <?php echo $p['view_count']; ?> views</span>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right" style="color:#cbd5e1; font-size:12px; flex-shrink:0;"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Quick Info -->
                <div class="card mb-3">
                    <div style="font-size:15px; font-weight:700; margin-bottom:14px; color:#004AAD;">
                        <i class="fas fa-info-circle"></i> &nbsp;About This Library
                    </div>
                    <p class="text-muted" style="font-size:13px; line-height:1.7;">
                        The GCTU Online Project Library is a digital repository for student final-year projects, dissertations and theses. Browse, search and download verified academic research.
                    </p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div style="margin-top:16px; display:flex; gap:8px;">
                            <a href="register.php" class="btn btn-primary btn-sm">Register</a>
                            <a href="login.php" class="btn btn-outline btn-sm">Login</a>
                        </div>
                    <?php else: ?>
                        <div style="margin-top:16px;">
                            <a href="upload.php" class="btn btn-primary btn-sm"><i class="fas fa-upload"></i> Upload Project</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Stats box -->
                <a href="browse.php" class="card card-link" aria-label="Browse all projects">
                    <div style="font-size:15px; font-weight:700; margin-bottom:14px; color:#004AAD;">
                        <i class="fas fa-chart-bar"></i> &nbsp;Repository Stats
                    </div>
                    <table style="width:100%; font-size:13px;">
                        <tr><td style="padding:6px 0; color:#64748b;">Total Projects</td><td style="text-align:right; font-weight:600;"><?php echo $stats['projects']; ?></td></tr>
                        <tr><td style="padding:6px 0; color:#64748b;">Registered Students</td><td style="text-align:right; font-weight:600;"><?php echo $stats['students']; ?></td></tr>
                        <tr><td style="padding:6px 0; color:#64748b;">Departments</td><td style="text-align:right; font-weight:600;"><?php echo $stats['departments']; ?></td></tr>
                    </table>
                    <div style="margin-top:14px; color:#004AAD; font-size:12px; font-weight:700;">Explore the repository <i class="fas fa-arrow-right"></i></div>
                </a>
            </div>
        </div>

        <!-- Departments -->
        <div style="margin-top:48px;">
            <div class="section-hdr">
                <h2>Browse by Department</h2>
            </div>
            <div class="responsive-grid" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:16px;">
                <?php
                $icons = ['fa-laptop-code','fa-network-wired','fa-briefcase','fa-microchip','fa-square-root-alt','fa-comments'];
                foreach ($depts as $i => $d):
                    $cnt = $pdo->query("SELECT COUNT(*) FROM projects WHERE department_id={$d['department_id']} AND approval_status='approved'")->fetchColumn();
                ?>
                    <a href="browse.php?dept=<?php echo $d['department_id']; ?>" class="dept-card">
                        <i class="fas <?php echo $icons[$i % count($icons)]; ?>"></i>
                        <h3><?php echo htmlspecialchars($d['department_name']); ?></h3>
                        <div class="count"><?php echo $cnt; ?> project<?php echo $cnt!=1?'s':''; ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
