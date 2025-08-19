<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_data'])) {
    echo '<div class="alert alert-warning">कृपया पहले लॉगिन करें!</div>';
    exit;
}

if (isset($_POST['product_id'])) {
    $user_id = $_SESSION['user_data']['id'];
    $product_id = $_POST['product_id'];
    
    // पहले चेक करें कि प्रोडक्ट पहले से विशलिस्ट में है या नहीं
    $check_query = "SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<div class="alert alert-info">यह प्रोडक्ट पहले से आपकी विशलिस्ट में है!</div>';
    } else {
        // विशलिस्ट में प्रोडक्ट जोड़ें
        $insert_query = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $user_id, $product_id);
        
        if ($stmt->execute()) {
            echo '<div class="alert alert-success">प्रोडक्ट विशलिस्ट में जोड़ा गया!</div>';
        } else {
            echo '<div class="alert alert-danger">कुछ गलत हो गया! कृपया पुनः प्रयास करें।</div>';
        }
    }
    
    $stmt->close();
} else {
    echo '<div class="alert alert-danger">अमान्य अनुरोध!</div>';
}

$conn->close();
?>