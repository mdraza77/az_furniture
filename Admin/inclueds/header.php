<!-- <header class="admin-header">
    <div class="d-flex justify-content-between align-items-center">
        <button class="toggle-sidebar btn btn-dark">
            <i class="bi bi-list"></i>
        </button>
        <div class="user-info text-end">
            <span class="me-3"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span>
            <a href="javascript:void(0)" onclick="confirmLogout()" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</header> -->
<!-- Main Content -->
<div class="main-content">
        <div class="topbar">
            <button class="toggle-sidebar">
                <i class="bi bi-list"></i>
            </button>
            <div class="user-profile">
                <i class="bi bi-person-circle"></i>
                <span><?php echo htmlspecialchars($admin['name']); ?></span>
            </div>
        </div>