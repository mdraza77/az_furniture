<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $sku = $_POST['sku'];
    $price = $_POST['price'];
    $sale_price = $_POST['sale_price'] ?: null;
    $category_id = $_POST['category_id'] ?: null;
    $stock_quantity = $_POST['stock_quantity'];
    $designer = $_POST['designer'];
    $description = $_POST['description'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // पहले मौजूदा इमेज URL प्राप्त करें
    $current_image_query = "SELECT image_url FROM products WHERE id = ?";
    $stmt = $conn->prepare($current_image_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_image = $result->fetch_assoc()['image_url'];
    
    // इमेज अपलोड हैंडलिंग
    $image_url = $current_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../assets/images/products/';
        
        // अगर डायरेक्टरी नहीं है तो बनाएं
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // पुरानी इमेज को डिलीट करें
        if ($current_image && file_exists('../' . $current_image)) {
            unlink('../' . $current_image);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = 'assets/images/products/' . $file_name;
        }
    }
    
    $query = "UPDATE products SET 
              name = ?, 
              sku = ?, 
              price = ?, 
              sale_price = ?, 
              category_id = ?, 
              stock_quantity = ?, 
              designer = ?, 
              description = ?, 
              image_url = ?, 
              is_featured = ?, 
              is_active = ? 
              WHERE id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssddsisssiii", $name, $sku, $price, $sale_price, $category_id, $stock_quantity, $designer, $description, $image_url, $is_featured, $is_active, $product_id);
    
    if ($stmt->execute()) {
        header('Location: products.php');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>