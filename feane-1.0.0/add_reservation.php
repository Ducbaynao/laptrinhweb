<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: admin.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// Kiểm tra bảng reservations có tồn tại không
$tableExists = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;

// Nếu bảng không tồn tại, chuyển hướng để tạo bảng
if (!$tableExists) {
    header("Location: manage_reservations.php");
    exit;
}

// Xử lý form khi submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email'] ?? '');
    $party_size = intval($_POST['party_size']);
    $reservation_date = $_POST['reservation_date'];
    $reservation_time = $_POST['reservation_time'];
    $special_request = trim($_POST['special_request'] ?? '');
    $status = $_POST['status'] ?? 'pending';
    
    // Validate dữ liệu
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Vui lòng nhập họ tên";
    }
    
    if (empty($phone)) {
        $errors[] = "Vui lòng nhập số điện thoại";
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    if ($party_size <= 0) {
        $errors[] = "Số lượng người phải lớn hơn 0";
    }
    
    if (empty($reservation_date)) {
        $errors[] = "Vui lòng chọn ngày đặt bàn";
    }
    
    if (empty($reservation_time)) {
        $errors[] = "Vui lòng chọn giờ đặt bàn";
    }
    
    // Nếu không có lỗi, thêm đặt bàn vào database
    if (empty($errors)) {
        // Chuẩn bị câu lệnh SQL
        $sql = "INSERT INTO reservations (name, phone, email, party_size, reservation_date, reservation_time, special_request, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssisiss", $name, $phone, $email, $party_size, $reservation_date, $reservation_time, $special_request, $status);
        
        // Thực thi câu lệnh
        if ($stmt->execute()) {
            // Chuyển hướng với thông báo thành công
            header("Location: manage_reservations.php?success=add");
            exit;
        } else {
            $errors[] = "Đã xảy ra lỗi: " . $stmt->error;
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm đặt bàn mới</title>
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Nhân viên</h3>
                <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
            
            <div class="nav-menu">
                <a href="manage_customers.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
                        <i class="fas fa-users"></i>
                        <span>Quản lý khách hàng</span>
                    </div>
                </a>
                <a href="manage_reservations.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Quản lý đặt bàn</span>
                    </div>
                </a>
                <a href="add_reservation.php" style="text-decoration: none; color: white;">
                    <div class="menu-item active">
                        <i class="fas fa-plus-circle"></i>
                        <span>Thêm đặt bàn</span>
                    </div>
                </a>
                <a href="profile.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
                        <i class="fas fa-user-circle"></i>
                        <span>Thông tin cá nhân</span>
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
                <h2>Thêm đặt bàn mới</h2>
                <div class="user-actions">
                    <a href="manage_reservations.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                </div>
            </div>
            
            <?php
            // Hiển thị lỗi nếu có
            if (!empty($errors)) {
                echo '<div class="alert alert-danger">';
                echo '<ul style="margin: 0; padding-left: 20px;">';
                foreach ($errors as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            ?>
            
            <!-- Form -->
            <div class="form-container">
                <h3 class="form-title">Thông tin đặt bàn</h3>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name" class="form-label">Họ tên<span class="required-star">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Số điện thoại<span class="required-star">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="party_size" class="form-label">Số lượng người<span class="required-star">*</span></label>
                            <input type="number" class="form-control" id="party_size" name="party_size" min="1" value="<?php echo htmlspecialchars($party_size ?? '1'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="reservation_date" class="form-label">Ngày đặt<span class="required-star">*</span></label>
                            <input type="date" class="form-control" id="reservation_date" name="reservation_date" value="<?php echo htmlspecialchars($reservation_date ?? date('Y-m-d')); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="reservation_time" class="form-label">Giờ đặt<span class="required-star">*</span></label>
                            <input type="time" class="form-control" id="reservation_time" name="reservation_time" value="<?php echo htmlspecialchars($reservation_time ?? '18:00'); ?>" required>
                        </div>
                        
                        <div class="form-group" style="grid-column: span 2;">
                            <label for="special_request" class="form-label">Yêu cầu đặc biệt</label>
                            <textarea class="form-control" id="special_request" name="special_request" rows="3"><?php echo htmlspecialchars($special_request ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-control" id="status" name="status">
                                <option value="pending" <?php echo (($status ?? 'pending') == 'pending') ? 'selected' : ''; ?>>Đang chờ</option>
                                <option value="confirmed" <?php echo (($status ?? '') == 'confirmed') ? 'selected' : ''; ?>>Đã xác nhận</option>
                                <option value="cancelled" <?php echo (($status ?? '') == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                                <option value="completed" <?php echo (($status ?? '') == 'completed') ? 'selected' : ''; ?>>Đã hoàn thành</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="manage_reservations.php" class="btn btn-danger">Hủy</a>
                        <button type="submit" class="btn btn-success">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Set minimum date for reservation
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('reservation_date').setAttribute('min', today);
        });
    </script>
</body>
</html> 