<?php
require_once '../config/database.php';
require_once '../authentication/check_session.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['error' => 'Product ID not provided']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

// Check if product is already in wishlist
$check_stmt = $conn->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
$check_stmt->bind_param("ii", $user_id, $product_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Remove from wishlist
    $delete_stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
    $delete_stmt->bind_param("ii", $user_id, $product_id);
    $delete_stmt->execute();
    echo json_encode(['status' => 'removed']);
} else {
    // Add to wishlist
    $insert_stmt = $conn->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $user_id, $product_id);
    $insert_stmt->execute();
    echo json_encode(['status' => 'added']);
}