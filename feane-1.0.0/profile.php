<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: admin.php");
    exit;
}

// Nếu người dùng là admin, chuyển hướng đến trang thông tin admin
if($_SESSION['username'] === 'admin') {
    header("location: admin_profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân</title>
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
            text-decoration: none;
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
        
        /* Profile card styles */
        .profile-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .profile-header {
            background-color: #3498db;
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 50px;
            color: #3498db;
        }
        
        .profile-name {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .profile-role {
            font-size: 16px;
            opacity: 0.8;
        }
        
        .profile-body {
            padding: 30px;
        }
        
        .profile-section {
            margin-bottom: 30px;
        }
        
        .profile-section h3 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-item {
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
        
        .profile-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->        <?php
        require_once 'includes/sidebar.php';
        echo getSidebarMenu('profile');
        ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Thông tin cá nhân</h2>
                <div class="user-actions">
                    <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
                </div>
            </div>
            
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h1 class="profile-name"><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']); ?></h1>
                    <p class="profile-role">Nhân viên</p>
                </div>
                
                <div class="profile-body">
                    <div class="profile-section">
                        <h3>Thông tin cá nhân</h3>
                        <div class="profile-info">
                            <div class="info-item">
                                <span class="info-label">ID</span>
                                <div class="info-value"><?php echo htmlspecialchars($_SESSION['user_id'] ?? 'N/A'); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Tên tài khoản</span>
                                <div class="info-value"><?php echo htmlspecialchars($_SESSION['username'] ?? 'N/A'); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Họ tên</span>
                                <div class="info-value"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'N/A'); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <div class="info-value"><?php echo htmlspecialchars($_SESSION['email'] ?? 'N/A'); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Ngày sinh</span>
                                <div class="info-value"><?php echo htmlspecialchars($_SESSION['birthday'] ?? 'N/A'); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Số điện thoại</span>
                                <div class="info-value"><?php echo htmlspecialchars($_SESSION['phone'] ?? 'N/A'); ?></div>
                            </div>
                            
                            <div class="info-item" style="grid-column: span 2;">
                                <span class="info-label">Địa chỉ</span>
                                <div class="info-value"><?php echo htmlspecialchars($_SESSION['address'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-section">
                        <h3>Thông tin công việc</h3>
                        <div class="profile-info">
                            <div class="info-item">
                                <span class="info-label">Vị trí</span>
                                <div class="info-value"><?php echo htmlspecialchars($_SESSION['job_position'] ?? 'Nhân viên'); ?></div>
                            </div>
                            
                            <?php if(!empty($_SESSION['description'])): ?>
                            <div class="info-item" style="grid-column: span 2;">
                                <span class="info-label">Mô tả</span>
                                <div class="info-value"><?php echo htmlspecialchars($_SESSION['description']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <a href="#" class="btn btn-primary">Chỉnh sửa thông tin</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>