<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT id, name, description FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($category = $result->fetch_assoc()) {
        echo json_encode($category);
    } else {
        echo json_encode(['error' => 'Category not found']);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?>