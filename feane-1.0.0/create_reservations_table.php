<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: admin.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// SQL để tạo bảng reservations (đặt bàn)
$sql = "CREATE TABLE IF NOT EXISTS reservations (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    party_size INT(11) NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    special_request TEXT,
    status ENUM('confirmed', 'pending', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// Thực thi câu lệnh tạo bảng
if ($conn->query($sql) === TRUE) {
    // Thông báo thành công và chuyển hướng
    header("Location: manage_reservations.php?success=table_created");
    exit;
} else {
    // Thông báo lỗi và chuyển hướng
    header("Location: manage_reservations.php?error=table_creation_failed&message=" . urlencode($conn->error));
    exit;
}

$conn->close();
?> 