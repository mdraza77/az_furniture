<?php
require 'config/database.php';
require_once 'authentication/check_session.php';
redirectToLogin();

$user_id = $_SESSION['user_data']['id'];

// Fetch fresh user data from database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Update session with fresh data
$_SESSION['user_data'] = array_merge($_SESSION['user_data'], $user_data);

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        $error = "Only JPG, PNG and GIF images are allowed";
    } elseif ($file['size'] > $max_size) {
        $error = "Image size must be less than 5MB";
    } else {
        $upload_dir = 'assets/images/profile/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Update user profile image in database
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $image_path = $target_path;
            $stmt->bind_param("si", $image_path, $user_id);

            if ($stmt->execute()) {
                // Fetch fresh user data after update
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user_data = $result->fetch_assoc();

                // Update only the profile_image in session data
                $_SESSION['user_data']['profile_image'] = $user_data['profile_image'];

                header("Location: profile.php?success=1");
                exit;
            } else {
                $error = "Failed to update profile image";
            }
        } else {
            $error = "Failed to upload image";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Az Furniture</title>
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

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
            object-fit: cover;
        }

        .online-indicator {
            width: 12px;
            height: 12px;
            background-color: #4CAF50;
            border-radius: 50%;
            display: inline-block;
            margin-left: 5px;
        }

        .profile-email {
            color: #888;
            margin-top: 5px;
        }

        .profile-menu-item {
            background-color: #1E1E1E;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .profile-menu-item:hover {
            background-color: #2c2c2c;
            color: #fff;
        }

        .profile-menu-item i {
            margin-right: 15px;
            font-size: 20px;
        }

        .logout-button {
            color: #dc3545;
            background: none;
            border: none;
            width: 100%;
            text-align: center;
            padding: 15px;
            margin-top: 20px;
            font-size: 16px;
            text-decoration: none;
            display: block;
        }

        .logout-button:hover {
            color: #bb2d3b;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="profile-container">
        <div class="profile-header">
            <a href="index.php" class="back-button">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2>Profile</h2>
        </div>

        <div class="text-center mb-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form id="imageForm" action="profile.php" method="POST" enctype="multipart/form-data">
                <div class="profile-image-container">
                    <img src="<?php echo isset($user_data['profile_image']) ? $user_data['profile_image'] : 'assets/images/default_user_image.png'; ?>"
                        alt="Profile" class="profile-image">
                    <label for="imageUpload" class="edit-image-button">
                        <i class="bi bi-pencil"></i>
                    </label>
                    <input type="file" id="imageUpload" name="profile_image" accept="image/jpeg,image/png,image/gif" onchange="validateAndSubmit(this)">
                </div>
            </form>
            <h3><?php echo htmlspecialchars($user_data['full_name']); ?><span class="online-indicator"></span></h3>
            <div class="profile-email"><?php echo htmlspecialchars($user_data['email']); ?></div>
        </div>

        <a href="edit_profile.php" class="profile-menu-item">
            <i class="bi bi-person"></i>
            Profile
        </a>

        <a href="payment_methods.php" class="profile-menu-item">
            <i class="bi bi-credit-card"></i>
            Payment Methods
        </a>

        <a href="order_history.php" class="profile-menu-item">
            <i class="bi bi-clock-history"></i>
            Order History
        </a>

        <a href="manage_delivery_addresses.php" class="profile-menu-item">
            <i class="bi bi-geo-alt"></i>
            Delivery Address
        </a>

        <a onclick="return confirm('This page is under maintenance!');" href="profile.php" class="profile-menu-item">
            <i class="bi bi-headset"></i>
            Support Center
        </a>

        <a onclick="return confirm('This page is under maintenance!');" href="profile.php" class="profile-menu-item">
            <i class="bi bi-shield-check"></i>
            Legal Policy
        </a>

        <a href="authentication/logout.php" class="logout-button" onclick="return confirm('Are you sure you want to log out?')">
            Log Out
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<script>
    function validateAndSubmit(input) {
        const file = input.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (file) {
            if (file.size > maxSize) {
                alert('Image size must be less than 5MB');
                input.value = '';
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPG, PNG and GIF images are allowed');
                input.value = '';
                return;
            }

            document.getElementById('imageForm').submit();
        }
    }
</script>
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

    .profile-image {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin-bottom: 15px;
        object-fit: cover;
    }

    .online-indicator {
        width: 12px;
        height: 12px;
        background-color: #4CAF50;
        border-radius: 50%;
        display: inline-block;
        margin-left: 5px;
    }

    .profile-email {
        color: #888;
        margin-top: 5px;
    }

    .profile-menu-item {
        background-color: #1E1E1E;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        color: #fff;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .profile-menu-item:hover {
        background-color: #2c2c2c;
        color: #fff;
    }

    .profile-menu-item i {
        margin-right: 15px;
        font-size: 20px;
    }

    .logout-button {
        color: #dc3545;
        background: none;
        border: none;
        width: 100%;
        text-align: center;
        padding: 15px;
        margin-top: 20px;
        font-size: 16px;
        text-decoration: none;
        display: block;
    }

    .logout-button:hover {
        color: #bb2d3b;
        text-decoration: none;
    }

    .profile-image-container {
        position: relative;
        display: inline-block;
        margin-bottom: 30px;
    }

    .edit-image-button {
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        background: #1E1E1E;
        border: none;
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.3s;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .edit-image-button:hover {
        background: #2c2c2c;
    }

    #imageUpload {
        display: none;
    }
</style>
</body>

</html>