<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Payment - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Lato', sans-serif;
        }
        .payment-form {
            background-color: #1E1E1E;
            border-radius: 15px;
            padding: 20px;
            margin: 20px auto;
            max-width: 600px;
        }
        .form-control {
            background-color: #2c2c2c;
            border: 1px solid #3E3E3E;
            color: #fff;
        }
        .form-control:focus {
            background-color: #2c2c2c;
            border-color: #00897B;
            color: #fff;
            box-shadow: none;
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
        <div class="payment-form">
            <h2 class="mb-4">Card Payment</h2>
            <form action="process_order.php" method="POST" id="paymentForm">
                <div class="mb-3">
                    <label for="card_number" class="form-label">Card Number</label>
                    <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label for="expiry" class="form-label">Expiry Date</label>
                        <input type="text" class="form-control" id="expiry" name="expiry" placeholder="MM/YY" required>
                    </div>
                    <div class="col">
                        <label for="cvv" class="form-label">CVV</label>
                        <input type="password" class="form-control" id="cvv" name="cvv" placeholder="123" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="card_name" class="form-label">Name on Card</label>
                    <input type="text" class="form-control" id="card_name" name="card_name" required>
                </div>
                <div id="hiddenFields"></div>
                <button type="submit" class="btn btn-primary w-100">Pay Now</button>
            </form>
        </div>
    </div>
    <script>
        // Restore shipping data from session storage
        window.onload = function() {
            const shippingData = JSON.parse(sessionStorage.getItem('shippingData'));
            const hiddenFields = document.getElementById('hiddenFields');
            
            for (const [key, value] of Object.entries(shippingData)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                hiddenFields.appendChild(input);
            }
        }
    </script>
</body>
</html>