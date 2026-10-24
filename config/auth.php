<?php
/**
 * GlobeTrek Adventures - Authentication & RBAC System
 * Supports login by username OR email address.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function current_user() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id'        => $_SESSION['user_id'],
            'full_name' => $_SESSION['full_name'] ?? 'User',
            'email'     => $_SESSION['email'] ?? '',
            'username'  => $_SESSION['username'] ?? '',
            'role'      => $_SESSION['role'] ?? 'customer',
            'phone'     => $_SESSION['phone'] ?? '',
            'avatar'    => $_SESSION['avatar'] ?? ''
        ];
    }
    return null;
}

function is_logged_in() { return isset($_SESSION['user_id']); }
function is_customer()  { return is_logged_in() && ($_SESSION['role'] === 'customer'); }
function is_staff()     { return is_logged_in() && in_array($_SESSION['role'], ['staff', 'admin']); }
function is_admin()     { return is_logged_in() && ($_SESSION['role'] === 'admin'); }

function require_login($requiredRole = null) {
    if (!is_logged_in()) {
        set_flash('warning', 'Please login to access this section.');
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    if ($requiredRole !== null) {
        $userRole = $_SESSION['role'] ?? 'customer';
        if ($requiredRole === 'admin' && $userRole !== 'admin') {
            set_flash('danger', 'Access denied. Administrator privileges required.');
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
        if ($requiredRole === 'staff' && !in_array($userRole, ['staff', 'admin'])) {
            set_flash('danger', 'Access denied. Staff privileges required.');
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }
}

/**
 * Login by username OR email address with password verification.
 */
function login_user($identifier, $password) {
    // Support username OR email login
    $user = db_fetch_one(
        "SELECT * FROM users WHERE (email = ? OR username = ?) AND is_active = 1",
        [$identifier, $identifier]
    );

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['username']  = $user['username'] ?? '';
        $_SESSION['role']      = $user['role'];
        $_SESSION['phone']     = $user['phone'];
        $_SESSION['avatar']    = $user['avatar'] ?? '';

        log_audit_action($user['id'], 'LOGIN', "User {$user['email']} logged in successfully");

        return ['success' => true, 'user' => $user];
    }

    return ['success' => false, 'message' => 'Invalid credentials. Please check your username and password.'];
}

/**
 * Registers a new customer account.
 */
function register_customer($fullName, $email, $password, $phone = '', $address = '') {
    $existing = db_fetch_one("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        return ['success' => false, 'message' => 'An account with this email address already exists.'];
    }

    $hash   = password_hash($password, PASSWORD_DEFAULT);
    $userId = db_insert(
        "INSERT INTO users (full_name, email, password_hash, role, phone, address, is_active, created_at)
         VALUES (?, ?, ?, 'customer', ?, ?, 1, datetime('now'))",
        [$fullName, $email, $hash, $phone, $address]
    );

    log_audit_action($userId, 'REGISTER', "New customer registration: {$email}");
    return ['success' => true, 'user_id' => $userId];
}

/**
 * Logs user out and clears session.
 */
function logout_user() {
    if (isset($_SESSION['user_id'])) {
        log_audit_action($_SESSION['user_id'], 'LOGOUT', "User logged out");
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}
