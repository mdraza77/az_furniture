<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Get all products from the featured and popular sections
$featured_query = "SELECT id, name, price, sale_price, designer, image_url FROM products WHERE is_featured = 1 AND is_active = 1 ORDER BY created_at DESC LIMIT 6";
$popular_query = "SELECT id, name, price, sale_price, designer, image_url FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6";

try {
    $featured_result = $conn->query($featured_query);
    $popular_result = $conn->query($popular_query);
    
    $products = [];
    
    // Process featured products
    while ($product = $featured_result->fetch_assoc()) {
        $products[$product['id']] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'sale_price' => $product['sale_price'],
            'designer' => $product['designer'],
            'image_url' => $product['image_url']
        ];
    }
    
    // Process popular products
    while ($product = $popular_result->fetch_assoc()) {
        $products[$product['id']] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'sale_price' => $product['sale_price'],
            'designer' => $product['designer'],
            'image_url' => $product['image_url']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching product updates: ' . $e->getMessage()
    ]);
}
?>