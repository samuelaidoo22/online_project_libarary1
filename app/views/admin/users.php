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
.badge-active { background:#dcfce7; color:#15803d; }
.badge-suspended { background:#fee2e2; color:#991b1b; }
.badge-admin { background:#dbeafe; color:#1d4ed8; }
.badge-lecturer { background:#ede9fe; color:#7c3aed; }
.badge-student { background:#fef3c7; color:#92400e; }
.btn-sm { padding:8px 12px; border-radius:10px; font-size:12px; }
.btn-primary { background:#004AAD; color:#fff; border:none; }
.btn-outline { background:#fff; color:#475569; border:1px solid #cbd5e1; }
.btn-danger { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
.btn-success { background:#dcfce7; color:#15803d; border:1px solid #86efac; }
.select-role { padding:8px 10px; border:1px solid #cbd5e1; border-radius:10px; background:#fff; }
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
                <h1 style="margin:0; font-size:24px;">User Management</h1>
                <p class="text-muted" style="margin:4px 0 0; color:#64748b;">Review users, change roles, and suspend access.</p>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" style="text-align:center; padding:24px; color:#64748b;">No users found.</td></tr>
                    <?php else: foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['department_name'] ?? '—'); ?></td>
                            <td>
                                <form method="POST" style="display:inline-block; min-width:160px;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="action" value="update_role">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <select name="role" class="select-role" <?php echo $user['role'] === 'admin' ? 'disabled' : ''; ?>>
                                        <?php foreach (['student','lecturer','admin'] as $role): ?>
                                            <option value="<?php echo $role; ?>" <?php echo $user['role'] === $role ? 'selected' : ''; ?>><?php echo ucfirst($role); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <button type="submit" class="btn btn-primary btn-sm" style="margin-left:8px;">Save</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td>
                                <span class="badge <?php echo $user['status'] === 'active' ? 'badge-active' : 'badge-suspended'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="POST" style="display:inline-block; margin:0;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                        <button type="submit" class="btn <?php echo $user['status'] === 'active' ? 'btn-danger' : 'btn-success'; ?> btn-sm">
                                            <?php echo $user['status'] === 'active' ? 'Suspend' : 'Activate'; ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-admin">Protected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
