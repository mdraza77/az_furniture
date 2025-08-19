<?php
header('Content-Type: application/json');

try {
    require_once '../config/database.php';

    if(!isset($_GET['product_id'])) {
        throw new Exception('Product ID is required');
    }

    $product_id = (int)$_GET['product_id'];
    
    $stmt = $conn->prepare("SELECT id, name, price, sale_price, sku, category, stock_quantity, designer, image_url, description, is_featured, is_active FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        throw new Exception('Product not found');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>