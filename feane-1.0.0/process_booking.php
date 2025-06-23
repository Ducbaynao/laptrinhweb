<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = htmlspecialchars(trim($_POST['email']));
    $people_count = intval($_POST['people_count']);
    $booking_date = trim($_POST['booking_date']);
    
    // Validate input
    $errors = [];
    if (empty($fullname) || strlen($fullname) < 2) {
        $errors[] = "Họ tên phải có ít nhất 2 ký tự";
    }
    if (empty($phone) || !preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Số điện thoại không hợp lệ (phải có 10 chữ số)";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    if ($people_count < 1 || $people_count > 20) {
        $errors[] = "Số lượng người phải từ 1 đến 20";
    }
      // Validate booking date (must be in the future)
    $booking_timestamp = strtotime($booking_date);
    if ($booking_timestamp === false || $booking_timestamp < time()) {
        $errors[] = "Ngày đặt bàn phải là thời gian trong tương lai";
    }      if (empty($errors)) {
        $sql = "INSERT INTO customers (fullname, phone, email, people_count, booking_date) 
                VALUES (?, ?, ?, ?, ?)";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $fullname, $phone, $email, $people_count, $booking_date);
        
        if ($stmt->execute()) {
            header("Location: index.html?booking=success");
        } else {
            header("Location: index.html?booking=error&message=" . urlencode($stmt->error));
        }
        $stmt->close();
    } else {
        header("Location: index.html?booking=error&message=" . urlencode(implode(", ", $errors)));
    }
    
    $conn->close();
} else {
    header("Location: index.html");
}
?>
