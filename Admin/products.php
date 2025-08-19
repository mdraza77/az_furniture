<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/admin-styles.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
</head>
<body>
    <?php include 'inclueds/sidebar.php'; ?>
    <?php include 'inclueds/header.php'; ?>
    
    <?php
    $count_query = "SELECT COUNT(*) as total FROM products";
    $count_result = $conn->query($count_query);
    $total_products = $count_result->fetch_assoc()['total'];
    ?>

    <!-- Products Section -->
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Products (<?php echo $total_products; ?>)</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-lg"></i> Add Product
            </button>
        </div>

        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Designer</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM products ORDER BY id DESC";
                    $result = $conn->query($query);
                    
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['id']."</td>";
                        // इमेज पाथ को सही करें
                        $imagePath = file_exists('../' . $row['image_url']) ? '../' . $row['image_url'] : '../assets/images/no-image.png';
                        echo "<td><img src='".$imagePath."' height='50' style='object-fit: cover; width: 50px;'></td>";
                        echo "<td>".$row['name']."</td>";
                        echo "<td>".$row['designer']."</td>";
                        echo "<td>₹".number_format($row['price'], 2)."</td>";
                        echo "<td>
                                <button class='btn btn-sm btn-info me-2' onclick='editProduct(".$row['id'].")'><i class='bi bi-pencil'></i></button>
                                <button class='btn btn-sm btn-danger' onclick='deleteProduct(".$row['id'].")'><i class='bi bi-trash'></i></button>
                            </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="add-product.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name*</label>
                                <input type="text" class="form-control bg-secondary text-white" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control bg-secondary text-white" name="sku" maxlength="50">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price*</label>
                                <input type="number" step="0.01" class="form-control bg-secondary text-white" name="price" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sale Price</label>
                                <input type="number" step="0.01" class="form-control bg-secondary text-white" name="sale_price">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select bg-secondary text-white" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php
                                    $cat_query = "SELECT id, name FROM categories WHERE is_active = 1";
                                    $cat_result = $conn->query($cat_query);
                                    while($cat = $cat_result->fetch_assoc()) {
                                        echo "<option value='".$cat['id']."'>".$cat['name']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control bg-secondary text-white" name="stock_quantity" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Designer</label>
                                <input type="text" class="form-control bg-secondary text-white" name="designer" maxlength="100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Image</label>
                                <input type="file" class="form-control bg-secondary text-white" name="image" accept="image/*" id="add_product_image" onchange="previewImage(this, 'add_image_preview')">
                                <div class="mt-2 text-center">
                                    <img id="add_image_preview" src="#" alt="Preview" style="max-height: 150px; display: none;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control bg-secondary text-white" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_featured" value="1">
                                    <label class="form-check-label">Featured Product</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                                    <label class="form-check-label">Active Product</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="update-product.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name*</label>
                                <input type="text" class="form-control bg-secondary text-white" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control bg-secondary text-white" name="sku" id="edit_sku" maxlength="50">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price*</label>
                                <input type="number" step="0.01" class="form-control bg-secondary text-white" name="price" id="edit_price" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sale Price</label>
                                <input type="number" step="0.01" class="form-control bg-secondary text-white" name="sale_price" id="edit_sale_price">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select bg-secondary text-white" name="category_id" id="edit_category_id">
                                    <option value="">Select Category</option>
                                    <?php
                                    $cat_query = "SELECT id, name FROM categories WHERE is_active = 1";
                                    $cat_result = $conn->query($cat_query);
                                    while($cat = $cat_result->fetch_assoc()) {
                                        echo "<option value='".$cat['id']."'>".$cat['name']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control bg-secondary text-white" name="stock_quantity" id="edit_stock_quantity">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Designer</label>
                                <input type="text" class="form-control bg-secondary text-white" name="designer" id="edit_designer" maxlength="100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Image</label>
                                <input type="file" class="form-control bg-secondary text-white" name="image" accept="image/*" id="edit_product_image" onchange="previewImage(this, 'edit_image_preview')">
                                <div class="mt-2 text-center">
                                    <img id="edit_image_preview" src="#" alt="Preview" style="max-height: 150px; display: none;">
                                    <img id="edit_current_image" src="" alt="Current Image" style="max-height: 150px;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control bg-secondary text-white" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_featured" id="edit_is_featured" value="1">
                                    <label class="form-check-label">Featured Product</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="edit_is_active" value="1">
                                    <label class="form-check-label">Active Product</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add JavaScript for Image Preview -->
    <script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const currentImage = document.getElementById('edit_current_image');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                
                // Hide current image in edit mode
                if (previewId === 'edit_image_preview' && currentImage) {
                    currentImage.style.display = 'none';
                }
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '#';
            preview.style.display = 'none';
            
            // Show current image back in edit mode
            if (previewId === 'edit_image_preview' && currentImage) {
                currentImage.style.display = 'block';
            }
        }
    }

    function editProduct(productId) {
        fetch(`get-product.php?id=${productId}`)
            .then(response => response.json())
            .then(product => {
                // बाकी फील्ड्स को भरें
                document.getElementById('edit_product_id').value = product.id;
                document.getElementById('edit_name').value = product.name;
                document.getElementById('edit_sku').value = product.sku;
                document.getElementById('edit_price').value = product.price;
                document.getElementById('edit_sale_price').value = product.sale_price;
                document.getElementById('edit_category_id').value = product.category_id;
                document.getElementById('edit_stock_quantity').value = product.stock_quantity;
                document.getElementById('edit_designer').value = product.designer;
                document.getElementById('edit_description').value = product.description;
                document.getElementById('edit_is_featured').checked = product.is_featured == 1;
                document.getElementById('edit_is_active').checked = product.is_active == 1;
                
                // इमेज प्रीव्यू को सही करें
                const currentImage = document.getElementById('edit_current_image');
                const previewImage = document.getElementById('edit_image_preview');
                
                if (product.image_url && product.image_url.trim() !== '') {
                    currentImage.src = '../' + product.image_url;
                    currentImage.style.display = 'block';
                    previewImage.style.display = 'none';
                } else {
                    currentImage.src = '../assets/images/no-image.png';
                    currentImage.style.display = 'block';
                    previewImage.style.display = 'none';
                }
                
                // फाइल इनपुट को रीसेट करें
                document.getElementById('edit_product_image').value = '';
                
                new bootstrap.Modal(document.getElementById('editProductModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading product details');
            });
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>

    <!-- Add JavaScript for Image Preview -->
    <script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.classList.add('d-none');
        }
    }
    </script>

    <!-- Delete Product Function -->
    <script>
    function deleteProduct(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            fetch('delete-product.php?id=' + productId, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product deleted successfully');
                    location.reload();
                } else {
                    alert('Error deleting product: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error occurred while deleting product');
            });
        }
    }
    </script>
</body>
</html>