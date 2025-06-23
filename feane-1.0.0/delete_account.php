<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['username'] !== 'admin') {
    header("location: admin.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// Kiểm tra id tài khoản
if(!isset($_GET['id'])) {
    header("location: manage_accounts.php");
    exit;
}

$account_id = intval($_GET['id']);

// Kiểm tra xem có phải tài khoản admin không
$check_sql = "SELECT username FROM users WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $account_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if($result->num_rows > 0) {
    $account = $result->fetch_assoc();
    
    // Không cho phép xóa tài khoản admin
    if($account['username'] === 'admin') {
        header("location: manage_accounts.php?error=delete_admin");
        exit;
    }
    
    // Xóa tài khoản
    $delete_sql = "DELETE FROM users WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $account_id);
    
    if($delete_stmt->execute()) {
        header("location: manage_accounts.php?success=delete");
    } else {
        header("location: manage_accounts.php?error=delete_failed");
    }
    
    $delete_stmt->close();
} else {
    header("location: manage_accounts.php?error=account_not_found");
}

$check_stmt->close();
$conn->close();
?> 