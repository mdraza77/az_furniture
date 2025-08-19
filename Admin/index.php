<?php
require_once 'authentication/check_admin_session.php';
require_once '../config/database.php';

// Get total users count
$users_query = "SELECT COUNT(*) as total FROM users";
$users_result = $conn->query($users_query);
$total_users = $users_result->fetch_assoc()['total'];

// Get total products count
$products_query = "SELECT COUNT(*) as total FROM products";
$products_result = $conn->query($products_query);
$total_products = $products_result->fetch_assoc()['total'];

// Get total orders count
$orders_query = "SELECT COUNT(*) as total FROM orders";
$orders_result = $conn->query($orders_query);
$total_orders = $orders_result->fetch_assoc()['total'];

// Get total categories count
$categories_query = "SELECT COUNT(*) as total FROM categories";
$categories_result = $conn->query($categories_query);
$total_categories = $categories_result->fetch_assoc()['total'];

// Get 5 most recent orders with their items
$recent_orders_query = "
    SELECT 
        o.id,
        o.total_amount,
        o.status,
        o.created_at,
        u.full_name as customer_name,
        GROUP_CONCAT(oi.product_name, ' (', oi.quantity, ')' SEPARATOR ', ') as order_items
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN order_items oi ON o.id = oi.order_id 
    GROUP BY o.id, o.total_amount, o.status, o.created_at, u.full_name
    ORDER BY o.created_at DESC 
    LIMIT 5";
$recent_orders_result = $conn->query($recent_orders_query);

// Get total revenue
$total_revenue_query = "SELECT SUM(total_amount) as total FROM orders";
$total_revenue_result = $conn->query($total_revenue_query);
$total_revenue = $total_revenue_result->fetch_assoc()['total'] ?? 0;

// Get monthly revenue
$monthly_revenue_query = "SELECT SUM(total_amount) as total FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$monthly_revenue_result = $conn->query($monthly_revenue_query);
$monthly_revenue = $monthly_revenue_result->fetch_assoc()['total'] ?? 0;

// Get yearly revenue
$yearly_revenue_query = "SELECT SUM(total_amount) as total FROM orders WHERE YEAR(created_at) = YEAR(CURRENT_DATE())";
$yearly_revenue_result = $conn->query($yearly_revenue_query);
$yearly_revenue = $yearly_revenue_result->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Panel - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/admin-styles.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
</head>
<body>
    <?php include 'inclueds/sidebar.php'; ?>
    <?php include 'inclueds/header.php'; ?>

    <div class="container-fluid">
        <div class="dashboard-header">
            <h2 class="mb-1">Dashboard</h2>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <h3><?php echo $total_products; ?></h3>
                    <p>Total Products</p>
                    <i class="bi bi-box"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3>₹<?php echo number_format($total_revenue, 2); ?></h3>
                    <p>Total Revenue</p>
                    <i class="bi bi-currency-rupee"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3>₹<?php echo number_format($monthly_revenue, 2); ?></h3>
                    <p>Monthly Revenue</p>
                    <!-- <i class="bi bi-calendar-month"></i> -->
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3>₹<?php echo number_format($yearly_revenue, 2); ?></h3>
                    <p>Yearly Revenue</p>
                    <i class="bi bi-calendar-year"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $total_orders; ?></h3>
                    <p>Total Orders</p>
                    <i class="bi bi-cart"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $total_categories; ?></h3>
                    <p>Categories</p>
                    <i class="bi bi-tags"></i>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Recent Orders</h4>
                <a href="orders.php" class="btn btn-primary">View All Orders</a>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $recent_orders_result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_items']); ?></td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($order['status']) {
                                            case 'delivered':
                                                echo 'success';
                                                break;
                                            case 'pending':
                                                echo 'warning';
                                                break;
                                            case 'processing':
                                                echo 'info';
                                                break;
                                            case 'shipped':
                                                echo 'primary';
                                                break;
                                            default:
                                                echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar with Overlay
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close Sidebar on Outside Click
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.toggle-sidebar');
            
            if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target) && window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        });

        // Adjust Layout on Window Resize
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>

    <!-- Products Section -->
    <!-- <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Products</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-lg"></i> Add Product
            </button>
        </div> -->

        <!-- Products Table -->
        <!-- <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Designer</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM products ORDER BY id DESC";
                    $result = $conn->query($query);
                    
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['id']."</td>";
                        echo "<td><img src='../".$row['image_url']."' height='50'></td>";
                        echo "<td>".$row['name']."</td>";
                        echo "<td>".$row['designer']."</td>";
                        echo "<td>₹".number_format($row['price'], 2)."</td>";
                        echo "<td>
                                <button class='btn btn-sm btn-info me-2'><i class='bi bi-pencil'></i></button>
                                <button class='btn btn-sm btn-danger'><i class='bi bi-trash'></i></button>
                            </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div> -->

    <!-- Add Product Modal -->
    <!-- <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="add-product.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" class="form-control bg-secondary text-white" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Designer Name</label>
                                <input type="text" class="form-control bg-secondary text-white" name="designer" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control bg-secondary text-white" name="price" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Image</label>
                                <input type="file" class="form-control bg-secondary text-white" name="image" accept="image/*" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control bg-secondary text-white" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div> -->