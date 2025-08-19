<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];
    $user_id = $_SESSION['user_data']['id'];

    try {
        if ($action === 'remove') {
            // Remove item from cart
            $query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $product_id, $user_id);
            $success = $stmt->execute();
        } else {
            // Get current quantity
            $query = "SELECT quantity FROM cart WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $product_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current = $result->fetch_assoc();

            if ($current) {
                $new_quantity = $action === 'increase' ?
                    $current['quantity'] + 1 :
                    max(1, $current['quantity'] - 1);

                // Update quantity
                $query = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iii", $new_quantity, $product_id, $user_id);
                $success = $stmt->execute();
            }
        }

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database update failed']);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
