<?php
require 'config/database.php';
require_once 'authentication/check_session.php';

if (!isset($_SESSION['user_data'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #121212;
            color: white;
        }
        .cart-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 15px;
        }
        .cart-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .product-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: #28a745;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #updateMessage {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Cart</h2>
            <a href="index.php" class="btn btn-outline-light">
                <i class="bi bi-arrow-left"></i> Continue Shopping
            </a>
        </div>

        <?php
        if(isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_data']['id'];
            
            // कार्ट और प्रोडक्ट्स टेबल से डेटा लें
            $sql = "SELECT c.*, p.image_url FROM cart c 
                   LEFT JOIN products p ON c.product_id = p.id 
                   WHERE c.user_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $total = 0;
            $shipping = 99; // शिपिंग चार्ज
            
            if($result && $result->num_rows > 0) {
                $grand_total = 0;  // सभी प्रोडक्ट्स का कुल योग
                while($item = $result->fetch_assoc()) {
                    $item_total = $item['price'] * $item['quantity'];
                    $total += $item_total;
                    $grand_total += $item_total;  // कुल योग में जोड़ें
            ?>
                <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="<?php echo $item['image_url'] ?? 'assets/images/placeholder.jpg'; ?>" class="product-img" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        </div>
                        <div class="col-md-4">
                            <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                            <p class="text-success">₹<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-3">
                                <button onclick="updateQuantity(<?php echo $item['id']; ?>, -1, this)" class="quantity-btn" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>-</button>
                                <span id="quantity-<?php echo $item['id']; ?>"><?php echo $item['quantity']; ?></span>
                                <button onclick="updateQuantity(<?php echo $item['id']; ?>, 1, this)" class="quantity-btn">+</button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-0" id="total-<?php echo $item['id']; ?>">Total: ₹<?php echo number_format($item_total, 2); ?></p>
                        </div>
                        <div class="col-md-1">
                            <button onclick="removeItem(<?php echo $item['id']; ?>)" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php
                }
            ?>
                <div class="card bg-dark mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 text-white">
                            <h5>सब टोटल:</h5>
                            <h5 id="subtotal">₹<?php echo number_format($total, 2); ?></h5>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-white">
                            <h5>शिपिंग चार्ज:</h5>
                            <h5>₹<?php echo number_format($shipping, 2); ?></h5>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-white">
                            <h5>सभी प्रोडक्ट्स का कुल योग:</h5>
                            <h5 id="grandTotal">₹<?php echo number_format($grand_total, 2); ?></h5>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between text-white">
                            <h4>कुल राशि (शिपिंग सहित):</h4>
                            <h4 id="total">₹<?php echo number_format($grand_total + $shipping, 2); ?></h4>
                        </div>
                        <button class="btn btn-success w-100 mt-3">चेकआउट करें</button>
                    </div>
                </div>
            <?php
            } else {
                echo '<div class="text-center py-5">
                        <h3>Your cart is empty</h3>
                        <a href="index.php" class="btn btn-success mt-3">Start Shopping</a>
                      </div>';
            }
        }
        ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function showMessage(message, type = 'success') {
        $('#updateMessage').removeClass().addClass(`alert alert-${type}`).text(message).fadeIn();
        setTimeout(() => $('#updateMessage').fadeOut(), 3000);
    }

    function updateQuantity(itemId, change, button) {
        const quantitySpan = document.getElementById(`quantity-${itemId}`);
        const currentQuantity = parseInt(quantitySpan.textContent);
        const newQuantity = Math.max(1, currentQuantity + change);
        const priceElement = button.closest('.row').querySelector('.text-success');
        const price = parseFloat(priceElement.textContent.replace('₹', '').replace(',', ''));
        
        quantitySpan.textContent = newQuantity;
        
        const totalElement = document.getElementById(`total-${itemId}`);
        const newTotal = (price * newQuantity).toFixed(2);
        totalElement.textContent = `Total: ₹${newTotal}`;
        
        const minusButton = button.closest('.d-flex').querySelector('button:first-child');
        minusButton.disabled = newQuantity <= 1;
        
        updateCartTotal();
        
        fetch('handlers/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `item_id=${itemId}&change=${change}`
        });
    }

    function updateCartTotal() {
        let subtotal = 0;
        let grandTotal = 0;
        document.querySelectorAll('.cart-item').forEach(item => {
            const totalText = item.querySelector('[id^="total-"]').textContent;
            const amount = parseFloat(totalText.replace('Total: ₹', '').replace(',', ''));
            subtotal += amount;
            grandTotal += amount;
        });
        
        const shipping = 99;
        const total = grandTotal + shipping;
        
        document.querySelector('#subtotal').textContent = `₹${subtotal.toFixed(2)}`;
        document.querySelector('#grandTotal').textContent = `₹${grandTotal.toFixed(2)}`;
        document.querySelector('#total').textContent = `₹${total.toFixed(2)}`;
    }

    function removeItem(itemId) {
        if (confirm('क्या आप वाकई इस आइटम को हटाना चाहते हैं?')) {
            const itemElement = document.querySelector(`.cart-item[data-product-id="${itemId}"]`);
            
            fetch('handlers/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    itemElement.style.opacity = '0';
                    setTimeout(() => {
                        itemElement.remove();
                        updateCartTotal();
                        if (document.querySelectorAll('.cart-item').length === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    showMessage('आइटम हटाने में त्रुटि हुई', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('कुछ गलत हो गया', 'danger');
            });
        }
    }

    function updateCartItems() {
        $('.cart-item').each(function() {
            const itemId = $(this).data('product-id');
            const cartItem = $(this);
            
            $.ajax({
                type: 'GET',
                url: 'includes/get_product_price.php',
                data: { product_id: itemId },
                dataType: 'json',
                success: function(data) {
                    try {
                        const priceElement = cartItem.find('.text-success');
                        const quantityElement = cartItem.find('[id^="quantity-"]');
                        const totalElement = cartItem.find('[id^="total-"]');
                        const quantity = parseInt(quantityElement.text());
                        
                        // Update product details
                        cartItem.find('h5').first().text(data.name);
                        cartItem.find('.product-img').attr('src', data.image_url || 'assets/images/placeholder.jpg');
                        
                        const currentPrice = data.sale_price || data.price;
                        priceElement.text(`₹${parseFloat(currentPrice).toFixed(2)}`);
                        
                        const newTotal = (currentPrice * quantity).toFixed(2);
                        totalElement.text(`Total: ₹${newTotal}`);
                        
                        updateCartTotal();
                    } catch (error) {
                        console.error('Error processing product data:', error);
                        showMessage('प्रोडक्ट की जानकारी अपडेट करने में समस्या आई', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    showMessage('सर्वर से डेटा प्राप्त करने में समस्या आई', 'danger');
                }
            });
        });
    }

    // Check for updates every 5 seconds
    setInterval(function() {
        $.ajax({
            type: 'GET',
            url: 'includes/check_updates.php',
            dataType: 'json',
            success: function(data) {
                if(data.hasUpdates) {
                    updateCartItems();
                }
            },
            error: function(xhr, status, error) {
                console.error('Update check error:', error);
            }
        });
    }, 5000);

    // Initial update on page load
    $(document).ready(function() {
        updateCartItems();
    });
    </script>
</body>
</html>