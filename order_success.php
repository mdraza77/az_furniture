<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();

$order_id = $_GET['order_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Lato', sans-serif;
        }
        .success-container {
            text-align: center;
            padding: 50px 20px;
        }
        .success-icon {
            font-size: 64px;
            color: #00897B;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #00897B;
            border: none;
        }
        .btn-primary:hover {
            background-color: #007366;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <i class="bi bi-check-circle-fill success-icon"></i>
            <h2>Order Placed Successfully!</h2>
            <p>Your order #<?php echo $order_id; ?> has been placed successfully.</p>
            <p>You will receive an email confirmation shortly.</p>
            <a href="index.php" class="btn btn-primary mt-4">Continue Shopping</a>
        </div>

    </div>
</body>
</html>