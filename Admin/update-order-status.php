<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';
require '../config/mail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $status = $_POST['status'] ?? null;
    
    if (!$order_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        // Move the success response to after all operations are complete
        $success = true;
        $message = '';

        // Retrieve user_id from orders table
        $user_id = null;
        $stmt = $conn->prepare("SELECT user_id FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $user_id = $user_data ? $user_data['user_id'] : null;
        $stmt->close();

        if (!$user_id) {
            $success = false;
            $message = 'User ID not found';
        } else {
            // Retrieve email from users table
            $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            $email = $user_data ? $user_data['email'] : null;
            $stmt->close();

            if (!$email) {
                $success = false;
                $message = 'Email not found';
            } else {
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
            }
        }
        
        // Send the JSON response after all operations
        echo json_encode(['success' => $success, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}