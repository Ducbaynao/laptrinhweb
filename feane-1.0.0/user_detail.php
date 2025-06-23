<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: admin.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// Kiểm tra xem có ID người dùng được truyền vào không
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: manage_users.php?error=no_id");
    exit;
}

$id = $_GET['id'];

// Lấy thông tin chi tiết của người dùng từ database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Kiểm tra xem người dùng có tồn tại không
if($result->num_rows <= 0) {
    header("location: manage_users.php?error=user_not_found");
    exit;
}

// Lấy dữ liệu người dùng
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin chi tiết người dùng</title>
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
            display: inline-flex;
            align-items: center;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        /* User detail card styles */
        .user-detail-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .user-header {
            background-color: #3498db;
            padding: 30px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .user-avatar {
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
        
        .user-name {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .user-username {
            font-size: 16px;
            opacity: 0.8;
        }
        
        .user-actions-corner {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .action-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        .user-body {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section h3 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .info-grid {
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
        
        .info-description {
            grid-column: span 2;
        }
        
        .description-content {
            min-height: 100px;
            white-space: pre-line;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .status-active {
            background-color: #2ecc71;
            color: white;
        }
        
        .user-id-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
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
                <a href="admin_profile.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
                        <i class="fas fa-user"></i>
                        <span>Trang chủ</span>
                    </div>
                </a>
                <a href="manage_users.php" style="text-decoration: none; color: white;">
                    <div class="menu-item active">
                        <i class="fas fa-users"></i>
                        <span>Quản lý tài khoản</span>
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
                <h2>Chi tiết người dùng</h2>
                <div class="user-actions">
                    <a href="manage_users.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
                </div>
            </div>
            
            <!-- User Detail Card -->
            <div class="user-detail-card">
                <div class="user-header">
                    <div class="user-id-badge">ID: <?php echo $user['id']; ?></div>
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h1 class="user-name"><?php echo htmlspecialchars($user['fullname'] ?: 'Chưa cập nhật'); ?></h1>
                    <p class="user-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                    
                    <div class="user-actions-corner">
                        <a href="edit_account.php?id=<?php echo $user['id']; ?>" class="action-btn">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="action-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                
                <div class="user-body">
                    <div class="section">
                        <h3>Thông tin cá nhân</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Họ và tên</span>
                                <div class="info-value"><?php echo htmlspecialchars($user['fullname'] ?: 'Chưa cập nhật'); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Tên tài khoản</span>
                                <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <div class="info-value"><?php echo htmlspecialchars($user['email'] ?: 'Chưa cập nhật'); ?></div>
                            </div>
                            
                                                        
                            <div class="info-item">
                                <span class="info-label">Ngày sinh</span>
                                <div class="info-value">
                                    <?php 
                                    echo !empty($user['birthday']) ? date('d/m/Y', strtotime($user['birthday'])) : 'Chưa cập nhật';
                                    ?>
                                </div>
                            </div>
                            
                                                        
                            <div class="info-item" style="grid-column: span 2;">
                                <span class="info-label">Địa chỉ</span>
                                <div class="info-value"><?php echo htmlspecialchars($user['address'] ?: 'Chưa cập nhật'); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <h3>Thông tin khác</h3>
                        <div class="info-grid">
                            <?php if(isset($user['job_position'])): ?>
                            <div class="info-item">
                                <span class="info-label">Vị trí công việc</span>
                                <div class="info-value"><?php echo htmlspecialchars($user['job_position'] ?: 'Chưa cập nhật'); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <span class="info-label">Ngày tạo tài khoản</span>
                                <div class="info-value">
                                    <?php 
                                    echo isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : date('d/m/Y');
                                    ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Trạng thái</span>
                                <div class="info-value">
                                    Hoạt động
                                    <span class="status-badge status-active">Active</span>
                                </div>
                            </div>
                            
                            <?php if(!empty($user['description'])): ?>
                            <div class="info-item info-description">
                                <span class="info-label">Mô tả</span>
                                <div class="info-value description-content"><?php echo htmlspecialchars($user['description']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 