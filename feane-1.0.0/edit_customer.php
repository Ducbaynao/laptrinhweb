<?php
session_start();

// Kiểm tra đăng nhập
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: admin.php");
    exit;
}

require_once 'config.php';

$fullname = $phone = $email = $people_count = $booking_date = "";
$fullname_err = $phone_err = $email_err = $people_count_err = $booking_date_err = "";

// Xử lý khi form được gửi đi
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    
    // Validate fullname
    if(empty(trim($_POST["fullname"]))) {
        $fullname_err = "Vui lòng nhập họ tên khách hàng";
    } else {
        $fullname = trim($_POST["fullname"]);
    }
    
    // Validate phone
    if(empty(trim($_POST["phone"]))) {
        $phone_err = "Vui lòng nhập số điện thoại";
    } else {
        $phone = trim($_POST["phone"]);
    }
    
    // Validate email (optional)
    if(!empty(trim($_POST["email"]))) {
        if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $email_err = "Email không hợp lệ";
        } else {
            $email = trim($_POST["email"]);
        }
    }
    
    // Validate people_count
    if(empty(trim($_POST["people_count"]))) {
        $people_count_err = "Vui lòng nhập số lượng người";
    } else {
        $people_count = trim($_POST["people_count"]);
        if(!is_numeric($people_count) || $people_count < 1) {
            $people_count_err = "Số lượng người phải là số dương";
        }
    }
    
    // Validate booking_date
    if(empty(trim($_POST["booking_date"]))) {
        $booking_date_err = "Vui lòng chọn ngày đặt bàn";
    } else {
        $booking_date = trim($_POST["booking_date"]);
    }
    
    // Check input errors before updating the database
    if(empty($fullname_err) && empty($phone_err) && empty($email_err) && empty($people_count_err) && empty($booking_date_err)) {
        $sql = "UPDATE customers SET fullname=?, phone=?, email=?, people_count=?, booking_date=? WHERE id=?";
        
        if($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssisi", $fullname, $phone, $email, $people_count, $booking_date, $id);
            
            if($stmt->execute()) {
                header("location: manage_customers.php?msg=edit_success");
                exit();
            } else {
                echo "Có lỗi xảy ra. Vui lòng thử lại sau.";
            }
            
            $stmt->close();
        }
    }
} else {
    // Lấy thông tin khách hàng từ ID
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        $id = trim($_GET["id"]);
        
        $sql = "SELECT * FROM customers WHERE id = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            if($stmt->execute()) {
                $result = $stmt->get_result();
                
                if($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    
                    $fullname = $row["fullname"];
                    $phone = $row["phone"];
                    $email = $row["email"];
                    $people_count = $row["people_count"];
                    $booking_date = $row["booking_date"];
                } else {
                    header("location: manage_customers.php");
                    exit();
                }
                
            } else {
                echo "Có lỗi xảy ra. Vui lòng thử lại sau.";
            }
        }
        
        $stmt->close();
    } else {
        header("location: manage_customers.php");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thông tin khách hàng</title>
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
        
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #34495e;
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
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .invalid-feedback {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
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
                <a href="profile.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
                        <i class="fas fa-user-circle"></i>
                        <span>Thông tin cá nhân</span>
                    </div>
                </a>
                <a href="manage_customers.php" style="text-decoration: none; color: white;">
                    <div class="menu-item active">
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
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Chỉnh sửa thông tin khách hàng</h2>
            </div>
            
            <div class="form-container">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    
                    <div class="form-group">
                        <label>Họ tên</label>
                        <input type="text" name="fullname" class="form-control <?php echo (!empty($fullname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $fullname; ?>">
                        <?php if(!empty($fullname_err)): ?>
                            <div class="invalid-feedback"><?php echo $fullname_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                        <?php if(!empty($phone_err)): ?>
                            <div class="invalid-feedback"><?php echo $phone_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Email (không bắt buộc)</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <?php if(!empty($email_err)): ?>
                            <div class="invalid-feedback"><?php echo $email_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Số lượng người</label>
                        <input type="number" name="people_count" class="form-control <?php echo (!empty($people_count_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $people_count; ?>" min="1">
                        <?php if(!empty($people_count_err)): ?>
                            <div class="invalid-feedback"><?php echo $people_count_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Ngày đặt bàn</label>
                        <input type="date" name="booking_date" class="form-control <?php echo (!empty($booking_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $booking_date; ?>">
                        <?php if(!empty($booking_date_err)): ?>
                            <div class="invalid-feedback"><?php echo $booking_date_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <a href="manage_customers.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
