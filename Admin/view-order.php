<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['id']);

// Get order details with customer information
$order_query = "SELECT o.*, 
                u.full_name as user_full_name, 
                u.email as user_email, 
                u.phone_number as user_phone,
                u.city as user_city,
                u.state as user_state,
                u.country as user_country
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = ?";

$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/admin-styles.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
</head>
<body>
    <?php include 'inclueds/sidebar.php'; ?>
    <?php include 'inclueds/header.php'; ?>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card bg-dark text-white">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order #<?php echo $order_id; ?></h5>
                        <a href="orders.php" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Customer Information</h6>
                                <p class="mb-1">Name: <?php echo htmlspecialchars($order['user_full_name']); ?></p>
                                <p class="mb-1">Email: <?php echo htmlspecialchars($order['user_email']); ?></p>
                                <p class="mb-1">Phone: <?php echo htmlspecialchars($order['user_phone']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Order Status</h6>
                                <p class="mb-1">Status: <span class="badge bg-<?php echo getStatusColor($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></p>
                                <p class="mb-1">Order Date: <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                <p class="mb-1">Payment Method: <?php echo ucfirst($order['payment_method']); ?></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted">Shipping Address</h6>
                                <p class="mb-1">Recipient: <?php echo htmlspecialchars($order['full_name']); ?></p>
                                <p class="mb-1">Contact: <?php echo htmlspecialchars($order['phone_number']); ?></p>
                                <p class="mb-1"><?php echo htmlspecialchars($order['address_line1']); ?></p>
                                <?php if ($order['address_line2']): ?>
                                    <p class="mb-1"><?php echo htmlspecialchars($order['address_line2']); ?></p>
                                <?php endif; ?>
                                <p class="mb-1">
                                    <?php echo htmlspecialchars($order['city']); ?>, 
                                    <?php echo htmlspecialchars($order['state']); ?> 
                                    <?php echo htmlspecialchars($order['postal_code']); ?>
                                </p>
                                <p class="mb-1"><?php echo htmlspecialchars($order['country']); ?></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted">Order Items</h6>
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td class="text-end">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td class="text-end"><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-muted">Update Tracking Information</h6>
                                <form id="trackingForm" class="mt-3">
                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Product</label>
                                            <select name="product_id" class="form-select" required>
                                                <?php foreach ($items as $item): ?>
                                                    <option value="<?php echo $item['product_id']; ?>">
                                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select" required>
                                                <?php
                                                // Get status options from database
                                                $status_query = "SELECT DISTINCT status FROM orders";
                                                $status_result = $conn->query($status_query);
                                                while ($status_row = $status_result->fetch_assoc()) {
                                                    $status = $status_row['status'];
                                                    echo '<option value="' . htmlspecialchars($status) . '">' . 
                                                         ucfirst(htmlspecialchars($status)) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Location</label>
                                            <input type="text" name="location" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Carrier Name</label>
                                            <input type="text" name="carrier_name" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tracking Number</label>
                                            <input type="text" name="tracking_number" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Estimated Delivery Date</label>
                                            <input type="date" name="estimated_delivery_date" class="form-control" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Notes</label>
                                            <textarea name="notes" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">Update Tracking</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tracking History Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-muted">Tracking History</h6>
                                <div class="table-responsive mt-3">
                                    <table class="table table-dark table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Product</th>
                                                <th>Status</th>
                                                <th>Location</th>
                                                <th>Carrier</th>
                                                <th>Tracking #</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody id="trackingHistory">
                                            <?php
                                            $tracking_query = "SELECT ot.*, p.name as product_name 
                                                             FROM order_tracking ot 
                                                             JOIN products p ON ot.product_id = p.id 
                                                             WHERE ot.order_id = ? 
                                                             ORDER BY ot.update_time DESC";
                                            $tracking_stmt = $conn->prepare($tracking_query);
                                            $tracking_stmt->bind_param("i", $order_id);
                                            $tracking_stmt->execute();
                                            $tracking_result = $tracking_stmt->get_result();
                                            while ($tracking = $tracking_result->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td><?php echo date('Y-m-d H:i', strtotime($tracking['update_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($tracking['product_name']); ?></td>
                                                <td><span class="badge bg-<?php echo getStatusColor($tracking['status']); ?>"><?php echo ucfirst($tracking['status']); ?></span></td>
                                                <td><?php echo htmlspecialchars($tracking['location']); ?></td>
                                                <td><?php echo htmlspecialchars($tracking['carrier_name']); ?></td>
                                                <td><?php echo htmlspecialchars($tracking['tracking_number']); ?></td>
                                                <td><?php echo htmlspecialchars($tracking['notes']); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('trackingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.entries(formData);
        console.log(data);

        fetch('update-tracking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Tracking information updated successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating tracking information.');
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>