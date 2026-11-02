<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';

function login($username, $password) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 1 LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_name'] = $user['full_name'];
        $_SESSION['admin_role'] = $user['role'];

        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        return true;
    }
    return false;
}

function requireAdmin() {
    if (!isLoggedIn()) {
        redirect('../admin/login.php');
    }
}

function requireRole($roles) {
    if (!isLoggedIn()) {
        redirect('../admin/login.php');
    }
    if (!in_array($_SESSION['admin_role'], (array)$roles)) {
        die('Access denied.');
    }
}

function logout() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_role']);
    session_destroy();
    redirect('login.php');
}
