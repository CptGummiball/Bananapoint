<?php
// src/auth.php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function current_user(): ?array {
    if (!empty($_SESSION['user_id'])) {
        $stmt = db()->prepare("SELECT id, email, name, role, is_active FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $u = $stmt->fetch();
        if ($u && (int)$u['is_active'] === 1) return $u;
    }
    return null;
}

function require_login(): void {
    if (!current_user()) {
        header('Location: /index.php?route=login');
        exit;
    }
}

function is_admin(): bool {
    $u = current_user();
    return $u && $u['role'] === 'admin';
}
function is_manager(): bool {
    $u = current_user();
    return $u && ($u['role'] === 'manager' || $u['role'] === 'admin');
}

function login(string $email, string $password): bool {
    $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
