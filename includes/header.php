<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | GCTU Project Library' : 'GCTU Online Project Library'; ?></title>
    <script>
        function goBackSafely() {
            if (window.history.length > 1) {
                window.history.back();
                return;
            }
            window.location.href = 'index.php';
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #edf3f8;
            color: #1e293b;
            font-size: 14px;
            line-height: 1.6;
        }

        a { color: inherit; text-decoration: none; }

        /* ---- NAV ---- */
        nav {
            background: rgba(255,255,255,0.96);
            border-bottom: 2px solid #0b3d91;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
        }
        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 16px;
            color: #004AAD;
        }
        .nav-logo img { height: 36px; border-radius: 4px; }
        .nav-logo span { color: #1e293b; }
        .nav-logo em { font-style: normal; color: #004AAD; }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }
        .nav-links a {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            transition: background 0.15s, color 0.15s;
        }
        .nav-links a:hover, .nav-links a.active {
            background: #f0f4f8;
            color: #004AAD;
        }
        .nav-links a.nav-btn-primary {
            background: #004AAD;
            color: #fff;
            font-weight: 600;
        }
        .nav-links a.nav-btn-primary:hover {
            background: #003a8c;
        }
        .nav-links a.nav-btn-outline {
            border: 1px solid #cbd5e1;
            color: #475569;
        }
        .nav-links a.nav-btn-outline:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        main { padding-top: 0; min-height: calc(100vh - 120px); }

        /* ---- CONTAINER ---- */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

        /* ---- PAGE BANNER ---- */
        .page-banner {
            background: linear-gradient(135deg, #0b3d91 0%, #123d72 42%, #081e38 100%);
            padding: 40px 0 32px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .page-banner h1 { font-size: 28px; font-weight: 700; color: #fff; }
        .page-banner p  { color: rgba(255,255,255,0.78); margin-top: 6px; font-size: 14px; }
        .page-banner .breadcrumb {
            font-size: 12px; color: rgba(255,255,255,0.58);
            margin-bottom: 10px;
        }
        .page-banner .breadcrumb a { color: rgba(255,255,255,0.82); }
        .page-banner .breadcrumb a:hover { color: #FCD12A; }

        /* ---- CARDS ---- */
        .card {
            background: #fff;
            border: 1px solid #dfeaf3;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.03);
        }
        .card:hover { box-shadow: 0 14px 24px rgba(15, 23, 42, 0.06); }
        .card-link { display: block; color: inherit; text-decoration: none; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-link:hover { transform: translateY(-2px); }
        .card-link:focus-visible { outline: 3px solid rgba(0,74,173,0.25); outline-offset: 3px; }

        /* ---- BACK BUTTON ---- */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
            text-decoration: none;
        }
        .back-button:hover {
            background: rgba(255,255,255,0.14);
            border-color: rgba(255,255,255,0.55);
            transform: translateX(-1px);
        }
        .back-button.secondary {
            background: #fff;
            border-color: #dfe7f1;
            color: #475569;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        }
        .back-button.secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .back-button.auth {
            background: rgba(0, 74, 173, 0.08);
            border-color: rgba(0, 74, 173, 0.18);
            color: #004AAD;
        }
        .back-button.auth:hover {
            background: rgba(0, 74, 173, 0.12);
            border-color: rgba(0, 74, 173, 0.28);
        }

        /* ---- BUTTONS ---- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.15s;
            text-decoration: none;
        }
        .btn-primary { background: #004AAD; color: #fff; }
        .btn-primary:hover { background: #003a8c; }
        .btn-gold    { background: #FCD12A; color: #1e293b; }
        .btn-gold:hover { background: #f0c520; }
        .btn-outline { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
        .btn-outline:hover { background: #f8fafc; }
        .btn-danger  { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .btn-danger:hover  { background: #fecaca; }
        .btn-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .btn-success:hover { background: #bbf7d0; }
        .btn-sm { padding: 6px 14px; font-size: 12px; }

        /* ---- FORM ELEMENTS ---- */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            background: #fff;
            color: #1e293b;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #004AAD;
            box-shadow: 0 0 0 3px rgba(0,74,173,0.1);
        }
        textarea.form-control { resize: vertical; min-height: 120px; line-height: 1.6; }
        select.form-control option { background: #fff; }

        /* ---- BADGE ---- */
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 4px;
        }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-gold   { background: #fef9c3; color: #854d0e; }
        .badge-green  { background: #dcfce7; color: #15803d; }
        .badge-red    { background: #fee2e2; color: #b91c1c; }
        .badge-gray   { background: #f1f5f9; color: #475569; }

        /* ---- TABLE ---- */
        .table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table th {
            text-align: left;
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .table td {
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #374151;
            vertical-align: middle;
        }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: #f8fafc; }

        /* ---- UTILITY ---- */
        .grid { display: grid; gap: 20px; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        .flex { display: flex; align-items: center; }
        .flex-between { display: flex; align-items: center; justify-content: space-between; }
        .mt-1 { margin-top: 8px; }  .mt-2 { margin-top: 16px; } .mt-3 { margin-top: 24px; }
        .mb-1 { margin-bottom: 8px; } .mb-2 { margin-bottom: 16px; } .mb-3 { margin-bottom: 24px; }
        .text-muted { color: #64748b; }
        .text-blue  { color: #004AAD; }
        .text-sm    { font-size: 12px; }
        .fw-600     { font-weight: 600; }
        .fw-700     { font-weight: 700; }

        /* Alert */
        .alert {
            padding: 12px 16px; border-radius: 8px;
            font-size: 13px; margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-error   { background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; }
        .alert-success { background: #dcfce7; border: 1px solid #86efac; color: #15803d; }
        .alert-info    { background: #dbeafe; border: 1px solid #93c5fd; color: #1d4ed8; }
        .alert i { flex-shrink: 0; margin-top: 2px; }

        /* Section title */
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            padding-bottom: 12px;
            border-bottom: 2px solid #004AAD;
            margin-bottom: 20px;
            display: inline-block;
        }

        /* ---- MOBILE RESPONSIVENESS ---- */
        @media (max-width: 768px) {
            /* Navigation */
            .nav-inner { flex-direction: column; height: auto; padding: 12px 20px; gap: 12px; }
            .nav-links { flex-wrap: wrap; justify-content: center; gap: 8px; }
            .nav-links a { padding: 6px 10px; font-size: 13px; }

            /* Grids */
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr !important; }
            
            /* Typography & Layout */
            .page-banner { padding: 30px 0 20px; }
            .page-banner h1 { font-size: 24px; }
            
            /* Responsive Utility for Inline Styles */
            .responsive-grid { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body>
<nav>
    <div class="nav-inner">
        <a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../index.php' : 'index.php'; ?>" class="nav-logo">
            <img src="<?php echo (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../gt_pic/logo.jpg' : 'gt_pic/logo.jpg'; ?>" alt="GCTU">
            <span>GCTU <em>Project Library</em></span>
        </a>
        <ul class="nav-links">
            <?php $base = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : ''; ?>
            <li><a href="<?php echo $base; ?>index.php" class="<?php echo ($current_page=='home') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php echo $base; ?>browse.php" class="<?php echo ($current_page=='browse') ? 'active' : ''; ?>">Browse</a></li>
            <li><a href="<?php echo $base; ?>search.php" class="<?php echo ($current_page=='search') ? 'active' : ''; ?>">Search</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="<?php echo $base; ?>upload.php" class="<?php echo ($current_page=='upload') ? 'active' : ''; ?>">Upload</a></li>
                <?php if (($_SESSION['role'] ?? '') !== 'admin'): ?>
                    <?php
                    $unreadNotifications = 0;
                    try {
                        $notificationStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                        $notificationStmt->execute([(int)$_SESSION['user_id']]);
                        $unreadNotifications = (int)$notificationStmt->fetchColumn();
                    } catch (Throwable $e) {
                        $unreadNotifications = 0;
                    }
                    ?>
                    <li><a href="<?php echo $base; ?>notifications.php" class="<?php echo ($current_page=='notifications') ? 'active' : ''; ?>">Notifications<?php echo $unreadNotifications ? ' (' . $unreadNotifications . ')' : ''; ?></a></li>
                <?php endif; ?>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li><a href="<?php echo $base; ?>admin/dashboard.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="<?php echo $base; ?>logout.php" class="nav-btn-outline">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo $base; ?>register.php">Register</a></li>
                <li><a href="<?php echo $base; ?>login.php" class="nav-btn-primary">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<main>
