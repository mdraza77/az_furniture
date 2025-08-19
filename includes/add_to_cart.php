<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_data']['id'])) {
        $_SESSION['error'] = 'Please login first';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    $user_id = $_SESSION['user_data']['id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if (!$product_id || !$product_name || !$price) {
        $_SESSION['error'] = 'Invalid input data';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $new_quantity = $row['quantity'] + $quantity;

            // सिर्फ quantity, product_name और admin का original price अपडेट करें
            $stmt = $conn->prepare("UPDATE cart SET quantity = ?, product_name = ?, price = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("isdii", $new_quantity, $product_name, $price, $user_id, $product_id);
        } else {
            // नया प्रोडक्ट इन्सर्ट करें admin के original price के साथ
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisdi", $user_id, $product_id, $product_name, $price, $quantity);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Product successfully added to cart!';
        } else {
            $_SESSION['error'] = 'Failed to add product to cart';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    header('Location: ../index.php');
    exit;
}
