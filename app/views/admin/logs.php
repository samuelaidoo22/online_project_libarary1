<style>
.admin-wrap { display:grid; grid-template-columns:220px 1fr; min-height:calc(100vh - 60px); gap:24px; }
.admin-sidebar { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:20px; }
.sidebar-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#64748b; margin-bottom:10px; }
.sidebar-link { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:12px; color:#475569; text-decoration:none; font-weight:600; transition:background .15s, color .15s; }
.sidebar-link.active, .sidebar-link:hover { background:#eff6ff; color:#004AAD; }
.panel { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:22px; }
.panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; font-weight:700; }
.table { width:100%; border-collapse:collapse; font-size:13px; }
.table th, .table td { padding:14px; border-bottom:1px solid #eef2ff; }
.table th { text-align:left; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }
.table td { color:#1e293b; }
.badge { display:inline-block; padding:6px 12px; border-radius:999px; font-size:12px; font-weight:700; }
.badge-download { background:#dbeafe; color:#1d4ed8; }
.badge-view { background:#f0f9ff; color:#2563eb; }
.filter-row { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:18px; align-items:center; }
.filter-input, .filter-select { border:1px solid #cbd5e1; border-radius:10px; padding:10px 12px; background:#fff; color:#1e293b; font-size:13px; }
.btn-primary { background:#004AAD; color:#fff; border:none; border-radius:10px; padding:10px 16px; cursor:pointer; }
@media (max-width: 880px) { .admin-wrap { grid-template-columns:1fr; } }
</style>

<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="sidebar-label">Overview</div>
        <a href="dashboard.php" class="sidebar-link <?php echo isset($activeMenu) && $activeMenu === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <div class="sidebar-label">Content</div>
        <a href="dashboard.php" class="sidebar-link"><i class="fas fa-clock"></i> Pending Approvals</a>
        <a href="../browse.php" class="sidebar-link"><i class="fas fa-book-open"></i> All Projects</a>
        <div class="sidebar-label">Users</div>
        <a href="users.php" class="sidebar-link <?php echo isset($activeMenu) && $activeMenu === 'users' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Manage Users</a>
        <div class="sidebar-label">System</div>
        <a href="logs.php" class="sidebar-link <?php echo isset($activeMenu) && $activeMenu === 'logs' ? 'active' : ''; ?>"><i class="fas fa-scroll"></i> Activity Logs</a>
        <a href="../logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </aside>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h1 style="margin:0; font-size:24px;">Activity Logs</h1>
                <p class="text-muted" style="margin:4px 0 0; color:#64748b;">Review recent user activity, downloads, and project views.</p>
            </div>
        </div>

        <form method="GET" class="filter-row">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search ?? ''); ?>" class="filter-input" placeholder="Search user or project...">
            <select name="type" class="filter-select">
                <option value="">All activity</option>
                <option value="view_abstract" <?php echo ($type === 'view_abstract') ? 'selected' : ''; ?>>Viewed Abstract</option>
                <option value="download_full" <?php echo ($type === 'download_full') ? 'selected' : ''; ?>>Downloaded Full</option>
            </select>
            <button type="submit" class="btn-primary">Filter</button>
        </form>

        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Project</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="4" style="text-align:center; padding:24px; color:#64748b;">No activity logs found.</td></tr>
                    <?php else: foreach ($logs as $log): ?>
                        <?php $download = $log['access_type'] === 'download_full'; ?>
                        <tr>
                            <td><?php echo date('d M Y H:i', strtotime($log['access_date'])); ?></td>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['title']); ?></td>
                            <td>
                                <span class="badge <?php echo $download ? 'badge-download' : 'badge-view'; ?>">
                                    <?php echo $download ? 'Downloaded Full PDF' : 'Viewed Abstract'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
