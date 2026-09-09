<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Admin;

class AdminController extends Controller
{
    public function dashboard(\PDO $pdo): void
    {
        require_role('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['project_id'], $_POST['csrf_token'])) {
            if (verify_csrf_token(request_post('csrf_token'))) {
                $status = $_POST['action'] === 'approve' ? 'approved' : 'rejected';
                $stmt = $pdo->prepare("UPDATE projects SET approval_status=? WHERE project_id=?");
                $stmt->execute([$status, filter_int($_POST['project_id'] ?? 0)]);
            }
            safe_redirect('/admin/dashboard.php');
        }

        $page_title = 'Admin Dashboard';
        $current_page = 'admin';
        $activeMenu = 'dashboard';
        $stats = Admin::stats($pdo);
        $pending = Admin::pendingProjects($pdo, 10);
        $logs = Admin::recentLogs($pdo, 6);
        $depts = Admin::departmentProjectCounts($pdo);
        $max = max(array_column($depts, 'cnt') ?: [1]);

        $this->render('admin/dashboard', compact('page_title', 'current_page', 'activeMenu', 'stats', 'pending', 'logs', 'depts', 'max'));
    }

    public function users(\PDO $pdo): void
    {
        require_role('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'], $_POST['csrf_token'])) {
            if (verify_csrf_token(request_post('csrf_token'))) {
                $userId = filter_int($_POST['user_id'] ?? 0);
                if ($_POST['action'] === 'toggle_status') {
                    $currentStatus = $_POST['current_status'] ?? 'active';
                    $newStatus = $currentStatus === 'active' ? 'suspended' : 'active';
                    Admin::updateUserStatus($pdo, $userId, $newStatus);
                }
                if ($_POST['action'] === 'update_role' && isset($_POST['role'])) {
                    Admin::updateUserRole($pdo, $userId, $_POST['role']);
                }
            }
            safe_redirect('/admin/users.php');
        }

        $page_title = 'User Management';
        $current_page = 'admin';
        $activeMenu = 'users';
        $users = Admin::users($pdo);

        $this->render('admin/users', compact('page_title', 'current_page', 'activeMenu', 'users'));
    }

    public function logs(\PDO $pdo): void
    {
        require_role('admin');

        $search = request_get('q');
        $type = request_get('type');
        $allowedTypes = ['view_abstract', 'download_full'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = null;
        }

        $page_title = 'Activity Logs';
        $current_page = 'admin';
        $activeMenu = 'logs';
        $logs = Admin::activityLogs($pdo, 100, $search, $type);

        $this->render('admin/logs', compact('page_title', 'current_page', 'activeMenu', 'logs', 'search', 'type'));
    }
}
