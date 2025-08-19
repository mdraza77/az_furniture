<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();

// Get user's orders
$user_id = $_SESSION['user_data']['id'];
$query = "SELECT o.*, p.image_url 
          FROM cart o 
          LEFT JOIN products p ON o.product_id = p.id 
          WHERE o.user_id = ? 
          ORDER BY o.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total
$total = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total += $row['price'] * $row['quantity'];
    }
    // Reset result pointer
    $result->data_seek(0);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Gidole&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: #121212;
            color: #fff;
            min-height: 100vh;
            padding-bottom: 80px;
        }

        .header {
            padding: 20px;
            background-color: #1E1E1E;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background-color: #00897B;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #007366;
            color: #fff;
            text-decoration: none;
        }

        .cart-container {
            padding: 20px;
        }

        .cart-item {
            background-color: #2c2c2c;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .cart-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }

        .cart-details {
            flex-grow: 1;
        }

        .cart-title {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .cart-price {
            color: #00897B;
            font-weight: 600;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            background-color: #00897B;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .quantity-btn:hover {
            background-color: #007366;
        }

        .empty-cart {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-cart i {
            font-size: 48px;
            color: #666;
            margin-bottom: 20px;
        }

        .checkout-btn {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #00897B;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 15px 40px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 137, 123, 0.2);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkout-btn:hover {
            background-color: #007366;
            transform: translateX(-50%) translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 137, 123, 0.3);
        }

        @media (max-width: 576px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
            }

            .cart-image {
                width: 120px;
                height: 120px;
            }

            .quantity-controls {
                justify-content: center;
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <a href="index.php" class="back-btn">
            <i class="bi bi-arrow-left"></i>
            <span>Back</span>
        </a>
        <h1 class="mb-0">My Cart</h1>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="cart-container">
            <div class="cart-list">
                <?php while ($item = $result->fetch_assoc()): ?>
                    <div class="cart-item">
                        <img src="<?php echo $item['image_url'] ?? 'assets/images/no-image.png'; ?>"
                            alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                            class="cart-image">
                        <div class="cart-details">
                            <div class="cart-title"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <div class="cart-price">₹<?php echo number_format($item['price'], 2); ?></div>
                            <div class="item-total">Total: ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                        </div>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 'decrease')">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span><?php echo $item['quantity']; ?></span>
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 'increase')">
                                <i class="bi bi-plus"></i>
                            </button>
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="cart-summary">
                <div class="total-section">
                    <h3>Cart Total</h3>
                    <div class="total-amount">₹<?php echo number_format($total, 2); ?></div>
                </div>

                <div class="price-breakdown mt-4">
                    <div class="breakdown-item d-flex justify-content-between align-items-center mb-3">
                        <span>Price (<?php echo $result->num_rows; ?> items)</span>
                        <span class="text-white">₹<?php echo number_format($total, 2); ?></span>
                    </div>

                    <div class="breakdown-total d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <div>
                            <span class="h5 mb-0">Total Amount</span>
                        </div>
                        <span class="h5 mb-0">₹<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>

            <button class="checkout-btn" onclick="window.location.href='shipping.php'">
                Proceed to Checkout
            </button>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <i class="bi bi-cart-x"></i>
            <h2>Your cart is empty</h2>
            <p>Add some products to your cart and they will appear here</p>
            <a href="index.php" class="back-btn mt-3">Continue Shopping</a>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function updateQuantity(productId, action) {
            $.ajax({
                type: 'POST',
                url: 'includes/update_cart.php',
                data: {
                    product_id: productId,
                    action: action
                },
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            // Get the item container
                            const itemContainer = $(`button[onclick="updateQuantity(${productId}, '${action}')"]`).closest('.cart-item');
                            const quantitySpan = itemContainer.find('.quantity-controls span');
                            const currentQuantity = parseInt(quantitySpan.text());

                            let newQuantity = currentQuantity;
                            if (action === 'increase') {
                                newQuantity = currentQuantity + 1;
                            } else if (action === 'decrease' && currentQuantity > 1) {
                                newQuantity = currentQuantity - 1;
                            }

                            // Update quantity display
                            quantitySpan.text(newQuantity);

                            // Update item total
                            const price = parseFloat(itemContainer.find('.cart-price').text().replace('₹', '').replace(',', ''));
                            const newTotal = price * newQuantity;
                            itemContainer.find('.item-total').text(`Total: ₹${newTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);

                            // Update cart total
                            updateCartTotal();
                        } else {
                            alert('Failed to update cart: ' + (data.error || 'Unknown error'));
                        }
                    } catch (e) {
                        alert('Invalid response from server');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Failed to update cart: ' + error);
                }
            });
        }

        function removeFromCart(productId) {
            if (!confirm('Are you sure you want to remove this item?')) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'includes/update_cart.php',
                data: {
                    product_id: productId,
                    action: 'remove'
                },
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            // Remove item with animation
                            const itemContainer = $(`button[onclick="removeFromCart(${productId})"]`).closest('.cart-item');
                            itemContainer.fadeOut(300, function() {
                                $(this).remove();
                                // Update cart total
                                updateCartTotal();

                                // Check if cart is empty
                                if ($('.cart-item').length === 0) {
                                    // Show empty cart message
                                    $('.cart-container').html(`
                                        <div class="empty-cart">
                                            <i class="bi bi-cart-x"></i>
                                            <h2>Your cart is empty</h2>
                                            <p>Add some products to your cart and they will appear here</p>
                                            <a href="index.php" class="back-btn mt-3">Continue Shopping</a>
                                        </div>
                                    `);
                                }
                            });
                        } else {
                            alert('Failed to remove item: ' + (data.error || 'Unknown error'));
                        }
                    } catch (e) {
                        alert('Invalid response from server');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Failed to remove item: ' + error);
                }
            });
        }

        function updateCartTotal() {
            let total = 0;
            $('.cart-item').each(function() {
                const price = parseFloat($(this).find('.cart-price').text().replace('₹', '').replace(',', ''));
                const quantity = parseInt($(this).find('.quantity-controls span').text());
                total += price * quantity;
            });

            // Update total display with Indian currency format
            $('.total-amount').text(`₹${total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
        }
    </script>
</body>

</html>
<style>
    .item-total {
        color: #888;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .remove-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin-left: 10px;
    }

    .remove-btn:hover {
        background-color: #bb2d3b;
    }

    .cart-summary {
        background-color: #2c2c2c;
        border-radius: 15px;
        padding: 20px;
        margin-top: 20px;
    }

    .total-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .total-amount {
        font-size: 1.5rem;
        color: #00897B;
        font-weight: bold;
    }

    .price-breakdown {
        background-color: #1E1E1E;
        border-radius: 10px;
        padding: 15px;
    }

    .breakdown-item {
        font-size: 0.95rem;
        color: #e0e0e0;
    }

    .breakdown-total {
        color: #fff;
    }

    .savings-info {
        font-size: 0.9rem;
        font-weight: 500;
    }

    .text-success {
        color: #00897B !important;
    }

    .border-top {
        border-top: 1px solid #3E3E3E !important;
    }
</style>