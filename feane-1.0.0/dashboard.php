<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #34495e;
        }
        
        .sidebar-header h3 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .nav-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: #34495e;
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main content styles */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .header h2 {
            font-size: 24px;
            color: #2c3e50;
        }
        
        .user-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        /* Admin info card */
        .admin-info-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .admin-info-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .admin-info-header h3 {
            font-size: 20px;
            color: #2c3e50;
        }
        
        .admin-info-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: bold;
            color: #7f8c8d;
            margin-bottom: 5px;
            display: block;
        }
        
        .info-value {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            min-height: 40px;
        }
        
        /* Success alert */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-success .close {
            color: #155724;
            float: right;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Quản trị viên</h3>
                <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
            
            <div class="nav-menu">
                <a href="dashboard.php" style="text-decoration: none; color: white;">
                    <div class="menu-item active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Trang chủ</span>
                    </div>
                </a>
                <a href="admin_profile.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
                        <i class="fas fa-user"></i>
                        <span>Thông tin cá nhân</span>
                    </div>
                </a>
                <a href="manage_users.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
                        <i class="fas fa-users"></i>
                        <span>Quản lý khách hàng</span>
                    </div>
                </a>
                <div class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Cài đặt</span>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Trang chủ</h2>
                <div class="user-actions">
                    <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
                </div>
            </div>
            
            <!-- Success Alert -->
            <div class="alert alert-success">
                <span class="close">&times;</span>
                <strong>Thành công!</strong> Bạn đã đăng nhập vào hệ thống.
            </div>
            
            <!-- Admin Information Card -->
            <div class="admin-info-card">
                <div class="admin-info-header">
                    <h3>Thông tin quản trị viên</h3>
                </div>
                <div class="admin-info-content">
                    <div class="info-group">
                        <span class="info-label">ID</span>
                        <div class="info-value"><?php echo htmlspecialchars($_SESSION['user_id']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Tên tài khoản</span>
                        <div class="info-value"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Họ tên</span>
                        <div class="info-value"><?php echo htmlspecialchars($_SESSION['fullname']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Email</span>
                        <div class="info-value"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Ngày sinh</span>
                        <div class="info-value"><?php echo htmlspecialchars($_SESSION['birthday']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Địa chỉ</span>
                        <div class="info-value"><?php echo htmlspecialchars($_SESSION['address']); ?></div>
                    </div>
                    
                    <div class="info-group" style="grid-column: span 2;">
                        <span class="info-label">Mô tả</span>
                        <div class="info-value"><?php echo htmlspecialchars($_SESSION['description']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Đóng thông báo thành công khi nhấp vào nút đóng
        document.querySelector('.close').addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    </script>
</body>
</html> 