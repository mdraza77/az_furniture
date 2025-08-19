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
    <title>Popular Products - Az Furniture</title>
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

        .header {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #1E1E1E;
        }

        h1 {
            font-size: 1.75rem;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.25rem;
            }
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
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .back-btn:hover {
            background-color: #007366;
            transform: translateY(-2px);
            text-decoration: none;
            color: #fff;
        }

        .back-btn:active {
            transform: translateY(0);
        }

        .search-container {
            background-color: #1E1E1E;
            margin-bottom: 10px;
        }

        #searchInput {
            padding: 12px 40px 12px 15px;
            border: 1px solid #333;
            border-radius: 8px;
        }

        #searchInput::placeholder {
            color: #fff;
        }

        #searchInput:focus {
            outline: none;
            border-color: #00897B;
            box-shadow: 0 0 0 2px rgba(0, 137, 123, 0.2);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
            transition: transform 0.2s ease-in-out;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
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

        .product-price {
            font-weight: bold;
            margin-bottom: 35px;
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

        .price {
            color: rgb(171, 200, 197);
            padding-right: 5px;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                padding: 15px;
            }

            .product-image {
                height: 150px;
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="d-flex justify-content-between align-items-center w-100 px-3">
            <div class="d-flex align-items-center">
                <a href="index.php" class="back-btn">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
            <h1 class="mb-0 ms-3">Popular Products</h1>
        </div>
    </div>

    <div class="search-container px-3 py-3">
        <div class="position-relative">
            <input type="text" id="searchInput" class="form-control bg-dark text-white" placeholder="Search by product name, designer or price...">
            <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-white"></i>
        </div>
    </div>

    <div class="product-grid">
        <?php
        $popular_query = "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC";
        $popular_result = $conn->query($popular_query);

        while ($product = $popular_result->fetch_assoc()) {
            $image_path = $product['image_url'] ? $product['image_url'] : 'assets/images/no-image.png';
            $sale_price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
        ?>
            <div class="product-card">
                <a href="product-details.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="product-designer">
                        <?php echo htmlspecialchars($product['designer'] ?? 'Unknown Designer'); ?>
                    </div>
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="text-decoration-line-through price">₹<?php echo number_format($product['price'], 2); ?></span>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchValue = e.target.value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');

            productCards.forEach(card => {
                const productName = card.querySelector('.product-title').textContent.toLowerCase();
                const designer = card.querySelector('.product-designer').textContent.toLowerCase();
                const price = card.querySelector('.product-price').textContent.toLowerCase();

                if (productName.includes(searchValue) ||
                    designer.includes(searchValue) ||
                    price.includes(searchValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>