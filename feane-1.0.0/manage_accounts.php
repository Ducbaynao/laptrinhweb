<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['username'] !== 'admin') {
    header("location: admin.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// Lấy danh sách tài khoản
$sql = "SELECT * FROM users ORDER BY id";
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
        
        /* Table styles */
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .table tr:last-child td {
            border-bottom: none;
        }
        
        .table tr:hover {
            background-color: #f5f7fa;
        }
        
        .table .actions {
            width: 120px;
        }
        
        .action-btn {
            padding: 5px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .edit-btn {
            background-color: #3498db;
        }
        
        .delete-btn {
            background-color: #e74c3c;
        }
        
        .welcome-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .welcome-container h3 {
            margin-bottom: 15px;
            color: #2c3e50;
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
            margin-left: 5px;
        }
        
        .admin-badge {
            background-color: #e74c3c;
        }
        
        .user-badge {
            background-color: #2ecc71;
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
                        <i class="fas fa-user-circle"></i>
                        <span>Thông tin cá nhân</span>
                    </div>
                </a>
                <a href="manage_accounts.php" style="text-decoration: none; color: white;">
                    <div class="menu-item active">
                        <i class="fas fa-users-cog"></i>
                        <span>Quản lý tài khoản</span>
                    </div>
                </a>
                <a href="manage_reservations.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Quản lý đặt bàn</span>
                    </div>
                </a>
                <a href="logout.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng xuất</span>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Quản lý tài khoản</h2>
                <div class="user-actions">
                    <a href="add_account.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm tài khoản</a>
                </div>
            </div>
            
            <?php
            // Hiển thị thông báo
            if(isset($_GET['success'])) {
                if($_GET['success'] == 'add') {
                    echo '<div class="alert alert-success">Thêm tài khoản thành công!</div>';
                } else if($_GET['success'] == 'update') {
                    echo '<div class="alert alert-success">Cập nhật tài khoản thành công!</div>';
                } else if($_GET['success'] == 'delete') {
                    echo '<div class="alert alert-success">Xóa tài khoản thành công!</div>';
                }
            }
            
            if(isset($_GET['error'])) {
                if($_GET['error'] == 'delete_admin') {
                    echo '<div class="alert alert-danger">Không thể xóa tài khoản quản trị viên!</div>';
                } else if($_GET['error'] == 'account_not_found') {
                    echo '<div class="alert alert-danger">Không tìm thấy tài khoản!</div>';
                }
            }
            ?>
            
            <!-- Welcome message -->
            <div class="welcome-container">
                <h3>Quản lý tài khoản hệ thống</h3>
                <p>Tại đây bạn có thể xem, thêm, chỉnh sửa hoặc xóa tài khoản người dùng trong hệ thống.</p>
            </div>
            
            <!-- Account Table -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Chức vụ</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $is_admin = ($row["username"] === 'admin');
                                $badge_class = $is_admin ? 'admin-badge' : 'user-badge';
                                $role = $is_admin ? 'Quản trị viên' : 'Nhân viên';
                                
                                echo "<tr>";
                                echo "<td>" . $row["id"] . "</td>";
                                echo "<td>" . htmlspecialchars($row["username"]) . " <span class='badge {$badge_class}'>{$role}</span></td>";
                                echo "<td>" . htmlspecialchars($row["fullname"] ?? 'Chưa cập nhật') . "</td>";
                                echo "<td>" . htmlspecialchars($row["email"] ?? 'Chưa cập nhật') . "</td>";
                                echo "<td>" . htmlspecialchars($row["job_position"] ?? 'Chưa cập nhật') . "</td>";
                                echo "<td class='actions'>";
                                echo "<a href='edit_account.php?id=" . $row["id"] . "' class='action-btn edit-btn' title='Chỉnh sửa'><i class='fas fa-edit'></i></a>";
                                
                                // Không hiển thị nút xóa cho tài khoản admin
                                if(!$is_admin) {
                                    echo "<a href='delete_account.php?id=" . $row["id"] . "' class='action-btn delete-btn' title='Xóa' onclick='return confirm(\"Bạn có chắc chắn muốn xóa tài khoản này?\")'><i class='fas fa-trash'></i></a>";
                                }
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center;'>Không có tài khoản nào</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 