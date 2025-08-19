<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/admin-styles.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
</head>
<body>
    <?php include 'inclueds/header.php'; ?>
    <?php include 'inclueds/sidebar.php'; ?>

    <?php
    $count_query = "SELECT COUNT(*) as total FROM categories";
    $count_result = $conn->query($count_query);
    $total_categories = $count_result->fetch_assoc()['total'];
    ?>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Categories (<?php echo $total_categories; ?>)</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-lg"></i> Add Category
            </button>
        </div>

        <!-- Categories Table -->
        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT c.*, COUNT(p.id) as product_count 
                             FROM categories c 
                             LEFT JOIN products p ON c.id = p.category_id 
                             GROUP BY c.id 
                             ORDER BY c.id DESC";
                    $result = $conn->query($query);
                    
                    while($category = $result->fetch_assoc()) {
                        $status_badge = $category['is_active'] ? 
                            '<span class="badge rounded-pill bg-success">Active</span>' : 
                            '<span class="badge rounded-pill bg-danger">Inactive</span>';
                        
                        echo "<tr>";
                        echo "<td>{$category['id']}</td>";
                        echo "<td>{$category['name']}</td>";
                        echo "<td>" . (empty($category['description']) ? '-' : $category['description']) . "</td>";
                        echo "<td class='text-center'>{$status_badge}</td>";
                        echo "<td class='text-center'><span class='badge rounded-pill bg-info'>{$category['product_count']}</span></td>";
                        echo "<td>
                                <button class='btn btn-sm btn-info me-2' onclick='editCategory({$category['id']})'>
                                    <i class='bi bi-pencil'></i>
                                </button>
                                <button class='btn btn-sm btn-danger' onclick='deleteCategory({$category['id']})'>
                                    <i class='bi bi-trash'></i>
                                </button>
                            </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="add-category.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name*</label>
                            <input type="text" class="form-control bg-secondary text-white" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control bg-secondary text-white" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="update-category.php" method="POST">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name*</label>
                            <input type="text" class="form-control bg-secondary text-white" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control bg-secondary text-white" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(id) {
            fetch(`get-category.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_category_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_description').value = data.description;
                    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
                });
        }

        function deleteCategory(id) {
            if(confirm('Are you sure you want to delete this category?')) {
                fetch(`delete-category.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting category');
                    }
                });
            }
        }
    </script>
</body>
</html>