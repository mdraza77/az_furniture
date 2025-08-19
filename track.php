<?php
require_once 'config/database.php';
session_start();

$tracking_error = '';
$tracking_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['order_id'])) {
    $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : $_GET['order_id'];
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
    
    $query = "SELECT ot.*, p.name as product_name 
             FROM order_tracking ot 
             LEFT JOIN products p ON ot.product_id = p.id 
             WHERE ot.order_id = ?";
    
    if ($product_id) {
        $query .= " AND ot.product_id = ?";
    }
    
    $query .= " ORDER BY ot.update_time DESC";
    
    $stmt = $conn->prepare($query);
    
    if ($product_id) {
        $stmt->bind_param('ii', $order_id, $product_id);
    } else {
        $stmt->bind_param('i', $order_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tracking_data = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $tracking_error = 'No tracking information found for this order/product.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Gidole&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .tracking-roadmap { position: relative; padding: 20px 0; }
        .tracking-line, .tracking-progress {
            position: absolute;
            top: 50%;
            height: 4px;
            transform: translateY(-50%);
        }
        .tracking-line {
            left: 0;
            width: 100%;
            background: #e9ecef;
            z-index: 1;
        }
        .tracking-progress {
            left: 0;
            background: #0d6efd;
            z-index: 2;
            transition: width 0.3s ease;
        }
        .tracking-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            z-index: 3;
        }
        .tracking-step { text-align: center; flex: 1; }
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            border: 2px solid #dee2e6;
        }
        .step-icon.active { background: #0d6efd; color: white; border-color: #0d6efd; }
        .step-icon.completed { background: #198754; color: white; border-color: #198754; }
        .step-label { font-size: 0.875rem; color: #6c757d; margin-top: 5px; }
        .step-label.active { color: #0d6efd; font-weight: bold; }
        .step-label.completed { color: #198754; font-weight: bold; }
        .step-date { font-size: 0.75rem; color: #6c757d; margin-top: 2px; }
    </style>
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
<body>

    <div class="container my-5">
        <h2 class="mb-4">Track Your Order</h2>
        
        <form method="POST" class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="order_id" placeholder="Enter Order ID" required>
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="product_id" placeholder="Enter Product ID (optional)">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Track Order</button>
                </div>
            </div>
        </form>

        <?php if ($tracking_error): ?>
            <div class="alert alert-warning"><?php echo $tracking_error; ?></div>
        <?php endif; ?>

        <?php if ($tracking_data): ?>
            <?php 
            // Show roadmap for the latest tracking update only
            $latest = $tracking_data[0];
            $steps = [
                'processing' => ['icon' => '⚙️', 'label' => 'Processing'],
                'shipped' => ['icon' => '📦', 'label' => 'Shipped'],
                'in_transit' => ['icon' => '🚚', 'label' => 'In Transit'],
                'out_for_delivery' => ['icon' => '🚗', 'label' => 'Out for Delivery'],
                'delivered' => ['icon' => '✅', 'label' => 'Delivered']
            ];
            $current_status = $latest['status'];
            $progress_width = array_search($current_status, array_keys($steps)) * 25;
            ?>
            <div class="card mb-4 bg-dark text-white">
                <div class="card-body">
                    <h5 class="card-title">Product: <?php echo htmlspecialchars($latest['product_name']); ?></h5>
                    <div class="tracking-roadmap mt-4">
                        <div class="tracking-line"></div>
                        <div class="tracking-progress" style="width: <?php echo $progress_width; ?>%"></div>
                        <div class="tracking-steps">
                            <?php foreach ($steps as $status => $step): 
                                $is_completed = array_search($status, array_keys($steps)) < array_search($current_status, array_keys($steps));
                                $is_current = $status === $current_status;
                                $step_class = $is_completed ? 'completed' : ($is_current ? 'active' : '');
                            ?>
                            <div class="tracking-step">
                                <div class="step-icon <?php echo $step_class; ?>">
                                    <?php echo $step['icon']; ?>
                                </div>
                                <div class="step-label <?php echo $step_class; ?>">
                                    <?php echo $step['label']; ?>
                                    <?php if ($is_current || $is_completed): ?>
                                        <div class="step-date">
                                            <?php echo date('M j', strtotime($latest['update_time'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mt-4 row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Location:</strong> <?php echo htmlspecialchars($latest['location']); ?></p>
                            <p class="mb-2"><strong>Carrier:</strong> <?php echo htmlspecialchars($latest['carrier_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Tracking #:</strong> <?php echo htmlspecialchars($latest['tracking_number']); ?></p>
                            <p class="mb-2"><strong>Est. Delivery:</strong> <?php echo date('M j, Y', strtotime($latest['estimated_delivery_date'])); ?></p>
                        </div>
                        <?php if ($latest['notes']): ?>
                            <div class="col-12 mt-2">
                                <div class="alert alert-info mb-0"><?php echo htmlspecialchars($latest['notes']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Tracking Updates Table Below -->
            <div class="table-responsive mb-4">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Carrier</th>
                            <th>Tracking #</th>
                            <th>Est. Delivery</th>
                            <th>Updated</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tracking_data as $tracking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tracking['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($tracking['status']); ?></td>
                            <td><?php echo htmlspecialchars($tracking['location']); ?></td>
                            <td><?php echo htmlspecialchars($tracking['carrier_name']); ?></td>
                            <td><?php echo htmlspecialchars($tracking['tracking_number']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($tracking['estimated_delivery_date'])); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($tracking['update_time'])); ?></td>
                            <td><?php echo htmlspecialchars($tracking['notes']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

