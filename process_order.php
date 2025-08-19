<?php
// echo json_encode($_POST);
// // exit;

require 'config/database.php';
require_once 'authentication/check_session.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;


redirectToLogin();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_data']['id'];

    // Get cart items
    $cart_query = "SELECT c.*, p.price as original_price FROM cart c 
                   LEFT JOIN products p ON c.product_id = p.id 
                   WHERE c.user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result();

    // Calculate total amount
    $total_amount = 0;
    while ($item = $cart_items->fetch_assoc()) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    // Create order
    $order_query = "INSERT INTO orders (
        user_id, 
        total_amount, 
        full_name,
        shipping_address, 
        address_line1,
        address_line2,
        city, 
        state, 
        postal_code, 
        country,
        phone_number,
        payment_method
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($order_query);
    $stmt->bind_param(
        "idssssssssss",
        $user_id,
        $total_amount,
        $_POST['full_name'],
        $_POST['shipping_address'],
        $_POST['address_line1'],
        $_POST['address_line2'],
        $_POST['city'],
        $_POST['state'],
        $_POST['postal_code'],
        $_POST['country'],
        $_POST['phone_number'],
        $_POST['payment_method']
    );

    if ($stmt->execute()) {
        $order_id = $conn->insert_id;

        // Insert order items
        $cart_items->data_seek(0);
        while ($item = $cart_items->fetch_assoc()) {
            $item_query = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity) 
                          VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($item_query);
            $stmt->bind_param(
                "iisdi",
                $order_id,
                $item['product_id'],
                $item['product_name'],
                $item['price'],
                $item['quantity']
            );
            $stmt->execute();
        }

        $mailer = new PHPMailer;

        try {
            //Server settings
            $mailer->SMTPDebug = 0;                      // Enable verbose debug output
            $mailer->isSMTP();                           // Send using SMTP
            $mailer->Host       = 'smtp.gmail.com';             // Set the SMTP server to send through
            $mailer->SMTPAuth   = true;                  // Enable SMTP authentication
            $mailer->Username   = 'mdraza7477@gmail.com';             // SMTP username
            $mailer->Password   = 'lkabvvtlyvunmtfo';             // SMTP password
            $mailer->SMTPSecure = 'tls';                 // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mailer->Port       = 587;             // TCP port to connect to

            //Recipients
            $mailer->clearAllRecipients(); // Important: clear previous recipients
            $mailer->setFrom('mdraza7477@gmail.com', 'Az Furniture');

            $mailer->addAddress($_SESSION['user_data']['email']);     // Add a recipient

            // Content
            $mailer->isHTML(true);                                  // Set email format to HTML
            $mailer->Subject = 'Order Confirmation - Az Furniture';
            $mailer->Body    = "<h2>Thank you for your order, " . $_SESSION['name'] . "</h2>"
                . "<p>Your order (Order ID: {$order_id}) has been placed successfully.</p>"
                . "<p>Total Amount: $" . $total_amount . "</p>"
                . "<p>Track your order <a href='http://" . $_SERVER['HTTP_HOST'] . "/Az%20Furniture/track.php?order_id=" . $order_id . "'>here</a>.</p>"
                . "<p>We will notify you when your order is shipped.</p>";

            $mailer->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mailer->ErrorInfo}";
        }

        // Clear cart
        $clear_cart = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($clear_cart);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Redirect to success page
        header("Location: order_success.php?order_id=" . $order_id);
        exit;
    } else {
        header("Location: shipping.php?error=1");
        exit;
    }
}
