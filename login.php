<?php
require_once 'config/db.php';
require_once 'includes/csrf.php';
$current_page = 'login';
$page_title   = 'Login';
$error = '';
if ($_SERVER['REQUEST_METHOD']=='POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid or expired session. Please try again.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? AND status='active'");
        $stmt->execute([trim($_POST['username'])]);
        $user = $stmt->fetch();
        if ($user && password_verify($_POST['password'], $user['password_hash'])) {
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['dept_id']  = $user['department_id'];
            header("Location: index.php"); exit();
        } else { $error = "Invalid username or password."; }
    }
}
require_once 'includes/header.php';
?>
<style>
.auth-wrap {
    min-height: calc(100vh - 60px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
    background: linear-gradient(rgba(5, 27, 54, 0.80), rgba(7, 38, 75, 0.88)), url('gt_pic/library.jpg') center/cover no-repeat;
}
.auth-box {
    background: rgba(255, 255, 255, 0.98);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 14px;
    width: 100%;
    max-width: 420px;
    padding: 36px;
    box-shadow: 0 18px 48px rgba(2, 12, 24, 0.26);
}
.auth-logo { text-align: center; margin-bottom: 24px; }
.auth-logo img { height: 50px; border-radius: 6px; }
.auth-logo h2 { font-size: 18px; font-weight: 700; margin-top: 10px; color: #1e293b; }
.auth-logo p  { font-size: 13px; color: #64748b; margin-top: 4px; }
hr.divider { border: none; border-top: 1px solid #f1f5f9; margin: 20px 0; }
.link-row { text-align: center; font-size: 13px; color: #64748b; margin-top: 16px; }
.link-row a { color: #004AAD; font-weight: 600; }
</style>

<div class="auth-wrap">
    <div class="auth-box">
        <div style="margin-bottom:16px; text-align:left;">
            <button type="button" class="back-button auth" onclick="goBackSafely();"><i class="fas fa-arrow-left"></i> Back</button>
        </div>
        <div class="auth-logo">
            <img src="gt_pic/logo.jpg" alt="GCTU Logo">
            <h2>GCTU Project Library</h2>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter your username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
            </div>
            <div style="text-align:right; margin-top:-10px; margin-bottom:18px;">
                <span style="font-size:12px; color:#64748b;">Forgot your password? Contact the library administrator.</span>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <hr class="divider">
        <p class="link-row">Don't have an account? <a href="register.php">Register here</a></p>
        <p style="text-align:center; font-size:11px; color:#94a3b8; margin-top:20px;">
            Demo: admin / admin123 &nbsp;&bull;&nbsp; student / student123
        </p>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
