<?php
session_start();

// Remove only user-specific session data
unset($_SESSION['user_data']);

// Destroy the session if no other data exists
if (empty($_SESSION)) {
    session_destroy();
}

header("Location: login.php");
exit();
?>