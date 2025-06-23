<?php
session_start();

// Kiểm tra đăng nhập
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// Định nghĩa biến và khởi tạo giá trị rỗng
$user_id = $username = $password = $confirm_password = $fullname = $birthday = $address = $email = $description = $job_position = "";
$user_id_err = $username_err = $password_err = $confirm_password_err = $email_err = "";
$success_message = "";
$duplicate_id_error = false; // Biến đánh dấu lỗi ID trùng lặp
$add_success = false; // Biến đánh dấu thêm tài khoản thành công

// Xử lý dữ liệu khi form được submit
if($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // Kiểm tra ID
    if(empty(trim($_POST["user_id"]))){
        $user_id_err = "Vui lòng nhập ID.";
    } elseif(!is_numeric(trim($_POST["user_id"]))) {
        $user_id_err = "ID phải là số.";
    } else {
        // Kiểm tra ID đã tồn tại chưa
        $sql = "SELECT id FROM users WHERE id = ?";
        
        if($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $param_user_id);
            $param_user_id = trim($_POST["user_id"]);
            
            $stmt->execute();
            $stmt->store_result();
            
            if($stmt->num_rows > 0){
                $user_id_err = "ID này đã tồn tại.";
                $duplicate_id_error = true; // Đánh dấu lỗi ID trùng lặp
            } else{
                $user_id = trim($_POST["user_id"]);
            }
            
            $stmt->close();
        }
    }
    
    // Kiểm tra username
    if(empty(trim($_POST["username"]))){
        $username_err = "Vui lòng nhập tên tài khoản.";
    } else {
        // Chuẩn bị câu lệnh select
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = $conn->prepare($sql)) {
            // Gán tham số
            $stmt->bind_param("s", $param_username);
            
            // Thiết lập tham số
            $param_username = trim($_POST["username"]);
            
            // Thực thi câu lệnh
            if($stmt->execute()) {
                // Lưu kết quả
                $stmt->store_result();
                
                if($stmt->num_rows == 1){
                    $username_err = "Tên tài khoản này đã tồn tại.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Đã xảy ra lỗi. Vui lòng thử lại sau.";
            }

            // Đóng statement
            $stmt->close();
        }
    }
    
    // Kiểm tra password
    if(empty(trim($_POST["password"]))){
        $password_err = "Vui lòng nhập mật khẩu.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Kiểm tra confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Vui lòng xác nhận mật khẩu.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Mật khẩu không khớp.";
        }
    }
    
    // Kiểm tra email
    if(!empty(trim($_POST["email"])) && !filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Email không hợp lệ.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Lấy các giá trị khác
    $fullname = trim($_POST["fullname"]);
    $birthday = !empty($_POST["birthday"]) ? $_POST["birthday"] : NULL;
    $address = trim($_POST["address"]);
    $description = trim($_POST["description"]);
    $job_position = trim($_POST["job_position"]);
    
    // Kiểm tra lỗi trước khi chèn vào database
    if(empty($user_id_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err)){
        
        // Chuẩn bị câu lệnh insert
        $sql = "INSERT INTO users (id, username, password, fullname, birthday, address, email, description, job_position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
         
        if($stmt = $conn->prepare($sql)){
            // Gán tham số
            $stmt->bind_param("issssssss", $param_user_id, $param_username, $param_password, $param_fullname, $param_birthday, $param_address, $param_email, $param_description, $param_job_position);
            
            // Thiết lập tham số
            $param_user_id = $user_id;
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu
            $param_fullname = $fullname;
            $param_birthday = $birthday;
            $param_address = $address;
            $param_email = $email;
            $param_description = $description;
            $param_job_position = $job_position;
            
            // Thực thi câu lệnh
            if($stmt->execute()){
                // Đã thêm thành công
                $success_message = "Thêm tài khoản thành công!";
                $add_success = true; // Đánh dấu thêm tài khoản thành công
                
                // Xóa dữ liệu đã nhập
                $user_id = $username = $password = $confirm_password = $fullname = $birthday = $address = $email = $description = $job_position = "";
            } else{
                echo "Đã xảy ra lỗi. Vui lòng thử lại sau.";
            }

            // Đóng statement
            $stmt->close();
        }
    }
    
    // Đóng kết nối
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm người dùng</title>
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
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .form-title {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 20px;
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
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 15px;
        }
        
        .form-control:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .invalid-feedback {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-col {
            flex: 1;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
        }
        
        .text-muted {
            color: #6c757d;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            width: 400px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            text-align: center;
        }
        
        .modal-title {
            color: #e74c3c;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .modal-body {
            margin-bottom: 20px;
            color: #333;
            font-size: 16px;
        }
        
        .modal-footer {
            text-align: center;
        }
        
        .modal-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        
        .modal-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <!-- Modal Thông báo lỗi -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">
                <i class="fas fa-exclamation-circle"></i> Lỗi
            </div>
            <div class="modal-body">
                Tài khoản đã tồn tại vui lòng tạo tài khoản mới
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="closeModal('error')">OK</button>
            </div>
        </div>
    </div>

    <!-- Modal Thông báo thành công -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-title" style="color: #2ecc71;">
                <i class="fas fa-check-circle"></i> Thành công
            </div>
            <div class="modal-body">
                Thêm tài khoản mới thành công
            </div>
            <div class="modal-footer">
                <button class="modal-btn" style="background-color: #2ecc71;" onclick="closeModal('success')">OK</button>
            </div>
        </div>
    </div>

    <?php if($duplicate_id_error): ?>
    <script>
        window.onload = function() {
            // Hiển thị modal khi ID đã tồn tại
            document.getElementById('errorModal').style.display = 'block';
        }
    </script>
    <?php endif; ?>
    
    <?php if($add_success): ?>
    <script>
        window.onload = function() {
            // Hiển thị modal khi thêm tài khoản thành công
            document.getElementById('successModal').style.display = 'block';
        }
    </script>
    <?php endif; ?>
    
    <script>
        function closeModal(type) {
            // Đóng modal và chuyển hướng
            if (type === 'error') {
                document.getElementById('errorModal').style.display = 'none';
                window.location.href = 'manage_users.php';
            } else {
                document.getElementById('successModal').style.display = 'none';
                window.location.href = 'admin_profile.php';
            }
        }
    </script>
    
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
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Trang chủ</span>
                    </div>
                </a>
                <a href="manage_users.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
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
                <h2>Thêm người dùng mới</h2>
                <div class="user-actions">
                    <a href="manage_users.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                    <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
                </div>
            </div>
            
            <?php if(!empty($success_message) && !$add_success): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <!-- Form Thêm người dùng -->
            <div class="form-container">
                <h3 class="form-title">Nhập thông tin người dùng mới</h3>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label class="form-label">ID</label>
                        <input type="text" name="user_id" class="form-control <?php echo (!empty($user_id_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $user_id; ?>">
                        <span class="invalid-feedback"><?php echo $user_id_err; ?></span>
                        <small class="text-muted">Nhập ID cho người dùng (chỉ nhập số)</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Họ tên</label>
                                <input type="text" name="fullname" class="form-control" value="<?php echo $fullname; ?>">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Tên tài khoản</label>
                                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                                <span class="invalid-feedback"><?php echo $username_err; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Mật khẩu</label>
                                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                                <span class="invalid-feedback"><?php echo $password_err; ?></span>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Xác nhận mật khẩu</label>
                                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                                <span class="invalid-feedback"><?php echo $email_err; ?></span>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Ngày sinh</label>
                                <input type="date" name="birthday" class="form-control" value="<?php echo $birthday; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" name="address" class="form-control" value="<?php echo $address; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Vị trí công việc</label>
                        <input type="text" name="job_position" class="form-control" value="<?php echo $job_position; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo $description; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Thêm người dùng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 