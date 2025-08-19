<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: shipping.php');
    exit;
}

// Get cart total from database
$user_id = $_SESSION['user_data']['id'];
$query = "SELECT o.*, p.image_url 
          FROM cart o 
          LEFT JOIN products p ON o.product_id = p.id 
          WHERE o.user_id = ? 
          ORDER BY o.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total
$total = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total += $row['price'] * $row['quantity'];
    }
}

// Only keep COD charges, remove discounts
$cod_charges = 49; // COD handling charges
$final_total = $total;

// Store the total in POST data
$_POST['total_amount'] = $final_total;
$_POST['cod_charges'] = $cod_charges;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Selection - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Lato', sans-serif;
        }

        .payment-selection {
            background-color: #1E1E1E;
            border-radius: 15px;
            padding: 20px;
            margin: 20px auto;
            max-width: 600px;
        }

        .payment-option {
            background-color: #2c2c2c;
            border: 1px solid #3E3E3E;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-option:hover {
            border-color: #00897B;
        }

        .payment-option.selected {
            border-color: #00897B;
            background-color: #1E1E1E;
        }

        .btn-primary {
            background-color: #00897B;
            border: none;
        }

        .btn-primary:hover {
            background-color: #007366;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="payment-selection">
            <h2 class="mb-4">Select Payment Method</h2>

            <!-- Store shipping data -->
            <form id="paymentForm">
                <?php foreach ($_POST as $key => $value): ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                <?php endforeach; ?>

                <div class="payment-options">
                    <div class="payment-option" onclick="selectPayment('COD')">
                        <h4><i class="bi bi-cash"></i> Cash on Delivery</h4>
                        <p class="mb-0">Pay when your order arrives</p>
                        <div class="payment-details cod-details" id="codDetails" style="display: none;">
                            <div class="mt-3 p-3 bg-dark rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Order Amount:</span>
                                    <span>₹<?php echo number_format($_POST['total_amount'] ?? 0, 2); ?></span>
                                </div>
                                <hr class="border-secondary">
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total Amount:</span>
                                    <span>₹<?php echo number_format($_POST['total_amount'] ?? 0, 2); ?></span>
                                </div>
                                <small class="text-muted d-block mt-2">Cash payment will be collected at the time of delivery</small>
                            </div>
                        </div>
                    </div>

                    <div class="payment-option" onclick="selectPayment('UPI')">
                        <h4><i class="bi bi-phone"></i> UPI Payment</h4>
                        <p class="mb-0">Pay using any UPI app</p>
                        <div class="payment-details upi-details" id="upiDetails" style="display: none;">
                            <div class="mt-3">
                                <div class="mb-3">
                                    <label class="form-label">Amount to Pay</label>
                                    <div class="bg-dark p-2 rounded mb-3">₹<?php echo number_format($_POST['total_amount'] ?? 0, 2); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label for="upiId" class="form-label">UPI ID</label>
                                    <input type="text" class="form-control" id="upiId" placeholder="username@bank" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-option" onclick="selectPayment('CARD')">
                        <h4><i class="bi bi-credit-card"></i> Credit/Debit Card</h4>
                        <p class="mb-0">Pay using your card</p>
                        <div class="payment-details card-details" id="cardDetails" style="display: none;">
                            <div class="mt-3">
                                <div class="mb-3">
                                    <label class="form-label">Amount to Pay</label>
                                    <div class="bg-dark p-2 rounded mb-3">₹<?php echo number_format($_POST['total_amount'] ?? 0, 2); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label for="cardNumber" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCardNumber(this)" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col">
                                        <label for="expiry" class="form-label">Expiry Date</label>
                                        <div class="d-flex">
                                            <select class="form-control me-2" id="expiryMonth" required>
                                                <option value="">MM</option>
                                                <?php
                                                for ($i = 1; $i <= 12; $i++) {
                                                    $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                    echo "<option value=\"$month\">$month</option>";
                                                }
                                                ?>
                                            </select>
                                            <select class="form-control" id="expiryYear" required>
                                                <option value="">YY</option>
                                                <?php
                                                $currentYear = date('Y');
                                                $currentYearShort = date('y');
                                                for ($i = 0; $i <= 5; $i++) {
                                                    $year = $currentYear + $i;
                                                    $yearShort = ($currentYearShort + $i) % 100;
                                                    $yearShort = str_pad($yearShort, 2, '0', STR_PAD_LEFT);
                                                    echo "<option value=\"$yearShort\">$yearShort</option>";
                                                }
                                                ?>
                                            </select>
                                            <input type="hidden" id="expiry" value="">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="password" class="form-control" id="cvv" placeholder="123" maxlength="3" oninput="this.value=this.value.replace(/\D/g,'')" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="cardName" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control" id="cardName" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-primary w-100 mt-4" onclick="proceedToPayment()" disabled id="continueBtn">
                    Pay Now
                </button>
        </div>
    </div>

    <script>
        let selectedPayment = '';

        function selectPayment(method) {
            selectedPayment = method;
            // Hide all payment details
            document.querySelectorAll('.payment-details').forEach(detail => {
                detail.style.display = 'none';
            });

            // Remove selected class from all options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });

            // Show selected payment details and highlight option
            event.currentTarget.classList.add('selected');
            const detailsId = method.toLowerCase() + 'Details';
            document.getElementById(detailsId).style.display = 'block';
            document.getElementById('continueBtn').disabled = false;
            // Update button text based on payment method
            const continueBtn = document.getElementById('continueBtn');
            if (method === 'COD') {
                const total = <?php echo $_POST['total_amount']; ?>;
                const codCharges = <?php echo $cod_charges; ?>;
                continueBtn.textContent = `Pay ₹${(total + codCharges).toFixed(2)} on Delivery`;
            } else {
                continueBtn.textContent = `Pay ₹<?php echo number_format($_POST['total_amount'], 2); ?> Now`;
            }
        }

        function validateCardNumber(cardNumber) {
            // Remove spaces and check if it's 16 digits
            return cardNumber.replace(/\s/g, '').match(/^\d{16}$/);
        }

        function validateExpiry(expiry) {
            if (!expiry.match(/^(0[1-9]|1[0-2])\/([0-9]{2})$/)) return false;

            const [month, year] = expiry.split('/');
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear() % 100;
            const currentMonth = currentDate.getMonth() + 1;
            const maxYear = (currentYear + 5) % 100;

            const selectedYear = parseInt(year);

            // Check if year is within valid range
            if (selectedYear < currentYear || selectedYear > maxYear) return false;

            // If it's current year, check if month is valid
            if (selectedYear === currentYear && parseInt(month) < currentMonth) return false;

            return true;
        }

        function validateUpiId(upiId) {
            return /^[a-zA-Z0-9\.\-_]{2,256}@[a-zA-Z][a-zA-Z]{2,64}$/.test(upiId);
        }

        function proceedToPayment() {
            if (!selectedPayment) {
                alert('Please select a payment method');
                return;
            }

            // Validate payment details based on method
            if (selectedPayment === 'UPI') {
                const upiId = document.getElementById('upiId').value;
                if (!upiId) {
                    alert('Please enter UPI ID');
                    return;
                }
                if (!validateUpiId(upiId)) {
                    alert('Please enter a valid UPI ID (e.g., username@bank)');
                    return;
                }
            } else if (selectedPayment === 'CARD') {
                const cardNumber = document.getElementById('cardNumber').value;
                const expiry = document.getElementById('expiry').value;
                const cvv = document.getElementById('cvv').value;
                const cardName = document.getElementById('cardName').value;

                if (!cardNumber || !expiry || !cvv || !cardName) {
                    alert('Please fill all card details');
                    return;
                }

                // Validate card number
                if (!validateCardNumber(cardNumber)) {
                    alert('Please enter a valid 16-digit card number');
                    return;
                }

                // Validate expiry date
                if (!validateExpiry(expiry)) {
                    alert('Please enter a valid expiry date (MM/YY) that is not in the past');
                    return;
                }

                // Validate CVV
                if (!cvv.match(/^\d{3}$/)) {
                    alert('Please enter a valid 3-digit CVV');
                    return;
                }

                // Validate card name
                if (!cardName.trim() || cardName.trim().length < 3 || !/^[a-zA-Z\s]+$/.test(cardName)) {
                    alert('Please enter a valid name as shown on your card');
                    return;
                }
            }

            // Show confirmation alert
            const amount = selectedPayment === 'COD' ?
                <?php echo $_POST['total_amount']; ?> :
                <?php echo $_POST['total_amount']; ?>;

            const confirmMessage = selectedPayment === 'COD' ?
                `Confirm order with Cash on Delivery? Amount to be paid: ₹${amount.toFixed(2)}` :
                `Confirm payment of ₹${amount.toFixed(2)}?`;

            if (!confirm(confirmMessage)) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'process_order.php';

            // Add all shipping data from the original form
            const originalForm = document.getElementById('paymentForm');
            const hiddenInputs = originalForm.querySelectorAll('input[type="hidden"]');
            hiddenInputs.forEach(input => {
                const clone = input.cloneNode(true);
                form.appendChild(clone);
            });

            // Add payment details
            const paymentInput = document.createElement('input');
            paymentInput.type = 'hidden';
            paymentInput.name = 'payment_method';
            paymentInput.value = selectedPayment;
            form.appendChild(paymentInput);

            if (selectedPayment === 'UPI') {
                const upiInput = document.createElement('input');
                upiInput.type = 'hidden';
                upiInput.name = 'upi_id';
                upiInput.value = document.getElementById('upiId').value;
                form.appendChild(upiInput);
            } else if (selectedPayment === 'CARD') {
                const cardInputs = {
                    'card_number': 'cardNumber',
                    'card_expiry': 'expiry',
                    'card_name': 'cardName'
                };

                for (const [key, id] of Object.entries(cardInputs)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = document.getElementById(id).value;
                    form.appendChild(input);
                }
            }

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>

</html>

<script>
    function formatCardNumber(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 16) value = value.slice(0, 16);

        let formattedValue = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formattedValue += ' ';
            }
            formattedValue += value[i];
        }
        input.value = formattedValue;
    }

    function formatExpiry(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length >= 2) {
            const month = parseInt(value.substring(0, 2));
            if (month > 12) value = '12' + value.substring(2);
            value = value.substring(0, 2) + '/' + value.substring(2);
        }
        input.value = value;
    }

    // Add this new function to handle expiry date selection
    document.getElementById('expiryMonth').addEventListener('change', updateExpiry);
    document.getElementById('expiryYear').addEventListener('change', updateExpiry);

    function updateExpiry() {
        const month = document.getElementById('expiryMonth').value;
        const year = document.getElementById('expiryYear').value;
        if (month && year) {
            document.getElementById('expiry').value = month + '/' + year;
        } else {
            document.getElementById('expiry').value = '';
        }
    }

    // Update the validateExpiry function
    function validateExpiry(expiry) {
        if (!expiry.match(/^(0[1-9]|1[0-2])\/([0-9]{2})$/)) return false;

        const [month, year] = expiry.split('/');
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear() % 100;
        const currentMonth = currentDate.getMonth() + 1;
        const maxYear = (currentYear + 5) % 100;

        const selectedYear = parseInt(year);

        // Check if year is within valid range
        if (selectedYear < currentYear || selectedYear > maxYear) return false;

        // If it's current year, check if month is valid
        if (selectedYear === currentYear && parseInt(month) < currentMonth) return false;

        return true;
    }
</script>