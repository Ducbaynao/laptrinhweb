<?php
// Tạo menu sidebar đồng nhất cho tất cả các trang
function getSidebarMenu($activeItem = '') {
    return '
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Nhân viên</h3>
            <p>' . htmlspecialchars($_SESSION["username"]) . '</p>
        </div>
        
        <div class="nav-menu">
            <a href="profile.php" style="text-decoration: none; color: white;">
                <div class="menu-item ' . ($activeItem == 'profile' ? 'active' : '') . '">
                    <i class="fas fa-user-circle"></i>
                    <span>Thông tin cá nhân</span>
                </div>
            </a>
            <a href="manage_customers.php" style="text-decoration: none; color: white;">
                <div class="menu-item ' . ($activeItem == 'customers' ? 'active' : '') . '">
                    <i class="fas fa-users"></i>
                    <span>Quản lý khách hàng</span>
                </div>
            </a>
            <a href="logout.php" style="text-decoration: none; color: white;">
                <div class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </div>
            </a>
        </div>
    </div>';
}
?>
