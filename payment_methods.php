<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();

$user_id = $_SESSION['user_data']['id'];

// Fetch all payment methods for the user
$stmt = $conn->prepare("SELECT * FROM payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$payment_methods = $result->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        $payment_id = $_POST['payment_id'] ?? null;
        $card_holder = $_POST['card_holder'];
        $card_number = $_POST['card_number'];
        $expiry_month = $_POST['expiry_month'];
        $expiry_year = $_POST['expiry_year'];
        $card_type = $_POST['card_type'];
        $is_default = isset($_POST['is_default']) ? 1 : 0;

        if ($is_default) {
            // Reset all other payment methods to non-default
            $stmt = $conn->prepare("UPDATE payment_methods SET is_default = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }

        // Mask card number except last 4 digits
        $masked_card_number = str_repeat('*', strlen($card_number) - 4) . substr($card_number, -4);

        if ($action === 'add') {
            if ($card_type === 'upi') {
                $stmt = $conn->prepare("INSERT INTO payment_methods (user_id, upi_id, card_type, is_default) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $user_id, $card_number, $card_type, $is_default);
            } else {
                $stmt = $conn->prepare("INSERT INTO payment_methods (user_id, card_holder, card_number, expiry_month, expiry_year, card_type, is_default) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issiisi", $user_id, $card_holder, $masked_card_number, $expiry_month, $expiry_year, $card_type, $is_default);
            }
        } else {
            if ($card_type === 'upi') {
                $stmt = $conn->prepare("UPDATE payment_methods SET upi_id = ?, is_default = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("siii", $card_number, $is_default, $payment_id, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE payment_methods SET card_holder = ?, card_number = ?, expiry_month = ?, expiry_year = ?, card_type = ?, is_default = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ssiisiii", $card_holder, $masked_card_number, $expiry_month, $expiry_year, $card_type, $is_default, $payment_id, $user_id);
            }
        }

        if ($stmt->execute()) {
            header("Location: payment_methods.php?success=1");
            exit;
        }
    } elseif ($action === 'delete') {
        $payment_id = $_POST['payment_id'] ?? null;
        $stmt = $conn->prepare("DELETE FROM payment_methods WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $payment_id, $user_id);
        if ($stmt->execute()) {
            header("Location: payment_methods.php?success=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Lato', sans-serif;
        }

        .payment-container {
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

        .payment-card {
            background-color: #1E1E1E;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .card-type {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #00897B;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
        }

        .default-badge {
            background-color: #4CAF50;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            margin-left: 10px;
        }

        .card-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn-edit,
        .btn-delete {
            padding: 5px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-edit {
            background-color: #00897B;
            color: white;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .add-card-btn {
            background-color: #00897B;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            width: 100%;
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
            box-shadow: none;
        }

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
    </style>
</head>

<body>
    <div class="payment-container">
        <div class="profile-header">
            <a href="profile.php" class="back-button">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2>Payment Methods</h2>
        </div>

        <button class="add-card-btn" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Card
        </button>

        <?php foreach ($payment_methods as $method): ?>
            <div class="payment-card">
                <div class="card-type">
                    <?php echo ucfirst($method['card_type']); ?>
                    <?php if ($method['is_default']): ?>
                        <span class="default-badge">Default</span>
                    <?php endif; ?>
                </div>
                <?php if ($method['card_type'] === 'upi'): ?>
                    <h4>UPI Payment</h4>
                    <p>UPI ID: <?php echo htmlspecialchars($method['upi_id']); ?></p>
                <?php else: ?>
                    <h4><?php echo htmlspecialchars($method['card_holder']); ?></h4>
                    <p>Card Number: <?php echo htmlspecialchars($method['card_number']); ?></p>
                    <p>Expires: <?php echo sprintf('%02d/%d', $method['expiry_month'], $method['expiry_year']); ?></p>
                <?php endif; ?>
                <div class="card-actions">
                    <button class="btn-edit" onclick="editPaymentMethod(<?php echo htmlspecialchars(json_encode($method)); ?>)">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </button>
                    <button class="btn-delete" onclick="deletePaymentMethod(<?php echo $method['id']; ?>)">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Payment Method Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Card</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="paymentForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="payment_id" id="paymentId">

                        <div class="mb-3">
                            <label for="card_type" class="form-label">Payment Type</label>
                            <select class="form-control" id="card_type" name="card_type" required onchange="togglePaymentFields()">
                                <option value="visa">Visa</option>
                                <option value="mastercard">Mastercard</option>
                                <option value="amex">American Express</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>

                        <div id="card-fields">
                            <div class="mb-3">
                                <label for="card_holder" class="form-label">Card Holder Name</label>
                                <input type="text" class="form-control" id="card_holder" name="card_holder">
                            </div>

                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" maxlength="16" pattern="\d{16}">
                            </div>

                            <div class="row mb-3">
                                <div class="col">
                                    <label for="expiry_month" class="form-label">Expiry Month</label>
                                    <select class="form-control" id="expiry_month" name="expiry_month">
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo sprintf('%02d', $i); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="expiry_year" class="form-label">Expiry Year</label>
                                    <select class="form-control" id="expiry_year" name="expiry_year">
                                        <?php 
                                        $current_year = date('Y');
                                        for($i = $current_year; $i <= $current_year + 10; $i++): 
                                        ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="upi-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="upi_id" class="form-label">UPI ID</label>
                                <input type="text" class="form-control" id="upi_id" name="card_number" placeholder="example@upi">
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_default" name="is_default">
                            <label class="form-check-label" for="is_default">Set as default payment method</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePaymentFields() {
            const cardType = document.getElementById('card_type').value;
            const cardFields = document.getElementById('card-fields');
            const upiFields = document.getElementById('upi-fields');
            
            if (cardType === 'upi') {
                cardFields.style.display = 'none';
                upiFields.style.display = 'block';
                document.getElementById('upi_id').required = true;
                document.getElementById('card_holder').required = false;
                document.getElementById('card_number').required = false;
                document.getElementById('expiry_month').required = false;
                document.getElementById('expiry_year').required = false;
            } else {
                cardFields.style.display = 'block';
                upiFields.style.display = 'none';
                document.getElementById('upi_id').required = false;
                document.getElementById('card_holder').required = true;
                document.getElementById('card_number').required = true;
                document.getElementById('expiry_month').required = true;
                document.getElementById('expiry_year').required = true;
            }
        }

        function editPaymentMethod(method) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('paymentId').value = method.id;
            document.getElementById('card_type').value = method.card_type;
            document.getElementById('is_default').checked = method.is_default == 1;
            
            if (method.card_type === 'upi') {
                document.getElementById('upi_id').value = method.upi_id;
            } else {
                document.getElementById('card_holder').value = method.card_holder;
            }
            
            togglePaymentFields();
            document.getElementById('modalTitle').textContent = 'Edit Payment Method';
            var modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        }

        function deletePaymentMethod(id) {
            if (confirm('Are you sure you want to delete this payment method?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="payment_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Card number validation
        document.getElementById('card_number').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 16);
        });
    </script>
</body>
</html>