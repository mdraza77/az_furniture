<?php
// Main entry point of the application
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();

// Fetch fresh user data before displaying
$user_id = $_SESSION['user_data']['id'];
// echo json_encode($_SESSION['user_data']);


$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Update session with fresh data
$_SESSION['user_data'] = array_merge($_SESSION['user_data'], $user_data);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Az Furniture - Home</title>
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
        }

        .welcome-section {
            font-family: 'Gidole', sans-serif;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-title {
            font-family: 'Lato', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            margin: 8px 0;
            line-height: 1.2;
            height: 2.4em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-designer {
            font-family: 'Gidole', sans-serif;
            font-size: 0.875rem;
            color: #888;
            margin-bottom: 8px;
        }

        .price {
            color: rgb(171, 200, 197);
            padding-right: 5px;
        }

        h2,
        h3 {
            font-family: 'Lato', sans-serif;
            font-weight: 700;
        }

        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #2c2c2c;
        }

        .search-bar {
            margin: 20px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 40px 12px 35px;
            background-color: #2c2c2c;
            border: none;
            border-radius: 10px;
            color: #fff;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .filter-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            cursor: pointer;
        }

        .category-section {
            padding: 20px;
            display: flex;
            gap: 15px;
            overflow-x: auto;
        }

        .category-btn {
            padding: 10px 20px;
            background-color: #00897B;
            border: none;
            border-radius: 20px;
            color: white;
            white-space: nowrap;
        }

        .category-btn.inactive {
            background-color: #2c2c2c;
        }

        .special-offers {
            padding: 20px;
        }

        .offer-card {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/furniture_banner.webp');
            background-size: cover;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .product-card {
            background-color: #2c2c2c;
            border-radius: 15px;
            padding: 15px;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .product-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .cart-btn {
            position: absolute;
            right: 15px;
            bottom: 15px;
            background-color: #00897B;
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .cart-btn:hover {
            background-color: #007366;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #1E1E1E;
            display: flex;
            justify-content: space-around;
            padding: 15px;
            border-top: 1px solid #333;
            z-index: 1000;
        }

        .nav-item {
            color: #666;
            text-decoration: none;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .nav-item i {
            font-size: 1.5rem;
        }

        .nav-item.active {
            color: #00897B;
        }

        /* Responsive Improvements */
        @media (max-width: 767px) {
            .product-grid {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
            }

            .product-grid::-webkit-scrollbar {
                display: none;
            }

            .product-card {
                flex: 0 0 250px;
                scroll-snap-align: start;
            }
        }

        @media (min-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .product-image {
                height: 180px;
            }
        }

        @media (min-width: 992px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .product-image {
                height: 200px;
            }
        }

        /* Enhanced Responsive Design */
        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                padding: 10px;
            }

            .product-card {
                padding: 10px;
            }

            .product-image {
                height: 120px;
            }

            h2 {
                font-size: 1.5rem;
            }

            .welcome-section {
                padding: 15px;
            }

            .category-section {
                padding: 15px;
            }

            .special-offers {
                padding: 15px;
            }
        }

        @media (min-width: 577px) and (max-width: 767px) {
            .product-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .product-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (min-width: 992px) {
            .container {
                max-width: 1200px;
                margin: 0 auto;
            }

            .product-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        /* Cart Icon Styles */
        .cart-icon {
            position: relative;
            cursor: pointer;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            min-width: 18px;
            height: 18px;
            border-radius: 50%;
            background-color: #dc3545;
            color: white;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Product Card Enhancements */
        .product-card {
            transition: transform 0.2s ease-in-out;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-title {
            font-size: 1rem;
            margin: 8px 0;
            line-height: 1.2;
            height: 2.4em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-designer {
            font-size: 0.875rem;
            color: #888;
            margin-bottom: 8px;
        }

        .product-price {
            font-weight: bold;
            margin-bottom: 35px;
        }

        /* Improved Search Bar */
        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px #00897B;
        }

        /* Bottom Navigation Enhancements */
        .bottom-nav {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .nav-item {
            transition: color 0.2s ease;
        }

        .nav-item:hover {
            color: #00897B;
        }

        /* Loading State */
        .product-card.loading {
            position: relative;
        }

        .product-card.loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .mb-footer {
            margin-bottom: 80px;
            padding-bottom: 20px;
        }
    </style>
</head>
<div class="welcome-section">
    <div class="profile-pic" style="background-image: url('<?php echo htmlspecialchars($_SESSION['user_data']['profile_image'] ?? 'assets/images/profile/default_user_image.png'); ?>'); background-size: cover; background-position: center;"></div>
    <div>
        <div>Welcome,</div>
        <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_data']['full_name']); ?></div>
    </div>
    <div class="ms-auto">
        <a href="orders.php" class="text-white position-relative">
            <i class="bi bi-cart3 fs-4"></i>
            <?php
            // Get total items count from cart table for current user
            $user_id = $_SESSION['user_data']['id'];
            $cart_query = "SELECT COUNT(quantity) as total_items FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($cart_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_items = $result->fetch_assoc()['total_items'] ?? 0;

            if ($total_items > 0) {
                echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">'
                    . $total_items .
                    '</span>';
            }
            ?>
        </a>
    </div>
</div>

<div class="search-bar">
    <i class="bi bi-search search-icon"></i>
    <input type="text" class="search-input" placeholder="Search Furniture">
    <i class="bi bi-sliders filter-icon"></i>
</div>

<h2 class="px-4">Special Offers</h2>
<div class="special-offers">
    <div class="offer-card">
        <h3>25% Discount</h3>
        <p>For a cozy yellow seat</p>
        <button class="btn btn-success">Learn More</button>
    </div>
</div>

<div class="category-section">
    <button class="category-btn"><i class="bi bi-chair me-2"></i>Armchair</button>
    <button class="category-btn inactive"><i class="bi bi-collection me-2"></i>Sofa</button>
    <button class="category-btn inactive"><i class="bi bi-house-door me-2"></i>Bed</button>
    <button class="category-btn inactive"><i class="bi bi-lamp me-2"></i>Light</button>
    <!-- <a class="category-btn inactive logout-testing" onclick="return confirm('Are You Sure To Logout');" href="authentication/logout.php">Logout Testing</a> -->
</div>

<div class="d-flex justify-content-between align-items-center px-4">
    <h2>Most Interested</h2>
    <a href="most-interested.php" class="text-success">View All</a>
</div>

<div class="product-grid mb-footer">
    <?php
    // फीचर्ड प्रोडक्टस को सिर्फ 6 तक सीमित करें
    $featured_query = "SELECT * FROM products WHERE is_featured = 1 AND is_active = 1 ORDER BY created_at DESC LIMIT 6";
    $featured_result = $conn->query($featured_query);

    while ($product = $featured_result->fetch_assoc()) {
        $image_path = $product['image_url'] ? $product['image_url'] : 'assets/images/no-image.png';
        $sale_price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
    ?>
        <div class="product-card">
            <a href="product-details.php?id=<?php echo $product['id']; ?>"
                style="text-decoration: none; color: inherit;">
                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
                    class="product-image">
                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                <div class="product-designer">
                    <?php echo htmlspecialchars($product['designer'] ?? 'Unknown Designer'); ?>
                </div>
                <div class="product-price">
                    <?php if ($product['sale_price']): ?>
                        <span
                            class="text-decoration-line-through price">₹<?php echo number_format($product['price'], 2); ?></span>
                        <span class="text-success">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                    <?php else: ?>
                        ₹<?php echo number_format($product['price'], 2); ?>
                    <?php endif; ?>
                </div>
            </a>
            <button class="cart-btn"
                onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $sale_price; ?>)">
                <i class="bi bi-bag-plus"></i>
            </button>
        </div>
    <?php } ?>
</div>
</a>

<div class="d-flex justify-content-between align-items-center px-4">
    <h2>Popular</h2>
    <a href="popular.php" class="text-success">View All</a>
</div>

<div class="product-grid mb-footer">
    <?php
    $popular_query = "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6";
    $popular_result = $conn->query($popular_query);

    while ($product = $popular_result->fetch_assoc()) {
        $image_path = $product['image_url'] ? $product['image_url'] : 'assets/images/no-image.png';
        $sale_price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
    ?>
        <div class="product-card">
            <a href="product-details.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
                    class="product-image">
                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                <div class="product-designer"><?php echo htmlspecialchars($product['designer'] ?? 'Unknown Designer'); ?>
                </div>
                <div class="product-price">
                    <?php if ($product['sale_price']): ?>
                        <span
                            class="text-decoration-line-through price">₹<?php echo number_format($product['price'], 2); ?></span>
                        <span class="text-success">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                    <?php else: ?>
                        ₹<?php echo number_format($product['price'], 2); ?>
                    <?php endif; ?>
                </div>
            </a>
            <button class="cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $sale_price; ?>)">
                <i class="bi bi-bag-plus"></i>
            </button>
        </div>
    <?php } ?>
</div>

<nav class="bottom-nav">
    <a href="#" class="nav-item active">
        <i class="bi bi-house-door"></i>
        <span>Home</span>
    </a>
    <a onclick="return confirm('This page is under maintenance!');" href="#" class="nav-item">
        <i class="bi bi-heart"></i>
        <span>Favourite</span>
    </a>
    <a href="shopping.php" class="nav-item">
        <i class="bi bi-bag"></i>
        <span>Shopping</span>
    </a>
    <a href="profile.php" class="nav-item">
        <i class="bi bi-person"></i>
        <span>Profile</span>
    </a>
</nav>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function addToCart(productId, productName, price) {
        $.ajax({
            type: 'POST',
            url: 'includes/add_to_cart.php',
            data: {
                product_id: productId,
                product_name: productName,
                price: price
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    }

    // रीयल-टाइम अपडेट कोड को पूरी तरह से हटा दें
    // updateCartPrices फंक्शन और उससे संबंधित कोड को हटा दिया गया है
    // function updateCartPrices() {
    //     $.ajax({
    //         type: 'POST',
    //         url: 'includes/check_updates.php',
    //         success: function(response) {
    //             const data = JSON.parse(response);
    //             if (data.hasUpdates) {
    //                 // दोनों सेक्शन्स के प्रोडक्ट कार्ड्स को अपडेट करें
    //                 $('.product-grid .product-card').each(function() {
    //                     const productId = $(this).find('input[name="product_id"]').val() ||
    //                                 $(this).find('.cart-btn').attr('onclick')?.match(/\d+/)?.[0];

    //                     if (!productId) return;

    //                     const priceElement = $(this).find('.product-price');
    //                     const addToCartBtn = $(this).find('input[name="price"]');

    //                     $.ajax({
    //                         type: 'GET',
    //                         url: 'includes/get_product_price.php',
    //                         data: { product_id: productId },
    //                         success: function(data) {
    //                             const product = JSON.parse(data);
    //                             if(product.sale_price) {
    //                                 priceElement.html(`
    //                                     <span class="text-decoration-line-through text-muted">₹${parseFloat(product.price).toFixed(2)}</span>
    //                                     <span class="text-success">₹${parseFloat(product.sale_price).toFixed(2)}</span>
    //                                 `);
    //                                 if(addToCartBtn.length) {
    //                                     addToCartBtn.val(product.sale_price);
    //                                 }
    //                             } else {
    //                                 priceElement.html(`₹${parseFloat(product.price).toFixed(2)}`);
    //                                 if(addToCartBtn.length) {
    //                                     addToCartBtn.val(product.price);
    //                                 }
    //                             }
    //                         }
    //                     });
    //                 });
    //             }
    //         }
    //     });
    // }

    // हर 30 सेकंड में चेक करें
    // setInterval(updateCartPrices, 30000);

    // // पेज लोड होने पर एक बार चेक करें
    // $(document).ready(function() {
    //     updateCartPrices();
    // });

    // हर 5 सेकंड में सिर्फ चेक करें कि कोई अपडेट है या नहीं
    // setInterval(updateCartPrices, 5000);

    // function updateProductCards() {
    //     $('.product-card').each(function() {
    //         const productId = $(this).find('input[name="product_id"]').val() ||
    //                      $(this).find('.cart-btn').attr('onclick')?.match(/\d+/)?.[0];

    //         if (!productId) return;

    //         const card = $(this);

    //         $.ajax({
    //             type: 'GET',
    //             url: 'includes/get_product_price.php',
    //             data: { product_id: productId },
    //             success: function(data) {
    //                 const product = JSON.parse(data);
    //                 if (!product.is_active) {
    //                     card.remove();
    //                     return;
    //                 }

    //                 // Update all product details
    //                 card.find('.product-title').text(product.name);
    //                 card.find('.product-designer').text(product.designer || 'Unknown Designer');
    //                 card.find('.product-image').attr('src', product.image_url || 'assets/images/no-image.png');

    //                 const priceElement = card.find('.product-price');
    //                 const addToCartBtn = card.find('input[name="price"]');

    //                 if(product.sale_price) {
    //                     priceElement.html(`
    //                         <span class="text-decoration-line-through text-muted">₹${parseFloat(product.price).toFixed(2)}</span>
    //                         <span class="text-success">₹${parseFloat(product.sale_price).toFixed(2)}</span>
    //                     `);
    //                     if(addToCartBtn.length) {
    //                         addToCartBtn.val(product.sale_price);
    //                     }
    //                 } else {
    //                     priceElement.html(`₹${parseFloat(product.price).toFixed(2)}`);
    //                     if(addToCartBtn.length) {
    //                         addToCartBtn.val(product.price);
    //                     }
    //                 }
    //             }
    //         });
    //     });
    // }

    // Check for updates every 5 seconds
    // setInterval(function() {
    //     $.ajax({
    //         type: 'GET',
    //         url: 'includes/check_updates.php',
    //         success: function(response) {
    //             const data = JSON.parse(response);
    //             if(data.hasUpdates) {
    //                 updateProductCards();
    //             }
    //         }
    //     });
    // }, 5000);

    // // Initial update on page load
    // $(document).ready(function() {
    //     updateProductCards();
    // });
</script>

<script>
    // Function to check for product updates
    function checkProductUpdates() {
        fetch('includes/check_product_updates.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update product cards
                    document.querySelectorAll('.product-card').forEach(card => {
                        const productId = card.querySelector('.cart-btn').getAttribute('onclick').match(/\d+/)[0];
                        const product = data.products[productId];

                        if (product) {
                            // Update product name
                            card.querySelector('.product-title').textContent = product.name;

                            // Update designer
                            card.querySelector('.product-designer').textContent = product.designer || 'Unknown Designer';

                            // Update price
                            const priceElement = card.querySelector('.product-price');
                            if (product.sale_price) {
                                priceElement.innerHTML = `
                                    <span class="text-decoration-line-through price">₹${parseFloat(product.price).toFixed(2)}</span>
                                    <span class="text-success">₹${parseFloat(product.sale_price).toFixed(2)}</span>
                                `;
                            } else {
                                priceElement.innerHTML = `₹${parseFloat(product.price).toFixed(2)}`;
                            }

                            // Update image
                            const imageElement = card.querySelector('.product-image');
                            if (imageElement) {
                                imageElement.src = product.image_url || 'assets/images/no-image.png';
                            }

                            // Update cart button price
                            const cartBtn = card.querySelector('.cart-btn');
                            const price = product.sale_price || product.price;
                            cartBtn.setAttribute('onclick', `addToCart(${productId}, '${product.name.replace(/'/g, "\\'")}', ${price})`);
                        }
                    });
                }
            })
            .catch(error => console.error('Error checking for updates:', error));
    }

    // Check for updates every 30 seconds
    setInterval(checkProductUpdates, 3000);
</script>

</body>

</html>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php
        echo $_SESSION['success'];
        unset($_SESSION['success']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<!-- </script> -->
</body>

</html>