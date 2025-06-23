<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// Lấy danh sách tất cả người dùng với đầy đủ thông tin, ngoại trừ admin
$sql = "SELECT * FROM users WHERE username != 'admin'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản</title>
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
        
        .btn-success {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        /* User card styles */
        .user-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .user-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .user-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .user-card-title {
            font-size: 18px;
            color: #2c3e50;
            margin: 0;
        }
        
        .user-card-actions {
            display: flex;
            gap: 5px;
        }
        
        .user-card-body {
            margin-bottom: 15px;
        }
        
        .user-info-item {
            margin-bottom: 10px;
            display: flex;
        }
        
        .user-info-label {
            font-weight: bold;
            min-width: 120px;
            color: #7f8c8d;
        }
        
        .user-info-value {
            flex: 1;
            word-break: break-word;
        }
        
        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            color: white;
        }
        
        .edit-btn {
            background-color: #3498db;
        }
        
        .delete-btn {
            background-color: #e74c3c;
        }
        
        .view-btn {
            background-color: #2ecc71;
        }
        
        /* Alert styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .description-box {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-top: 5px;
            font-style: italic;
            color: #666;
            max-height: 100px;
            overflow-y: auto;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 12px;
            font-weight: bold;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 10px;
            background-color: #3498db;
            color: white;
        }
        
        .clickable-card {
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
                <h2>Quản lý tài khoản</h2>
                <div class="user-actions">
                    <a href="add_user.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm tài khoản</a>
                    <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
                </div>
            </div>
            
            <?php 
            // Hiển thị thông báo thành công
            if(isset($_GET['success'])) {
                if($_GET['success'] == 'delete') {
                    echo '<div class="alert alert-success">Xóa tài khoản thành công!</div>';
                }
            }
            
            // Hiển thị thông báo lỗi
            if(isset($_GET['error'])) {
                if($_GET['error'] == 'self_delete') {
                    echo '<div class="alert alert-danger">Không thể xóa tài khoản đang đăng nhập!</div>';
                } else if($_GET['error'] == 'delete_failed') {
                    echo '<div class="alert alert-danger">Xóa tài khoản không thành công!</div>';
                } else if($_GET['error'] == 'user_not_found') {
                    echo '<div class="alert alert-danger">Không tìm thấy thông tin người dùng!</div>';
                } else if($_GET['error'] == 'no_id') {
                    echo '<div class="alert alert-danger">Thiếu thông tin ID người dùng!</div>';
                }
            }
            ?>
            
            <!-- User Grid -->
            <div class="user-grid">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        ?>
                        <div class="user-card clickable-card" onclick="window.location.href='user_detail.php?id=<?php echo $row['id']; ?>'">
                            <div class="user-card-header">
                                <h3 class="user-card-title">
                                    <?php echo htmlspecialchars($row["fullname"] ?: $row["username"]); ?>
                                    <span class="badge">ID: <?php echo $row["id"]; ?></span>
                                </h3>
                                <div class="user-card-actions" onclick="event.stopPropagation();">
                                    <a href="user_detail.php?id=<?php echo $row["id"]; ?>" class="action-btn view-btn" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                                    <a href="edit_user.php?id=<?php echo $row["id"]; ?>" class="action-btn edit-btn" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                    <a href="delete_user.php?id=<?php echo $row["id"]; ?>" class="action-btn delete-btn" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                            <div class="user-card-body">
                                <div class="user-info-item">
                                    <div class="user-info-label">Họ tên:</div>
                                    <div class="user-info-value"><?php echo htmlspecialchars($row["fullname"] ?: "Chưa cập nhật"); ?></div>
                                </div>
                                
                                <div class="user-info-item">
                                    <div class="user-info-label">Tên tài khoản:</div>
                                    <div class="user-info-value"><?php echo htmlspecialchars($row["username"]); ?></div>
                                </div>
                                
                                <div class="user-info-item">
                                    <div class="user-info-label">Email:</div>
                                    <div class="user-info-value"><?php echo htmlspecialchars($row["email"] ?: "Chưa cập nhật"); ?></div>
                                </div>
                                
                                <div class="user-info-item">
                                    <div class="user-info-label">Ngày sinh:</div>
                                    <div class="user-info-value">
                                        <?php 
                                        echo $row["birthday"] ? date("d/m/Y", strtotime($row["birthday"])) : "Chưa cập nhật"; 
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="user-info-item">
                                    <div class="user-info-label">Địa chỉ:</div>
                                    <div class="user-info-value"><?php echo htmlspecialchars($row["address"] ?: "Chưa cập nhật"); ?></div>
                                </div>
                                
                                <?php if(!empty($row["job_position"])): ?>
                                <div class="user-info-item">
                                    <div class="user-info-label">Vị trí công việc:</div>
                                    <div class="user-info-value"><?php echo htmlspecialchars($row["job_position"]); ?></div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($row["description"])): ?>
                                <div class="user-info-item">
                                    <div class="user-info-label">Mô tả:</div>
                                    <div class="user-info-value">
                                        <div class="description-box"><?php echo htmlspecialchars($row["description"]); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<div class='alert alert-info' style='grid-column: 1/-1;'>Không có dữ liệu người dùng</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html> 