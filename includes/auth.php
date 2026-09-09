<?php
/**
 * Authentication helpers.
 */

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        $next = $_SERVER['REQUEST_URI'] ?? '';
        if ($next !== '') {
            safe_redirect('/login.php?next=' . rawurlencode($next));
        }
        safe_redirect('/login.php');
    }
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['dept_id'] = $user['department_id'];
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function is_admin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_role(string $role): void {
    if (!is_logged_in()) {
        safe_redirect('/login.php');
    }
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        http_response_code(403);
        echo 'Access denied.';
        exit();
    }
}
