<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['admin_data']['id'];
    $full_name = trim($_POST['full_name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate full name
    if (empty($full_name)) {
        $_SESSION['error'] = "Full name is required";
        header("Location: settings.php");
        exit();
    }

    // Get current admin data
    $stmt = $conn->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // Initialize update query
    $update_query = "UPDATE admin_users SET full_name = ?";
    $types = "s";
    $params = [$full_name];

    // Handle password update if requested
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $_SESSION['error'] = "Current password is required to change password";
            header("Location: settings.php");
            exit();
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "New passwords do not match";
            header("Location: settings.php");
            exit();
        }

        // Hash the current password input with MD5 to match database format
        $current_password_hash = md5($current_password);
        
        // Get the stored password hash from database
        $check_pass_query = "SELECT password_hash FROM admin_users WHERE id = ?";
        $check_stmt = $conn->prepare($check_pass_query);
        $check_stmt->bind_param("i", $admin_id);
        $check_stmt->execute();
        $pass_result = $check_stmt->get_result();
        $stored_pass = $pass_result->fetch_assoc();

        // Compare the hashed current password with stored hash
        if ($current_password_hash !== $stored_pass['password_hash']) {
            $_SESSION['error'] = "Current password is incorrect";
            header("Location: settings.php");
            exit();
        }

        // If verification successful, hash the new password with MD5
        $password_hash = md5($new_password);
        $update_query .= ", password_hash = ?";
        $types .= "s";
        $params[] = $password_hash;
    }

    $update_query .= " WHERE id = ?";
    $types .= "i";
    $params[] = $admin_id;

    // Prepare and execute the update
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully";
        // Update session data
        $_SESSION['admin_data']['name'] = $full_name;
    } else {
        $_SESSION['error'] = "Error updating profile: " . $conn->error;
    }

    header("Location: settings.php");
    exit();
}
