<?php
session_start();

// Kiểm tra đăng nhập
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Kiểm tra xem ID đã được cung cấp chưa
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    // Không có ID được truyền, quay lại trang quản lý
    header("location: manage_users.php");
    exit;
}

// Kết nối CSDL
require_once "config.php";

// Lấy ID người dùng
$id = trim($_GET["id"]);

// Ngăn chặn việc xóa tài khoản đang đăng nhập
if($id == $_SESSION['user_id']) {
    header("location: manage_users.php?error=self_delete");
    exit;
}

// Chuẩn bị câu lệnh xóa
$sql = "DELETE FROM users WHERE id = ?";

if($stmt = $conn->prepare($sql)) {
    // Gán tham số
    $stmt->bind_param("i", $id);
    
    // Thực thi câu lệnh
    if($stmt->execute()) {
        // Xóa thành công, quay lại trang quản lý
        header("location: manage_users.php?success=delete");
        exit;
    } else {
        // Lỗi khi xóa
        header("location: manage_users.php?error=delete_failed");
        exit;
    }
    
    // Đóng statement
    $stmt->close();
}

// Đóng kết nối
$conn->close();
?> 