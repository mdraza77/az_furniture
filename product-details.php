<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #121212;
            color: #fff;
            min-height: 100vh;
        }

        .product-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .product-image {
            width: 100%;
            height: 300px;
            /* निश्चित ऊंचाई सेट की */
            border-radius: 15px;
            object-fit: cover;
        }

        @media (min-width: 768px) {
            .product-image {
                height: 300px;
                /* डेस्कटॉप पर भी समान ऊंचाई */
            }
        }

        .product-info {
            background: rgba(44, 44, 44, 0.7);
            border-radius: 15px;
            padding: 25px;
            margin-top: 0;
            position: relative;
            backdrop-filter: blur(10px);
        }

        .description {
            background: rgba(44, 44, 44, 0.9);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }

        .description h5 {
            color: #ffffff;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .description p {
            color: #e0e0e0;
            line-height: 1.6;
            font-size: 1rem;
        }

        .cancelled-price {
            color: #ff6b6b;
            text-decoration: line-through;
            font-size: 1.1rem;
            margin-right: 10px;
            opacity: 0.9;
        }

        .action-buttons {
            position: fixed;
            z-index: 100;
            padding: 15px;
            width: 100%;
            background: linear-gradient(to bottom, rgba(18, 18, 18, 0.9), transparent);
        }

        .quantity-control {
            background: rgba(44, 44, 44, 0.9);
            border-radius: 15px;
            padding: 15px;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            font-size: 18px;
            transition: transform 0.2s;
        }

        .quantity-btn:hover {
            transform: scale(1.1);
        }

        .price-section {
            background: rgba(44, 44, 44, 0.7);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }

        .add-to-cart {
            background: linear-gradient(45deg, #00897B, #00695C);
            transition: transform 0.2s;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .add-to-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 137, 123, 0.3);
        }

        .stats {
            background: rgba(44, 44, 44, 0.7);
            border-radius: 15px;
            padding: 15px;
        }

        .description {
            background: rgba(44, 44, 44, 0.7);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }

        @media (max-width: 767px) {
            .product-info {
                margin-top: -30px;
            }

            .action-buttons {
                background: rgba(18, 18, 18, 0.95);
            }
        }
    </style>
</head>

<body>
    <div class="action-buttons d-flex justify-content-between">
        <a href="index.php" class="btn btn-dark rounded-circle">
            <i class="bi bi-arrow-left"></i>
        </a>
        <?php
        if (isset($_GET['id'])) {
            $product_id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            // Check if product is in wishlist
            $in_wishlist = false;
            if (isset($_SESSION['user_id'])) {
                $check_stmt = $conn->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
                $check_stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
                $check_stmt->execute();
                $wishlist_result = $check_stmt->get_result();
                $in_wishlist = $wishlist_result->num_rows > 0;
            }

            if ($product) {
                $sale_price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
        ?>
                <button class="btn btn-dark rounded-circle" onclick="toggleWishlist(<?php echo $product['id']; ?>)" id="wishlistBtn">
                    <i class="bi <?php echo $in_wishlist ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>" id="wishlistIcon"></i>
                </button>
    </div>

    <div class="product-container">
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
            </div>
            <div class="col-md-6">
                <div class="product-info">
                    <h1 class="h2 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="text-muted mb-4"><?php echo htmlspecialchars($product['designer'] ?? 'Designer Not Available'); ?></p>

                    <div class="stats d-flex justify-content-between mb-4">
                        <span>Product Code: <?php echo htmlspecialchars($product['sku'] ?? 'Not Available'); ?></span>
                        <span>Stock: <?php echo $product['stock_quantity']; ?></span>
                    </div>

                    <div class="rating mb-4">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                    </div>

                    <div class="description">
                        <h5>Description</h5>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>

                    <div class="quantity-control d-flex align-items-center justify-content-between mb-4">
                        <button class="quantity-btn btn btn-success rounded-circle" onclick="updateQuantity(-1)">-</button>
                        <span class="quantity h4 mb-0" id="quantity">1</span>
                        <button class="quantity-btn btn btn-success rounded-circle" onclick="updateQuantity(1)">+</button>
                    </div>

                    <div class="price-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Price</span>
                            <span class="h4 mb-0">
                                <?php if ($product['sale_price']): ?>
                                    <span class="cancelled-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="text-success">₹<span id="total-price"><?php echo number_format($product['sale_price'], 2); ?></span></span>
                                <?php else: ?>
                                    ₹<span id="total-price"><?php echo number_format($product['price'], 2); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Total</span>
                            <span class="h4 mb-0 text-success" id="calculated-total">₹<?php echo number_format($sale_price, 2); ?></span>
                        </div>
                    </div>

                    <form method="POST" action="includes/add_to_cart.php" class="mt-4">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                        <input type="hidden" name="price" value="<?php echo $sale_price; ?>">
                        <input type="hidden" name="quantity" id="form-quantity" value="1">
                        <button type="submit" class="add-to-cart btn btn-success btn-lg w-100">
                            Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
            } else {
                echo '<div class="p-4">Product not found</div>';
            }
        } else {
            echo '<div class="p-4">Invalid product ID</div>';
        }
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let quantity = 1;
    const basePrice = <?php echo isset($product) ? ($product['sale_price'] ? $product['sale_price'] : $product['price']) : 0; ?>;

    function updateQuantity(change) {
        quantity = Math.max(1, quantity + change);
        document.getElementById('quantity').textContent = quantity;
        document.getElementById('form-quantity').value = quantity;
        updateTotal();
    }

    function updateTotal() {
        const total = (basePrice * quantity).toFixed(2);
        document.getElementById('total-price').textContent = basePrice.toFixed(2);
        document.getElementById('calculated-total').textContent = '₹' + total;
    }

    function toggleWishlist(productId) {
        $.ajax({
            url: 'includes/toggle_wishlist.php',
            type: 'POST',
            data: {
                product_id: productId
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.error) {
                    window.location.href = 'login.php';
                    return;
                }

                const icon = document.getElementById('wishlistIcon');
                if (data.status === 'added') {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    icon.classList.add('text-danger');
                } else {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.remove('text-danger');
                    icon.classList.add('bi-heart');
                }
            },
            error: function() {
                alert('Error updating wishlist. Please try again.');
            }
        });
    }
</script>
<script>
    $(document).ready(function() {
        // Wishlist functionality
        $('.wishlist-btn').click(function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');

            $.ajax({
                url: 'handlers/add_to_wishlist.php',
                type: 'POST',
                data: {
                    product_id: productId
                },
                success: function(response) {
                    
                    $('#wishlist-message').html(response).fadeIn().delay(2000).fadeOut();
                },
                error: function() {
                    $('#wishlist-message').html('कुछ गलत हो गया!').fadeIn().delay(2000).fadeOut();
                }
            });
        });
    });
</script>
</body>

</html>