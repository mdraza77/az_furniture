<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_data']) && 
           $_SESSION['admin_data']['type'] === 'admin';
}

$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'login.php') {
    if (!isAdminLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: /Az Furniture/Admin/authentication/login.php");
        exit();
    }
}

$admin = $_SESSION['admin_data'] ?? [
    'id' => null,
    'name' => null,
    'email' => null,
    'role' => null
];
?>