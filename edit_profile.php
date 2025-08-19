<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();

$user_id = $_SESSION['user_data']['id'];

// Fetch fresh user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = $_POST['field'];
    $value = $_POST['value'];

    $allowed_fields = ['full_name', 'email', 'phone_number', 'address', 'city', 'state', 'postal_code', 'country'];

    if (in_array($field, $allowed_fields)) {
        $stmt = $conn->prepare("UPDATE users SET $field = ? WHERE id = ?");
        $stmt->bind_param("si", $value, $user_id);

        if ($stmt->execute()) {
            // Update session data
            $_SESSION['user_data'][$field] = $value;
            echo json_encode(['success' => true]);
            exit;
        }
    }
    echo json_encode(['success' => false]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Az Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Lato', sans-serif;
        }

        .profile-container {
            max-width: 600px;
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

        .profile-field {
            background-color: #1E1E1E;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .field-label {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .field-value {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .edit-button {
            background: none;
            border: none;
            color: #00897B;
            cursor: pointer;
        }

        .edit-button:hover {
            color: #00695C;
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

        .save-button {
            padding: 8px 16px;
            font-size: 1.2rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .save-button:hover {
            background-color: rgba(0, 137, 123, 0.1);
        }

        .profile-menu-item.mt-4 {
            background: none;
            border: none;
            width: 100%;
            text-align: center;
            padding: 15px;
            margin-top: 20px;
            font-size: 16px;
            text-decoration: none;
            display: block;
            color: #fff;
            transition: color 0.3s;
        }

        .profile-menu-item.mt-4:hover {
            color: #e0e0e0;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="profile-container">
        <div class="profile-header">
            <a href="profile.php" class="back-button">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2>Edit Profile</h2>
        </div>

        <div class="profile-fields">
            <div class="profile-field">
                <div class="field-label">Full Name</div>
                <div class="field-value">
                    <span id="full_name_value"><?php echo htmlspecialchars($user_data['full_name']); ?></span>
                    <button class="edit-button" onclick="editField('full_name')">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
            </div>

            <div class="profile-field">
                <div class="field-label">Email</div>
                <div class="field-value">
                    <span id="email_value"><?php echo htmlspecialchars($user_data['email']); ?></span>
                    <button class="edit-button" disabled onclick="editField('email')">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
            </div>

            <div class="profile-field">
                <div class="field-label">Phone Number</div>
                <div class="field-value">
                    <span id="phone_number_value"><?php echo htmlspecialchars($user_data['phone_number'] ?? 'Not set'); ?></span>
                    <button class="edit-button" onclick="editField('phone_number')">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
            </div>

            <div class="profile-field">
                <div class="field-label">Address</div>
                <div class="field-value">
                    <span id="address_value"><?php echo htmlspecialchars($user_data['address'] ?? 'Not set'); ?></span>
                    <button class="edit-button" onclick="editField('address')">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
            </div>

            <div class="profile-field">
                <div class="field-label">City</div>
                <div class="field-value">
                    <span id="city_value"><?php echo htmlspecialchars($user_data['city'] ?? 'Not set'); ?></span>
                    <button class="edit-button" onclick="editField('city')">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
            </div>

            <div class="profile-field">
                <div class="field-label">State</div>
                <div class="field-value">
                    <span id="state_value"><?php echo htmlspecialchars($user_data['state'] ?? 'Not set'); ?></span>
                    <button class="edit-button" onclick="editField('state')">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
            </div>

            <div class="profile-field">
                <div class="field-label">Postal Code</div>
                <div class="field-value">
                    <span id="postal_code_value"><?php echo htmlspecialchars($user_data['postal_code'] ?? 'Not set'); ?></span>
                    <button class="edit-button" onclick="editField('postal_code')">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
            </div>

            <div class="profile-field">
                <div class="field-label">Country</div>
                <div class="field-value">
                    <span id="country_value"><?php echo htmlspecialchars($user_data['country'] ?? 'Not set'); ?></span>
                    <button class="edit-button" disabled onclick="editField('country')">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
            </div>

            <!-- Add new link for separate profile edit page -->
            <a href="manage_delivery_addresses.php" class="profile-menu-item mt-4">
                <i class="bi bi-pencil-square"></i>
                Edit Delivery Address
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editField(field) {
            const valueElement = document.getElementById(`${field}_value`);
            const currentValue = valueElement.textContent;

            const input = document.createElement('input');
            input.type = field === 'email' ? 'email' : 'text';
            input.className = 'form-control';
            input.value = currentValue === 'Not set' ? '' : currentValue;

            const saveButton = document.createElement('button');
            saveButton.className = 'edit-button save-button';
            saveButton.innerHTML = '<i class="bi bi-check"></i>';
            saveButton.onclick = () => saveField(field, input.value, valueElement);

            valueElement.parentElement.replaceChild(input, valueElement);
            const editButton = input.parentElement.querySelector('.edit-button');
            editButton.parentElement.replaceChild(saveButton, editButton);

            input.focus();
        }

        function saveField(field, value, originalElement) {
            fetch('edit_profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `field=${field}&value=${encodeURIComponent(value)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to update field');
                        cancelEdit(field, originalElement.textContent);
                    }
                })
                .catch(error => {
                    alert('Error updating field');
                    cancelEdit(field, originalElement.textContent);
                });
        }

        function cancelEdit(field, originalValue) {
            const container = document.querySelector(`#${field}_value`).parentElement;
            const valueSpan = document.createElement('span');
            valueSpan.id = `${field}_value`;
            valueSpan.textContent = originalValue;

            const editButton = document.createElement('button');
            editButton.className = 'edit-button';
            editButton.innerHTML = '<i class="bi bi-pencil"></i>';
            editButton.onclick = () => editField(field);

            container.innerHTML = '';
            container.appendChild(valueSpan);
            container.appendChild(editButton);
        }
    </script>
</body>

</html>