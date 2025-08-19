<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

header('Content-Type: application/json');

if(isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    
    // Get order details with customer information
    $order_query = "SELECT o.*, u.full_name, u.email, u.phone_number
                    FROM orders o
                    JOIN users u ON o.user_id = u.id
                    WHERE o.id = ?";
    
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    $order = $order_result->fetch_assoc();
    
    if($order) {
        // Get order items
        $items_query = "SELECT * FROM order_items WHERE order_id = ?";
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        $items = $items_result->fetch_all(MYSQLI_ASSOC);
        
        $response = [
            'customer' => [
                'name' => $order['full_name'],
                'email' => $order['email'],
                'phone' => $order['phone_number']
            ],
            'shipping' => [
                'address' => $order['shipping_address'],
                'city' => $order['city'],
                'state' => $order['state'],
                'postal_code' => $order['postal_code'],
                'country' => $order['country']
            ],
            'items' => $items,
            'payment' => [
                'method' => $order['payment_method'],
                'status' => 'Completed' // You can modify this based on your payment status field
            ],
            'summary' => [
                'subtotal' => $order['total_amount'],
                'shipping' => 0, // Add shipping cost if you have it in your database
                'tax' => 0, // Add tax if you have it in your database
                'total' => $order['total_amount']
            ]
        ];
        
        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID not provided']);
}