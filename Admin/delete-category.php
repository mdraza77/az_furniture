<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Category ID not found']);
        exit;
    }

    try {
        // Check if category has products
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $count = $result->fetch_assoc()['count'];

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete category with associated products']);
            exit;
        }

        // Delete the category
        $delete_stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>