<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $slug = strtolower(str_replace(' ', '-', $name));
    
    try {
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $slug, $description, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            header('Location: categories.php?success=1');
        } else {
            header('Location: categories.php?error=1');
        }
    } catch (Exception $e) {
        header('Location: categories.php?error=' . urlencode($e->getMessage()));
    }
    exit();
}
?>