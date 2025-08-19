<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $slug = strtolower(str_replace(' ', '-', $name));
    
    try {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $slug, $description, $category_id);
        
        if ($stmt->execute()) {
            header('Location: categories.php?success=2');
        } else {
            header('Location: categories.php?error=2');
        }
    } catch (Exception $e) {
        header('Location: categories.php?error=' . urlencode($e->getMessage()));
    }
    exit();
}
?>