<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();

$user_id = $_SESSION['user_data']['id'];

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        
        // Verify order belongs to user
        $check_stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $order_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            if ($_POST['action'] === 'cancel') {
                $update_stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
                $update_stmt->bind_param("i", $order_id);
                $update_stmt->execute();
                header("Location: order_history.php?success=1");
                exit;
            } elseif ($_POST['action'] === 'update_address') {
                $update_stmt = $conn->prepare("
                    UPDATE orders 
                    SET address_line1 = ?,
                        address_line2 = ?,
                        city = ?,
                        state = ?,
                        postal_code = ?,
                        country = ?,
                        phone_number = ?
                    WHERE id = ? AND user_id = ?
                ");
                
                $update_stmt->bind_param(
                    "sssssssii",
                    $_POST['address_line1'],
                    $_POST['address_line2'],
                    $_POST['city'],
                    $_POST['state'],
                    $_POST['postal_code'],
                    $_POST['country'],
                    $_POST['phone_number'],
                    $order_id,
                    $user_id
                );
                
                $update_stmt->execute();
                header("Location: order_history.php?success=1");
                exit;
            }
        }
    }
}

// Fetch all orders with status dates
$stmt = $conn->prepare("
    SELECT o.*, 
           GROUP_CONCAT(oi.product_name, ' (', oi.quantity, ')' SEPARATOR ', ') as items,
           o.created_at as pending_date,
           CASE 
               WHEN o.status = 'processing' THEN o.updated_at
               WHEN o.status IN ('shipped', 'delivered') THEN o.updated_at
           END as processing_date,
           CASE 
               WHEN o.status = 'shipped' THEN o.updated_at
               WHEN o.status = 'delivered' THEN o.updated_at
           END as shipped_date,
           CASE 
               WHEN o.status = 'delivered' THEN o.updated_at
           END as delivered_date
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Lato', sans-serif;
        }

        .order-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .back-button {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            background: #1E1E1E;
            border: none;
            color: #fff;
            font-size: 24px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #2c2c2c;
            color: #fff;
        }

        .order-card {
            background-color: #1E1E1E;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .order-status {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: #FFC107;
            color: #000;
        }

        .status-processing {
            background-color: #17a2b8;
            color: #fff;
        }

        .status-shipped {
            background-color: #00897B;
            color: #fff;
        }

        .status-delivered {
            background-color: #4CAF50;
            color: #fff;
        }

        .status-cancelled {
            background-color: #dc3545;
            color: #fff;
        }

        .order-details {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #3E3E3E;
        }

        .order-items {
            margin-top: 10px;
            padding: 10px;
            background-color: #2c2c2c;
            border-radius: 5px;
        }

        .order-address {
            margin-top: 10px;
            padding: 10px;
            background-color: #2c2c2c;
            border-radius: 5px;
        }

        .order-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .order-id {
            color: #00897B;
            font-weight: bold;
        }

        .order-date {
            color: #888;
            font-size: 0.9rem;
        }

        .order-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #00897B;
            margin-top: 10px;
        }

        .payment-method {
            display: inline-block;
            padding: 3px 8px;
            background-color: #2c2c2c;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        /* Order Tracking Styles */
        .order-tracking {
            margin: 20px 0;
            position: relative;
            display: flex;
            justify-content: space-between;
        }

        .tracking-step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .tracking-icon {
            width: 40px;
            height: 40px;
            background: #2c2c2c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            position: relative;
            z-index: 2;
        }

        .tracking-step.active .tracking-icon {
            background: #00897B;
        }

        .tracking-step.completed .tracking-icon {
            background: #4CAF50;
        }

        .tracking-line {
            position: absolute;
            top: 20px;
            left: 50%;
            right: 50%;
            height: 2px;
            background: #2c2c2c;
            z-index: 1;
        }

        .tracking-step.completed .tracking-line {
            background: #4CAF50;
        }

        .tracking-date {
            font-size: 0.8rem;
            color: #888;
            margin-top: 5px;
        }

        .order-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn-cancel, .btn-update {
            padding: 5px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }

        .btn-update {
            background-color: #00897B;
            color: white;
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Modal Styles */
        .modal-content {
            background-color: #1E1E1E;
            color: #fff;
        }

        .modal-header {
            border-bottom: 1px solid #3E3E3E;
        }

        .modal-footer {
            border-top: 1px solid #3E3E3E;
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
            box-shadow: 0 0 0 0.25rem rgba(0, 137, 123, 0.25);
        }
    </style>
</head>

<body>
    <div class="order-container">
        <div class="profile-header">
            <a href="profile.php" class="back-button">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2>Order History</h2>
        </div>

        <?php if (empty($orders)): ?>
            <div class="text-center mt-5">
                <i class="bi bi-bag-x" style="font-size: 3rem;"></i>
                <h3 class="mt-3">No orders found</h3>
                <p>You haven't placed any orders yet.</p>
                <a href="index.php" class="btn btn-primary mt-3">Start Shopping</a>
            </div>
        <?php endif; ?>

        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div title="Your order has been <?php echo strtolower($order['status']); ?>" class="order-status status-<?php echo strtolower($order['status']); ?>">
                    <?php echo ucfirst($order['status']); ?>
                </div>

                <div class="order-meta">
                    <span class="order-id">Order #<?php echo $order['id']; ?></span>
                    <!-- <span class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span> -->
                </div>

                <?php
                $order_date = strtotime($order['created_at']);
                $current_date = time();
                $hours_passed = ($current_date - $order_date) / 3600;
                $can_modify = $hours_passed <= 24;
                
                $status_steps = ['pending', 'processing', 'shipped', 'delivered'];
                $current_status_index = array_search(strtolower($order['status']), $status_steps);
                ?>

                <div class="order-tracking">
                    <?php foreach ($status_steps as $index => $step): ?>
                        <div class="tracking-step <?php 
                            echo $index < $current_status_index ? 'completed' : 
                                ($index === $current_status_index ? 'active' : ''); 
                        ?>">
                            <?php if ($index > 0): ?>
                                <div class="tracking-line"></div>
                            <?php endif; ?>
                            <div class="tracking-icon">
                                <i class="bi bi-<?php
                                    switch($step) {
                                        case 'pending': echo 'box-seam';
                                            break;
                                        case 'processing': echo 'gear';
                                            break;
                                        case 'shipped': echo 'truck';
                                            break;
                                        case 'delivered': echo 'check-lg';
                                            break;
                                    }
                                ?>"></i>
                            </div>
                            <div class="tracking-label"><?php echo ucfirst($step); ?></div>
                            <div class="tracking-date">
                                <?php 
                                $date_field = $step . '_date';
                                if (isset($order[$date_field]) && $order[$date_field]) {
                                    echo date('d M.', strtotime($order[$date_field]));
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-items">
                    <h5>Items</h5>
                    <p><?php echo htmlspecialchars($order['items']); ?></p>
                </div>

                <div class="order-address">
                    <h5>Shipping Address</h5>
                    <p>
                        <?php echo htmlspecialchars($order['full_name']); ?><br>
                        <?php echo htmlspecialchars($order['address_line1']); ?><br>
                        <?php if (!empty($order['address_line2'])): ?>
                            <?php echo htmlspecialchars($order['address_line2']); ?><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($order['city']); ?>, 
                        <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['postal_code']); ?><br>
                        <?php echo htmlspecialchars($order['country']); ?><br>
                        Phone: <?php echo htmlspecialchars($order['phone_number']); ?>
                    </p>
                </div>

                <div class="order-details">
                    <div class="payment-method">
                        Payment: <?php echo htmlspecialchars($order['payment_method']); ?>
                    </div>
                    <div class="order-total">
                        Total: ₹<?php echo number_format($order['total_amount'], 2); ?>
                    </div>
                    <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'delivered'): ?>
                        <div class="order-actions">
                            <button class="btn-update <?php echo !$can_modify ? 'btn-disabled' : ''; ?>"
                                    <?php echo !$can_modify ? 'disabled' : ''; ?>
                                    onclick="openUpdateModal('<?php echo $order['id']; ?>', 
                                        '<?php echo htmlspecialchars($order['address_line1']); ?>', 
                                        '<?php echo htmlspecialchars($order['address_line2']); ?>', 
                                        '<?php echo htmlspecialchars($order['city']); ?>', 
                                        '<?php echo htmlspecialchars($order['state']); ?>', 
                                        '<?php echo htmlspecialchars($order['postal_code']); ?>', 
                                        '<?php echo htmlspecialchars($order['country']); ?>', 
                                        '<?php echo htmlspecialchars($order['phone_number']); ?>')">
                                <i class="bi bi-pencil me-2"></i>Update Address
                            </button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" class="btn-cancel <?php echo !$can_modify ? 'btn-disabled' : ''; ?>"
                                        <?php echo !$can_modify ? 'disabled' : ''; ?>
                                        onclick="return confirm('क्या आप वाकई इस ऑर्डर को कैंसिल करना चाहते हैं?');">
                                    <i class="bi bi-x-circle me-2"></i>Cancel Order
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Address Update Modal -->
    <div class="modal fade" id="updateAddressModal" tabindex="-1" aria-labelledby="updateAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateAddressModalLabel">Update Shipping Address</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateAddressForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_address">
                        <input type="hidden" name="order_id" id="modal_order_id">
                        
                        <div class="mb-3">
                            <label for="address_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" id="address_line1" name="address_line1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="address_line2" name="address_line2">
                        </div>
                        
                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openUpdateModal(orderId, address1, address2, city, state, postal, country, phone) {
            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('address_line1').value = address1;
            document.getElementById('address_line2').value = address2 || '';
            document.getElementById('city').value = city;
            document.getElementById('state').value = state;
            document.getElementById('postal_code').value = postal;
            document.getElementById('country').value = country;
            document.getElementById('phone_number').value = phone;
            
            new bootstrap.Modal(document.getElementById('updateAddressModal')).show();
        }
    </script>
</body>
</html>