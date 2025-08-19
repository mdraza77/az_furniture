<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    
    // स्लग जनरेट करें
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    // चेक करें कि क्या यह स्लग पहले से मौजूद है
    $check_slug = $conn->prepare("SELECT id FROM products WHERE slug = ?");
    $check_slug->bind_param("s", $slug);
    $check_slug->execute();
    $result = $check_slug->get_result();
    
    // अगर स्लग मौजूद है तो उसमें नंबर जोड़ें
    if ($result->num_rows > 0) {
        $counter = 1;
        $original_slug = $slug;
        while ($result->num_rows > 0) {
            $slug = $original_slug . '-' . $counter;
            $check_slug->bind_param("s", $slug);
            $check_slug->execute();
            $result = $check_slug->get_result();
            $counter++;
        }
    }
    
    // इमेज अपलोड हैंडलिंग
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../assets/images/products/';
        
        // अगर डायरेक्टरी नहीं है तो बनाएं
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = 'assets/images/products/' . $file_name;
        }
    }
    
    $query = "INSERT INTO products (name, slug, sku, price, sale_price, category_id, stock_quantity, designer, description, image_url, is_featured, is_active) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssddssissii", $name, $slug, $sku, $price, $sale_price, $category_id, $stock_quantity, $designer, $description, $image_url, $is_featured, $is_active);
    
    if ($stmt->execute()) {
        header('Location: products.php');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            padding: 20px;
        }
        .admin-panel {
            background-color: #2c2c2c;
            padding: 20px;
            border-radius: 10px;
            max-width: 800px;
            margin: 0 auto;
        }
        .form-control, .form-control:focus {
            background-color: #333;
            border-color: #444;
            color: #fff;
        }
        .form-label {
            color: #ddd;
        }
        .btn-primary {
            background-color: #00897B;
            border: none;
        }
        .btn-primary:hover {
            background-color: #007366;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-panel">
        <h2 class="mb-4">Add New Product</h2>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Designer Name</label>
                <input type="text" class="form-control" name="designer" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" class="form-control" name="price" step="0.01" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="4" required></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Product Image</label>
                <input type="file" class="form-control" name="image" accept="image/*" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Product</button>
            <a href="../index.php" class="btn btn-secondary">Go Back</a>
        </form>
    </div>
</body>
</html>