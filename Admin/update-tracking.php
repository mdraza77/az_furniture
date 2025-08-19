<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';
require '../config/mail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
$required_fields = ['order_id', 'product_id', 'status', 'location', 'carrier_name', 'tracking_number', 'estimated_delivery_date'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Missing required field: ' . $field]);
        exit;
    }
}

// Sanitize inputs
$order_id = intval($_POST['order_id']);
$product_id = intval($_POST['product_id']);
$status = $_POST['status'];
$location = $_POST['location'];
$carrier_name = $_POST['carrier_name'];
$tracking_number = $_POST['tracking_number'];
$estimated_delivery_date = $_POST['estimated_delivery_date'];
$notes = isset($_POST['notes']) ? $_POST['notes'] : '';

// Insert tracking update
$query = "INSERT INTO order_tracking (order_id, product_id, status, location, carrier_name, tracking_number, estimated_delivery_date, notes) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("iissssss", $order_id, $product_id, $status, $location, $carrier_name, $tracking_number, $estimated_delivery_date, $notes);

// Get email using user id
$get_email = "SELECT email FROM users WHERE id = (SELECT user_id FROM orders WHERE id =?)";
$email_stmt = $conn->prepare($get_email);
$email_stmt->bind_param("i", $order_id);
$email_stmt->execute();
$email_result = $email_stmt->get_result();
$email_row = $email_result->fetch_assoc();
$email = $email_row['email'];


if ($stmt->execute()) {
    // Update main order status if needed
    if ($status === 'delivered') {
        $update_order = "UPDATE orders SET status = 'delivered' WHERE id = ?";
        $order_stmt = $conn->prepare($update_order);
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
    } elseif ($status === 'shipped' && $order['status'] === 'pending') {
        $update_order = "UPDATE orders SET status = 'shipped' WHERE id = ?";
        $order_stmt = $conn->prepare($update_order);
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
    }
    $statusColors = [
        'pending' => '#FFA726',    // Orange
        'processing' => '#29B6F6', // Light Blue
        'shipped' => '#66BB6A',    // Green
        'delivered' => '#43A047',  // Dark Green
        'cancelled' => '#EF5350'   // Red
    ];
    
    $statusColor = $statusColors[$status] ?? '#00897B'; // Default teal color if status not found
    // Email template and sending logic here
    $subject = "Order Status Update";
    $message = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
<style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .container {
                    background-color: #f9f9f9;
                    border-radius: 10px;
                    padding: 20px;
                    margin-top: 20px;
                }
                .header {
                    text-align: center;
                    padding-bottom: 20px;
                    border-bottom: 2px solid " . $statusColor . ";
                }
                .content {
                    padding: 20px 0;
                }
                .status {
                    background-color: " . $statusColor . ";
                    color: white;
                    padding: 10px 20px;
                    border-radius: 5px;
                    display: inline-block;
                    margin: 10px 0;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    font-size: 0.9em;
                    color: #666;
                }
                .details{
                padding: 10px;
                border: 2px solid yellow;
                border-radius: 10px;
                }

            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='color: #00897B; margin: 0;'>Az Furniture</h1>
                    <p style='margin: 5px 0 0;'>Order Status Update</p>
                </div>
                <div class='content'>
                    <p>Dear Customer,</p>
                    <p>Your order #$order_id has been updated.</p>
                    <p>New Status: <span class='status'>" . ucfirst($status) . "</span></p>
                    <p>Track your order <a href='http://" . $_SERVER['HTTP_HOST'] . "/Az%20Furniture/track.php?order_id=" . $order_id . "'>here</a>.</p>
                    <div class='details'>
                    <p>Location: $location</p>
                    <p>Carrier: $carrier_name</p>
                    <p>Tracking Number: $tracking_number</p>
                    <p>Estimated Delivery Date: $estimated_delivery_date</p>
                    <p>Notes: $notes</p>
                    </div>
                    <p>Thank you for shopping with Az Furniture. If you have any questions about your order, please don't hesitate to contact us.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply directly to this email.</p>
                    <p>&copy; " . date('Y') . " Az Furniture. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

    try {
        $mailer->sendMail($email, $subject, $message);
    } catch (Exception $e) {
        $success = false;
        $message = 'Error sending email: ' . $e->getMessage();
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update tracking information']);
}