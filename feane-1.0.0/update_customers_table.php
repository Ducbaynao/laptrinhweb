<?php
require_once 'config.php';

// Thêm các cột mới cho chức năng đặt bàn
$sql = "ALTER TABLE customers 
        ADD COLUMN people_count INT,
        ADD COLUMN booking_date DATETIME";

if ($conn->query($sql) === TRUE) {
    echo "Đã cập nhật bảng customers thành công";
} else {
    echo "Lỗi cập nhật bảng: " . $conn->error;
}

$conn->close();
?>
