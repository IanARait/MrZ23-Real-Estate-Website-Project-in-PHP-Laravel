<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';

function customerLogin($email, $password) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ? AND status = 1 LIMIT 1");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if ($customer && password_verify($password, $customer['password'])) {
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_email'] = $customer['email'];
        $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
        return true;
    }
    return false;
}

function requireCustomer() {
    if (!isCustomerLoggedIn()) {
        redirect('login.php');
    }
}

function customerLogout() {
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_email']);
    unset($_SESSION['customer_name']);
    session_destroy();
    redirect('login.php');
}
