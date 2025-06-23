<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Nếu người dùng là admin, chuyển hướng đến dashboard
if($_SESSION['username'] === 'admin') {
    header("location: dashboard.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// Kiểm tra bảng đã tồn tại chưa
$tableExists = $conn->query("SHOW TABLES LIKE 'customers'")->num_rows > 0;

if ($tableExists) {
    // Bảng đã tồn tại, quay lại trang quản lý khách hàng
    header("location: manage_customers.php?info=table_exists");
    exit;
}

// Tạo bảng customers
$sql = "CREATE TABLE customers (
    id INT(6) UNSIGNED PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    // Tạo bảng thành công, chèn dữ liệu mẫu
    $demoData = [
        [1, 'Nguyễn Văn A', '0987654321', 'nguyenvana@example.com', 'Hà Nội, Việt Nam', 'Khách hàng thân thiết'],
        [2, 'Trần Thị B', '0912345678', 'tranthib@example.com', 'Hồ Chí Minh, Việt Nam', 'Khách hàng mới'],
        [3, 'Lê Văn C', '0901234567', 'levanc@example.com', 'Đà Nẵng, Việt Nam', 'Khách hàng VIP']
    ];
    
    foreach ($demoData as $data) {
        $insertSql = "INSERT INTO customers (id, name, phone, email, address, description) 
                     VALUES ($data[0], '$data[1]', '$data[2]', '$data[3]', '$data[4]', '$data[5]')";
        $conn->query($insertSql);
    }
    
    // Chuyển hướng với thông báo thành công
    header("location: manage_customers.php?success=table_created");
    exit;
} else {
    // Lỗi khi tạo bảng
    header("location: manage_customers.php?error=table_creation_failed");
    exit;
}

// Đóng kết nối
$conn->close();
?> 