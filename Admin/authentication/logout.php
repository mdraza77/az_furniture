<?php
session_start();

// Remove only admin-specific session data
unset($_SESSION['admin_data']);

// Destroy the session if no other data exists
if (empty($_SESSION)) {
    session_destroy();
}

header("Location: /Az Furniture/Admin/authentication/login.php");
exit();
?>