<style>
.admin-wrap { display:grid; grid-template-columns:220px 1fr; min-height:calc(100vh - 60px); gap:24px; }
.admin-sidebar { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:20px; }
.sidebar-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; color:#64748b; margin-bottom:10px; }
.sidebar-link { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:12px; color:#475569; text-decoration:none; font-weight:600; transition:background .15s, color .15s; }
.sidebar-link.active, .sidebar-link:hover { background:#eff6ff; color:#004AAD; }
.sidebar-badge { margin-left:auto; background:#fde68a; color:#92400e; font-size:11px; font-weight:700; border-radius:999px; padding:2px 8px; }
.admin-main { display:grid; gap:24px; }
.stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.stat-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:22px; transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
        .stat-card:hover { transform: translateY(-6px); box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08); border-color: #cbd5e1; }
.panel { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:22px; }
.panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; font-weight:700; }
.bar-chart { display:flex; align-items:flex-end; gap:12px; height:140px; }
.bar-col { text-align:center; }
.bar-fill { width:100%; border-radius:12px 12px 0 0; background:linear-gradient(180deg, #3b82f6, #2563eb); }
.bar-label { margin-top:8px; font-size:12px; color:#64748b; }
.log-row { display:flex; align-items:center; gap:12px; padding:14px 0; border-bottom:1px solid #f1f5f9; }
.log-row:last-child { border-bottom:none; }
.log-icon { width:34px; height:34px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:14px; }
.log-icon.dl { background:#dbeafe; color:#1d4ed8; }
.log-icon.vw { background:#f1f5f9; color:#475569; }
.table { width:100%; border-collapse:collapse; font-size:13px; }
.table th, .table td { text-align:left; padding:14px; border-bottom:1px solid #eef2ff; }
.table th { color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
.btn-sm { padding:8px 14px; border-radius:10px; font-size:12px; }
.btn-success { background:#dcfce7; color:#15803d; border:1px solid #86efac; }
.btn-danger { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
@media (max-width: 880px) { .admin-wrap { grid-template-columns:1fr; } .stat-grid { grid-template-columns:1fr 1fr; } }
@media (max-width: 640px) { .stat-grid { grid-template-columns:1fr; } }
</style>

<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="sidebar-label">Overview</div>
        <a href="dashboard.php" class="sidebar-link <?php echo isset($activeMenu) && $activeMenu === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <div class="sidebar-label">Content</div>
        <a href="dashboard.php" class="sidebar-link"><i class="fas fa-clock"></i> Pending Approvals <?php if ($stats['pending'] > 0): ?><span class="sidebar-badge"><?php echo $stats['pending']; ?></span><?php endif; ?></a>
        <a href="../browse.php" class="sidebar-link"><i class="fas fa-book-open"></i> All Projects</a>
        <div class="sidebar-label">Users</div>
        <a href="users.php" class="sidebar-link <?php echo isset($activeMenu) && $activeMenu === 'users' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Manage Users</a>
        <div class="sidebar-label">System</div>
        <a href="logs.php" class="sidebar-link <?php echo isset($activeMenu) && $activeMenu === 'logs' ? 'active' : ''; ?>"><i class="fas fa-scroll"></i> Activity Logs</a>
        <a href="../logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </aside>

    <div class="admin-main">
        <div class="panel" style="padding:18px 24px; display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <div>
                <h1 style="margin:0; font-size:24px;">Admin Dashboard</h1>
                <p class="text-muted" style="margin:4px 0 0; color:#64748b;"><?php echo date('l, d F Y'); ?></p>
            </div>
            <a href="../upload.php" class="btn btn-primary btn-sm" style="background:#004AAD; color:#fff;">Add Project</a>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="text-muted">Total Projects</div>
                <div style="font-size:28px; font-weight:700; margin-top:10px;"><?php echo $stats['total']; ?></div>
                <div class="text-muted" style="margin-top:8px;"><?php echo $stats['approved']; ?> approved</div>
            </div>
            <div class="stat-card">
                <div class="text-muted">Pending Review</div>
                <div style="font-size:28px; font-weight:700; margin-top:10px; color:#b45309;"><?php echo $stats['pending']; ?></div>
                <div class="text-muted" style="margin-top:8px;">Awaiting approval</div>
            </div>
            <div class="stat-card">
                <div class="text-muted">Registered Users</div>
                <div style="font-size:28px; font-weight:700; margin-top:10px;"><?php echo $stats['users']; ?></div>
                <div class="text-muted" style="margin-top:8px;"><?php echo $stats['students']; ?> students</div>
            </div>
            <div class="stat-card">
                <div class="text-muted">Total Downloads</div>
                <div style="font-size:28px; font-weight:700; margin-top:10px;"><?php echo $stats['downloads']; ?></div>
                <div class="text-muted" style="margin-top:8px;">Full PDF downloads</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 280px; gap:20px;">
            <div class="panel">
                <div class="panel-head">Projects by Department</div>
                <div class="bar-chart">
                    <?php foreach ($depts as $d): $h = $max > 0 ? round(($d['cnt'] / $max) * 100) : 5; ?>
                        <div class="bar-col">
                            <div class="bar-fill" style="height:<?php echo max($h, 5); ?>px;"></div>
                            <div class="bar-label"><?php echo htmlspecialchars($d['department_code']); ?></div>
                            <div style="font-size:11px; color:#64748b; margin-top:6px;"><?php echo $d['cnt']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="panel">
                <div class="panel-head">
                <div>Recent Activity</div>
                <a href="logs.php" class="btn btn-outline" style="font-size:12px; padding:8px 12px;">View all</a>
            </div>
                <?php if (empty($logs)): ?>
                    <p class="text-muted">No activity yet.</p>
                <?php else: ?>
                    <?php foreach ($logs as $l): $dl = $l['access_type'] === 'download_full'; ?>
                        <div class="log-row">
                            <div class="log-icon <?php echo $dl ? 'dl' : 'vw'; ?>">
                                <i class="fas <?php echo $dl ? 'fa-download' : 'fa-eye'; ?>"></i>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-weight:700; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($l['username']); ?> <?php echo $dl ? 'downloaded' : 'viewed'; ?></div>
                                <div style="font-size:12px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($l['title']); ?></div>
                            </div>
                            <div style="font-size:11px; color:#94a3b8; white-space:nowrap;"><?php echo date('H:i', strtotime($l['access_date'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">Pending Approvals <span style="font-size:12px; color:#64748b;"><?php echo $stats['pending']; ?> pending</span></div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Submitted By</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pending)): ?>
                        <tr><td colspan="5" style="text-align:center; padding:24px; color:#64748b;">No pending projects to review.</td></tr>
                    <?php else: foreach ($pending as $p): ?>
                        <tr>
                            <td style="max-width:240px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($p['title']); ?></td>
                            <td><?php echo htmlspecialchars($p['department_name']); ?></td>
                            <td><?php echo htmlspecialchars($p['username']); ?></td>
                            <td><?php echo date('d M Y', strtotime($p['upload_date'])); ?></td>
                            <td>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="project_id" value="<?php echo $p['project_id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="project_id" value="<?php echo $p['project_id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

