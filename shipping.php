<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();

$user_id = $_SESSION['user_data']['id'];

// Store shipping details in database if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $address_line1 = $_POST['address_line1'];
    $address_line2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postal_code = $_POST['postal_code'];
    $country = $_POST['country'];
    $phone_number = $_POST['phone_number'];

    // Create complete shipping address
    $shipping_address = $address_line1;
    if (!empty($address_line2)) {
        $shipping_address .= "\n" . $address_line2;
    }

    // Insert order with shipping details
    $insert_query = "INSERT INTO orders (
        user_id, 
        full_name, 
        shipping_address, 
        address_line1,
        address_line2,
        city, 
        state, 
        postal_code, 
        country, 
        phone_number, 
        status,
        total_amount
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0.00)";

    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param(
        "isssssssss",
        $user_id,
        $full_name,
        $shipping_address,
        $address_line1,
        $address_line2,
        $city,
        $state,
        $postal_code,
        $country,
        $phone_number
    );

    if ($stmt->execute()) {
        $_SESSION['order_id'] = $stmt->insert_id;
        header('Location: payment_selection.php');
        exit();
    } else {
        $error = "Error creating order: " . $conn->error;
    }
}

// Fetch user's saved addresses
$stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$addresses = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Details - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Lato', sans-serif;
        }

        .shipping-form {
            background-color: #1E1E1E;
            border-radius: 15px;
            padding: 20px;
            margin: 20px auto;
            max-width: 600px;
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
        <div class="shipping-form">
            <h2 class="mb-4">Shipping Details</h2>
            <form action="payment_selection.php" method="POST" id="shippingForm">
                <div class="mb-3">
                    <label for="saved_address" class="form-label">Select Saved Address</label>
                    <select class="form-control" id="saved_address" onchange="fillAddressDetails()">
                        <option value="">Select an address</option>
                        <?php foreach ($addresses as $address): ?>
                            <option value="<?php echo htmlspecialchars(json_encode($address)); ?>">
                                <?php echo htmlspecialchars($address['full_name'] . ' - ' . $address['address_line1']); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="new">+ Add New Address</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>

                <div class="mb-3">
                    <label for="address_line1" class="form-label">Address Line 1</label>
                    <textarea class="form-control" id="address_line1" name="address_line1" rows="2" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="address_line2" class="form-label">Address Line 2 (Optional)</label>
                    <textarea class="form-control" id="address_line2" name="address_line2" rows="2"></textarea>
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
                    <input type="text" class="form-control" id="country" name="country" value="India" required>
                </div>

                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                </div>

                <!-- <div class="mb-3">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select class="form-control" id="payment_method" name="payment_method" required>
                        <option value="COD">Cash on Delivery</option>
                        <option value="UPI">UPI</option>
                        <option value="CARD">Credit/Debit Card</option>
                    </select>
                </div> -->

                <button type="button" class="btn btn-primary w-100" onclick="validateAndProceed()">Continue to Payment</button>
            </form>
            <script>
                function fillAddressDetails() {
                    const selectElement = document.getElementById('saved_address');
                    const selectedValue = selectElement.value;

                    if (selectedValue === 'new') {
                        // Clear all fields for new address
                        document.getElementById('full_name').value = '';
                        document.getElementById('address_line1').value = '';
                        document.getElementById('address_line2').value = '';
                        document.getElementById('city').value = '';
                        document.getElementById('state').value = '';
                        document.getElementById('postal_code').value = '';
                        document.getElementById('country').value = 'India';
                        document.getElementById('phone_number').value = '';
                        return;
                    }

                    if (selectedValue) {
                        const addressData = JSON.parse(selectedValue);
                        document.getElementById('full_name').value = addressData.full_name;
                        document.getElementById('address_line1').value = addressData.address_line1;
                        document.getElementById('address_line2').value = addressData.address_line2 || '';
                        document.getElementById('city').value = addressData.city;
                        document.getElementById('state').value = addressData.state;
                        document.getElementById('postal_code').value = addressData.postal_code;
                        document.getElementById('country').value = addressData.country;
                        document.getElementById('phone_number').value = addressData.phone_number || '';
                    }
                }

                function validateAndProceed() {
                    const form = document.getElementById('shippingForm');
                    const savedAddress = document.getElementById('saved_address').value;
                    const fullName = document.getElementById('full_name').value.trim();
                    const addressLine1 = document.getElementById('address_line1').value.trim();
                    const city = document.getElementById('city').value.trim();
                    const state = document.getElementById('state').value.trim();
                    const postalCode = document.getElementById('postal_code').value.trim();
                    const phoneNumber = document.getElementById('phone_number').value.trim();

                    if (!fullName || !addressLine1 || !city || !state || !postalCode || !phoneNumber) {
                        alert('Please fill in all required fields');
                        return;
                    }

                    // Save form data to session storage
                    const formData = new FormData(form);
                    const formObject = {};
                    formData.forEach((value, key) => {
                        formObject[key] = value;
                    });
                    sessionStorage.setItem('shippingData', JSON.stringify(formObject));

                    form.submit();
                }
            </script>
        </div>
    </div>
</body>

</html>