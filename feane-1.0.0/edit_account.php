<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: admin.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// Lấy thông tin tài khoản cần chỉnh sửa
if(isset($_GET['id'])) {
    $account_id = $_GET['id'];
} else {
    header("location: manage_users.php");
    exit;
}

// Lấy thông tin hiện tại của tài khoản
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    // Không tìm thấy tài khoản
    header("location: manage_users.php?error=account_not_found");
    exit;
}

$account = $result->fetch_assoc();
$stmt->close();

// Xử lý form khi submit
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $birthday = trim($_POST['birthday'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $job_position = trim($_POST['job_position'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    
    // Validate dữ liệu
    $errors = [];
    
    if(empty($username)) {
        $errors[] = "Vui lòng nhập tên đăng nhập";
    }
    
    if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    // Kiểm tra username đã tồn tại chưa (nếu thay đổi)
    if($username != $account['username']) {
        $check_sql = "SELECT * FROM users WHERE username = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $username, $account_id);
        $check_stmt->execute();
        
        if($check_stmt->get_result()->num_rows > 0) {
            $errors[] = "Tên đăng nhập đã tồn tại";
        }
        
        $check_stmt->close();
    }
    
    // Nếu không có lỗi, cập nhật tài khoản
    if(empty($errors)) {
        // Chuẩn bị câu lệnh SQL (phụ thuộc vào việc có thay đổi mật khẩu hay không)
        if(!empty($new_password)) {
            // Mã hóa mật khẩu mới
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_sql = "UPDATE users SET username = ?, password = ?, fullname = ?, email = ?, 
                          birthday = ?, address = ?, job_position = ?, description = ? 
                          WHERE id = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssssssi", $username, $hashed_password, $fullname, $email, 
                                   $birthday, $address, $job_position, $description, $account_id);
        } else {
            // Không thay đổi mật khẩu
            $update_sql = "UPDATE users SET username = ?, fullname = ?, email = ?, 
                          birthday = ?, address = ?, job_position = ?, description = ? 
                          WHERE id = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssssssi", $username, $fullname, $email, 
                                   $birthday, $address, $job_position, $description, $account_id);
        }
        
        // Thực thi câu lệnh cập nhật
        if($update_stmt->execute()) {
            // Cập nhật các biến session nếu đang chỉnh sửa tài khoản đăng nhập
            if($_SESSION['user_id'] == $account_id) {
                $_SESSION['username'] = $username;
                $_SESSION['fullname'] = $fullname;
                $_SESSION['email'] = $email;
                $_SESSION['birthday'] = $birthday;
                $_SESSION['address'] = $address;
                $_SESSION['job_position'] = $job_position;
                $_SESSION['description'] = $description;
            }
            
            // Chuyển hướng với thông báo thành công
            header("Location: user_detail.php?id=" . $account_id . "&success=update");
            exit;
        } else {
            $errors[] = "Đã xảy ra lỗi: " . $update_stmt->error;
        }
        
        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa tài khoản</title>
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
        
        /* Form styles */
        .form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .form-title {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }
        
        .form-text {
            margin-top: 5px;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-actions {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        /* Alert styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .invalid-feedback {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .required-star {
            color: #e74c3c;
            margin-left: 3px;
        }
        
        .form-section {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        
        .form-section-title {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
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
                        <span>Trang chủ</span>
                    </div>
                </a>
                <a href="manage_users.php" style="text-decoration: none; color: white;">
                    <div class="menu-item active">
                        <i class="fas fa-users-cog"></i>
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
                <h2>Chỉnh sửa tài khoản</h2>
                <div class="user-actions">
                    <a href="user_detail.php?id=<?php echo $account_id; ?>" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                </div>
            </div>
            
            <?php
            // Hiển thị lỗi nếu có
            if(!empty($errors)) {
                echo '<div class="alert alert-danger">';
                echo '<ul style="margin: 0; padding-left: 20px;">';
                foreach($errors as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            ?>
            
            <!-- Form chỉnh sửa tài khoản -->
            <div class="form-container">
                <h3 class="form-title">Thông tin tài khoản</h3>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $account_id); ?>" method="post">
                    <div class="form-section">
                        <h4 class="form-section-title">Thông tin đăng nhập</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="username" class="form-label">Tên đăng nhập<span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($account['username']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <small class="form-text">Để trống nếu không muốn thay đổi mật khẩu</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4 class="form-section-title">Thông tin cá nhân</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="fullname" class="form-label">Họ tên</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($account['fullname'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($account['email'] ?? ''); ?>">
                            </div>
                            
                                                                                    
                            <div class="form-group">
                                <label for="birthday" class="form-label">Ngày sinh</label>
                                <input type="date" class="form-control" id="birthday" name="birthday" value="<?php echo htmlspecialchars($account['birthday'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group" style="grid-column: span 2;">
                                <label for="address" class="form-label">Địa chỉ</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($account['address'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4 class="form-section-title">Thông tin công việc</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="job_position" class="form-label">Chức vụ</label>
                                <input type="text" class="form-control" id="job_position" name="job_position" value="<?php echo htmlspecialchars($account['job_position'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group" style="grid-column: span 2;">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($account['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="user_detail.php?id=<?php echo $account_id; ?>" class="btn btn-danger">Hủy</a>
                        <button type="submit" class="btn btn-success">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 