<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

// Get admin data
$admin_id = $_SESSION['admin_data']['id'];
$query = "SELECT * FROM admin_users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/admin-styles.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
</head>

<body>
    <?php include 'inclueds/sidebar.php'; ?>
    <?php include 'inclueds/header.php'; ?>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-dark text-white">
                    <div class="card-header">
                        <h4 class="mb-0">Profile Settings</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="update-settings.php" method="POST" class="needs-validation" novalidate>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control bg-secondary text-white" name="full_name" value="<?php echo htmlspecialchars($admin_data['full_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control bg-secondary text-white" value="<?php echo htmlspecialchars($admin_data['email']); ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <input type="text" class="form-control bg-secondary text-white" value="<?php echo ucfirst($admin_data['role']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Login</label>
                                    <input type="text" class="form-control bg-secondary text-white" value="<?php echo $admin_data['last_login'] ? date('d M Y H:i', strtotime($admin_data['last_login'])) : 'Never'; ?>" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control bg-secondary text-white" name="current_password">
                                <small class="text-muted">Required only if changing password</small>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control bg-secondary text-white" name="new_password" minlength="8">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control bg-secondary text-white" name="confirm_password" minlength="8">
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Password validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            const currentPassword = document.querySelector('input[name="current_password"]').value;

            if (newPassword || confirmPassword) {
                if (!currentPassword) {
                    e.preventDefault();
                    alert('Please enter your current password to change the password');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New passwords do not match');
                    return;
                }
            }
        });
    </script>
</body>

</html>