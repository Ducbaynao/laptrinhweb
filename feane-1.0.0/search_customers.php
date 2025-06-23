<?php
require_once 'config.php';
header('Content-Type: text/html; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if($q === '') {
    echo '';
    exit;
}

// Tìm kiếm theo tên, số điện thoại, email
$stmt = $conn->prepare("SELECT * FROM customers WHERE fullname LIKE CONCAT('%', ?, '%') OR phone LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%') ORDER BY id DESC");
$stmt->bind_param('sss', $q, $q, $q);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    echo '<div class="empty-state"><i class="fas fa-users"></i><h3>Không tìm thấy khách hàng phù hợp</h3></div>';
    exit;
}

while($row = $result->fetch_assoc()) {
    echo '<div class="customer-card">';
    echo '<div class="customer-card-header">';
    echo '<h3 class="customer-card-title">'.htmlspecialchars($row["fullname"]).' <span class="badge">ID: '.htmlspecialchars($row["id"]).'</span></h3>';
    echo '<div class="customer-card-actions">';
    echo '<a href="edit_customer.php?id='.htmlspecialchars($row["id"]).'" class="action-btn edit-btn" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>';
    echo '<a href="manage_customers.php?delete_id='.htmlspecialchars($row["id"]).'" class="action-btn delete-btn" title="Xóa" onclick="return confirm(\'Bạn có chắc chắn muốn xóa khách hàng này?\')"><i class="fas fa-trash"></i></a>';
    echo '</div></div>';
    echo '<div class="customer-card-body">';
    echo '<div class="customer-info-item"><div class="customer-info-label">Số điện thoại:</div><div class="customer-info-value">'.htmlspecialchars($row["phone"] ?: "Chưa cập nhật").'</div></div>';
    echo '<div class="customer-info-item"><div class="customer-info-label">Email:</div><div class="customer-info-value">'.htmlspecialchars($row["email"] ?: "Chưa cập nhật").'</div></div>';
    echo '<div class="customer-info-item"><div class="customer-info-label">Số lượng người:</div><div class="customer-info-value">'.htmlspecialchars($row["people_count"] ?: "Chưa cập nhật").'</div></div>';
    echo '<div class="customer-info-item"><div class="customer-info-label">Ngày đặt:</div><div class="customer-info-value">'.htmlspecialchars($row["booking_date"] ?: "Chưa cập nhật").'</div></div>';
    echo '</div></div>';
}
$stmt->close();
?>
