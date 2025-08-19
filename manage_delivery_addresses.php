<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();

$user_id = $_SESSION['user_data']['id'];

// Fetch all addresses for the user
$stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$addresses = $result->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        $address_id = $_POST['address_id'] ?? null;
        $address_type = $_POST['address_type'];
        $full_name = $_POST['full_name'];
        $address_line1 = $_POST['address_line1'];
        $address_line2 = $_POST['address_line2'] ?? null;
        $city = $_POST['city'];
        $state = $_POST['state'];
        $postal_code = $_POST['postal_code'];
        $country = $_POST['country'] ?? 'India';
        $phone_number = $_POST['phone_number'] ?? null;
        $is_default = isset($_POST['is_default']) ? 1 : 0;

        if ($is_default) {
            // Reset all other addresses to non-default
            $stmt = $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO user_addresses (user_id, address_type, full_name, address_line1, address_line2, city, state, postal_code, country, phone_number, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssssssi", $user_id, $address_type, $full_name, $address_line1, $address_line2, $city, $state, $postal_code, $country, $phone_number, $is_default);
        } else {
            $stmt = $conn->prepare("UPDATE user_addresses SET address_type = ?, full_name = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, country = ?, phone_number = ?, is_default = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssssssssiis", $address_type, $full_name, $address_line1, $address_line2, $city, $state, $postal_code, $country, $phone_number, $is_default, $address_id, $user_id);
        }

        if ($stmt->execute()) {
            header("Location: manage_delivery_addresses.php?success=1");
            exit;
        }
    } elseif ($action === 'delete') {
        $address_id = $_POST['address_id'] ?? null;
        $stmt = $conn->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $address_id, $user_id);
        if ($stmt->execute()) {
            header("Location: manage_delivery_addresses.php?success=1");
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
    <title>Manage Delivery Addresses - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Lato', sans-serif;
        }

        .address-container {
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

        .address-card {
            background-color: #1E1E1E;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .address-type {
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

        .address-actions {
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

        .add-address-btn {
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
    <div class="address-container">
        <div class="profile-header">
            <a href="profile.php" class="back-button">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2>Manage Addresses</h2>
        </div>

        <button class="add-address-btn" data-bs-toggle="modal" data-bs-target="#addressModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Address
        </button>

        <?php foreach ($addresses as $address): ?>
            <div class="address-card">
                <div class="address-type">
                    <?php echo ucfirst($address['address_type']); ?>
                    <?php if ($address['is_default']): ?>
                        <span class="default-badge">Default</span>
                    <?php endif; ?>
                </div>
                <h4><?php echo htmlspecialchars($address['full_name']); ?></h4>
                <p><?php echo htmlspecialchars($address['address_line1']); ?></p>
                <?php if ($address['address_line2']): ?>
                    <p><?php echo htmlspecialchars($address['address_line2']); ?></p>
                <?php endif; ?>
                <p>
                    <?php echo htmlspecialchars($address['city']); ?>,
                    <?php echo htmlspecialchars($address['state']); ?> -
                    <?php echo htmlspecialchars($address['postal_code']); ?>
                </p>
                <p><?php echo htmlspecialchars($address['country']); ?></p>
                <?php if ($address['phone_number']): ?>
                    <p>Phone: <?php echo htmlspecialchars($address['phone_number']); ?></p>
                <?php endif; ?>
                <div class="address-actions">
                    <button class="btn-edit" onclick="editAddress(<?php echo htmlspecialchars(json_encode($address)); ?>)">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </button>
                    <button class="btn-delete" onclick="deleteAddress(<?php echo $address['id']; ?>)">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Address Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Address</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addressForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="address_id" id="addressId">

                        <div class="mb-3">
                            <label class="form-label">Address Type</label>
                            <select name="address_type" class="form-control" required>
                                <option value="home">Home</option>
                                <option value="office">Office</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_line1" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control" required maxlength="6" pattern="[0-9]{6}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)" title="Please enter a valid 6-digit postal code">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="India">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone_number" class="form-control">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_default" class="form-check-input" id="isDefault">
                            <label class="form-check-label" for="isDefault">Set as default address</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="address_id" id="deleteAddressId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const addressModal = new bootstrap.Modal(document.getElementById('addressModal'));

        function editAddress(address) {
            document.getElementById('modalTitle').textContent = 'Edit Address';
            document.getElementById('formAction').value = 'update';
            document.getElementById('addressId').value = address.id;

            const form = document.getElementById('addressForm');
            form.elements['address_type'].value = address.address_type;
            form.elements['full_name'].value = address.full_name;
            form.elements['address_line1'].value = address.address_line1;
            form.elements['address_line2'].value = address.address_line2 || '';
            form.elements['city'].value = address.city;
            form.elements['state'].value = address.state;
            form.elements['postal_code'].value = address.postal_code;
            form.elements['country'].value = address.country;
            form.elements['phone_number'].value = address.phone_number || '';
            form.elements['is_default'].checked = address.is_default == 1;

            addressModal.show();
        }

        function deleteAddress(addressId) {
            if (confirm('Are you sure you want to delete this address?')) {
                document.getElementById('deleteAddressId').value = addressId;
                document.getElementById('deleteForm').submit();
            }
        }

        document.getElementById('addressModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('modalTitle').textContent = 'Add New Address';
            document.getElementById('formAction').value = 'add';
            document.getElementById('addressId').value = '';
            document.getElementById('addressForm').reset();
        });
    </script>
</body>

</html>