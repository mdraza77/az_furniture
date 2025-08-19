<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/admin-styles.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
</head>
<body>
    <?php include 'inclueds/sidebar.php'; ?>
    <?php include 'inclueds/header.php'; ?>

    <?php
    $count_query = "SELECT COUNT(*) as total FROM orders";
    $count_result = $conn->query($count_query);
    $total_orders = $count_result->fetch_assoc()['total'];
    ?>

    <!-- Orders Section -->
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Orders (<?php echo $total_orders; ?>)</h2>
            <div class="col-md-4">
                <input type="text" id="searchInput" class="form-control bg-dark text-white search-input" placeholder="Search orders...">
            </div>
            
            <style>
            .search-input::placeholder {
                color: white !important;
                opacity: 1;
            }
            
            .search-input::-moz-placeholder {
                color: white !important;
                opacity: 1;
            }
            
            .search-input::-webkit-input-placeholder {
                color: white !important;
                opacity: 1;
            }
            </style>
        </div>

        <!-- Orders Table -->
        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT o.*, u.full_name as customer_name 
                             FROM orders o 
                             JOIN users u ON o.user_id = u.id 
                             ORDER BY o.created_at DESC";
                    $result = $conn->query($query);
                    
                    while($row = $result->fetch_assoc()) {
                        $status_badge = '';
                        switch($row['status']) {
                            case 'pending':
                                $status_badge = 'bg-warning';
                                break;
                            case 'processing':
                                $status_badge = 'bg-info';
                                break;
                            case 'shipped':
                                $status_badge = 'bg-primary';
                                break;
                            case 'delivered':
                                $status_badge = 'bg-success';
                                break;
                            case 'cancelled':
                                $status_badge = 'bg-danger';
                                break;
                        }
                        
                        echo "<tr>";
                        echo "<td>#".$row['id']."</td>";
                        echo "<td>".$row['customer_name']."</td>";
                        echo "<td>₹".number_format($row['total_amount'], 2)."</td>";
                        echo "<td><span class='badge ".$status_badge."'>".ucfirst($row['status'])."</span></td>";
                        echo "<td>".date('d M Y H:i', strtotime($row['created_at']))."</td>";
                        echo "<td>
                                <div class='dropdown'>
                                    <button class='btn btn-sm btn-info me-2' onclick='updateOrderStatus(".$row['id'].")'><i class='bi bi-pencil'></i></button>
                                    <a href='view-order.php?id=".$row['id']."' class='btn btn-sm btn-secondary'><i class='bi bi-eye'></i></a>
                                </div>
                            </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Update Order Status</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="#" method="POST" id="updateStatusForm">
                    <input type="hidden" name="order_id" id="status_order_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select bg-secondary text-white" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function updateOrderStatus(orderId) {
        document.getElementById('status_order_id').value = orderId;
        
        const statusSelect = document.querySelector('select[name="status"]');
        statusSelect.value = ''; // Reset previous selection
        
        new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
    }
    
    document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('update-order-status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('updateStatusModal')).hide();
                window.location.reload();
            } else {
                alert('Error updating order status: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating order status');
        });
    });
    
    function viewOrderDetails(orderId) {
        fetch(`get-order-details.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                let content = `
                    <div class="mb-4">
                        <h6 class="text-muted">Customer Information</h6>
                        <p class="mb-1">Name: ${data.customer.name}</p>
                        <p class="mb-1">Email: ${data.customer.email}</p>
                        <p class="mb-1">Phone: ${data.customer.phone}</p>
                    </div>
                    <div class="mb-4">
                        <h6 class="text-muted">Shipping Address</h6>
                        <p class="mb-1">${data.shipping.address}</p>
                        <p class="mb-1">${data.shipping.city}, ${data.shipping.state} ${data.shipping.postal_code}</p>
                        <p class="mb-1">${data.shipping.country}</p>
                    </div>
                    <div class="mb-4">
                        <h6 class="text-muted">Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-dark table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                
                data.items.forEach(item => {
                    content += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>₹${item.price.toFixed(2)}</td>
                            <td>${item.quantity}</td>
                            <td>₹${(item.price * item.quantity).toFixed(2)}</td>
                        </tr>`;
                });
                
                content += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Payment Information</h6>
                            <p class="mb-1">Method: ${data.payment.method}</p>
                            <p class="mb-1">Status: ${data.payment.status}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Order Summary</h6>
                            <p class="mb-1">Subtotal: ₹${data.summary.subtotal}</p>
                            <p class="mb-1">Shipping: ₹${data.summary.shipping}</p>
                            <p class="mb-1">Tax: ₹${data.summary.tax}</p>
                            <p class="mb-1 fw-bold">Total: ₹${data.summary.total}</p>
                        </div>
                    </div>`;

                document.getElementById('orderDetailsContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading order details');
            });
    }

    document.getElementById('searchInput').addEventListener('keyup', function() {
        let searchValue = this.value.toLowerCase();
        let tableRows = document.querySelectorAll('table tbody tr');
        
        tableRows.forEach(row => {
            let text = row.textContent.toLowerCase();
            if(text.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>
