<?php
function isActive($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page === $page ? 'active' : '';
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0">Az Furniture</h4>
    </div>
    <ul class="sidebar-menu">
        <li><a href="index.php" class="<?php echo isActive('index.php'); ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li><a href="orders.php"><i class="bi bi-cart"></i> Orders</a></li>
        <li><a href="users.php"><i class="bi bi-people"></i> Users</a></li>
        <li><a href="categories.php" class="<?php echo isActive('categories.php'); ?>"><i class="bi bi-tags"></i> Categories</a></li>
        <li><a href="products.php" class="<?php echo isActive('products.php'); ?>"><i class="bi bi-box"></i> Products</a></li>
        <li><a href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
        <li><a href="javascript:void(0)" onclick="confirmLogout()"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
</div>

<script>
function confirmLogout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'authentication/logout.php';
    }
}
</script>