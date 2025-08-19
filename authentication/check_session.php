<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_data']) && 
           $_SESSION['user_data']['type'] === 'user';
}

function redirectToLogin() {
    if(!isLoggedIn()) {
        header("Location: authentication/login.php");
        exit();
    }
}

function redirectIfLoggedIn() {
    if(isLoggedIn()) {
        header("Location: ../index.php");
        exit();
    }
}
?>