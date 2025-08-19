<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/admin-styles.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
</head>
<body>
    <?php include 'inclueds/sidebar.php'; ?>
    <?php include 'inclueds/header.php'; ?>

    <?php
    $count_query = "SELECT COUNT(*) as total FROM users";
    $count_result = $conn->query($count_query);
    $total_users = $count_result->fetch_assoc()['total'];
    ?>

    <!-- Users Section -->
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Users (<?php echo $total_users; ?>)</h2>
            <div class="col-md-4">
                <input type="text" id="searchInput" class="form-control bg-dark text-white" placeholder="Search users...">
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM users ORDER BY id DESC";
                    $result = $conn->query($query);
                    
                    while($row = $result->fetch_assoc()) {
                        $imagePath = file_exists('../' . $row['profile_image']) ? '../' . $row['profile_image'] : '../assets/images/default_user_image.png';
                        $statusBadge = $row['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        $emailVerified = $row['is_email_verified'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>';
                        
                        echo "<tr>";
                        echo "<td>".$row['id']."</td>";
                        echo "<td><img src='".$imagePath."' height='50' style='object-fit: cover; width: 50px; border-radius: 50%;'></td>";
                        echo "<td>".htmlspecialchars($row['full_name'])."</td>";
                        echo "<td>".htmlspecialchars($row['email'])." ".$emailVerified."</td>";
                        echo "<td>".htmlspecialchars($row['phone_number'] ?? 'N/A')."</td>";
                        echo "<td><span class='badge bg-".($row['role'] === 'admin' ? 'primary' : 'secondary')."'>".ucfirst($row['role'])."</span></td>";
                        echo "<td>".$statusBadge."</td>";
                        echo "<td>
                                <button class='btn btn-sm btn-info me-2' onclick='viewUser(".$row['id'].")'><i class='bi bi-eye'></i></button>
                                <button class='btn btn-sm btn-danger' onclick='toggleUserStatus(".$row['id'].", ".$row['is_active'].")'><i class='bi bi-".($row['is_active'] ? 'lock' : 'unlock')."'></i></button>
                            </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <img id="user_image" src="" alt="User Profile" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <h5 id="user_name" class="mb-0"></h5>
                            <p id="user_role" class="text-muted"></p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Email:</strong> <span id="user_email"></span></p>
                                    <p><strong>Phone:</strong> <span id="user_phone"></span></p>
                                    <p><strong>Last Login:</strong> <span id="user_last_login"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Email Verified:</strong> <span id="user_email_verified"></span></p>
                                    <p><strong>Phone Verified:</strong> <span id="user_phone_verified"></span></p>
                                    <p><strong>Account Status:</strong> <span id="user_status"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Address Information</h6>
                            <p id="user_address"></p>
                            <p>
                                <span id="user_city"></span>
                                <span id="user_state"></span>
                                <span id="user_postal_code"></span>
                            </p>
                            <p id="user_country"></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Account Information</h6>
                            <p><strong>Created:</strong> <span id="user_created_at"></span></p>
                            <p><strong>Last Updated:</strong> <span id="user_updated_at"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function viewUser(userId) {
        fetch(`get-user.php?id=${userId}`)
            .then(response => response.json())
            .then(user => {
                // Update modal with user information
                document.getElementById('user_image').src = '../' + (user.profile_image || 'assets/images/default_user_image.png');
                document.getElementById('user_name').textContent = user.full_name;
                document.getElementById('user_role').textContent = user.role.toUpperCase();
                document.getElementById('user_email').textContent = user.email;
                document.getElementById('user_phone').textContent = user.phone_number || 'Not provided';
                document.getElementById('user_last_login').textContent = user.last_login ? new Date(user.last_login).toLocaleString() : 'Never';
                document.getElementById('user_email_verified').innerHTML = user.is_email_verified ? 
                    '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-danger">Not Verified</span>';
                document.getElementById('user_phone_verified').innerHTML = user.is_phone_verified ? 
                    '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-danger">Not Verified</span>';
                document.getElementById('user_status').innerHTML = user.is_active ? 
                    '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                document.getElementById('user_address').textContent = user.address || 'No address provided';
                document.getElementById('user_city').textContent = user.city ? user.city + ', ' : '';
                document.getElementById('user_state').textContent = user.state ? user.state + ' ' : '';
                document.getElementById('user_postal_code').textContent = user.postal_code || '';
                document.getElementById('user_country').textContent = user.country || 'Not specified';
                document.getElementById('user_created_at').textContent = new Date(user.created_at).toLocaleString();
                document.getElementById('user_updated_at').textContent = new Date(user.updated_at).toLocaleString();
                
                new bootstrap.Modal(document.getElementById('viewUserModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading user details');
            });
    }

    function toggleUserStatus(userId, currentStatus) {
        if (confirm(`Are you sure you want to ${currentStatus ? 'deactivate' : 'activate'} this user?`)) {
            fetch('toggle-user-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    status: !currentStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating user status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error occurred while updating user status');
            });
        }
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            const name = row.children[2].textContent.toLowerCase();
            const email = row.children[3].textContent.toLowerCase();
            const phone = row.children[4].textContent.toLowerCase();
            
            if (name.includes(searchText) || email.includes(searchText) || phone.includes(searchText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>