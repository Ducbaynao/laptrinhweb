<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: admin.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// Lấy danh sách đặt bàn
$sql = "SELECT * FROM reservations ORDER BY reservation_date DESC, reservation_time DESC";
$hasReservationsTable = false;

// Kiểm tra bảng reservations có tồn tại không
$tableExists = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;

// Nếu bảng tồn tại thì truy vấn dữ liệu
if ($tableExists) {
    $hasReservationsTable = true;
    $result = $conn->query($sql);
} else {
    // Bảng chưa tồn tại
    $hasReservationsTable = false;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đặt bàn</title>
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
        
        /* Reservation card styles */
        .reservation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .reservation-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
            overflow: hidden;
            transition: transform 0.3s ease;
            position: relative;
        }
        
        .reservation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .reservation-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .reservation-card-title {
            font-size: 18px;
            color: #2c3e50;
            margin: 0;
        }
        
        .reservation-card-actions {
            display: flex;
            gap: 5px;
        }
        
        .reservation-card-body {
            margin-bottom: 15px;
        }
        
        .reservation-info-item {
            margin-bottom: 10px;
            display: flex;
        }
        
        .reservation-info-label {
            font-weight: bold;
            min-width: 120px;
            color: #7f8c8d;
        }
        
        .reservation-info-value {
            flex: 1;
            word-break: break-word;
        }
        
        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            color: white;
        }
        
        .edit-btn {
            background-color: #3498db;
        }
        
        .delete-btn {
            background-color: #e74c3c;
        }
        
        .view-btn {
            background-color: #2ecc71;
        }
        
        .confirm-btn {
            background-color: #f39c12;
        }
        
        /* Alert styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 12px;
            font-weight: bold;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 10px;
            background-color: #3498db;
            color: white;
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .badge-confirmed {
            background-color: #2ecc71;
        }
        
        .badge-pending {
            background-color: #f39c12;
        }
        
        .badge-cancelled {
            background-color: #e74c3c;
        }
        
        .badge-completed {
            background-color: #3498db;
        }
        
        .welcome-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .welcome-container h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 60px;
            color: #bdc3c7;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .empty-state p {
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            margin-left: 10px;
            font-size: 12px;
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
                    <div class="menu-item active">
                        <i class="fas fa-calendar-check"></i>
                        <span>Quản lý đặt bàn</span>
                    </div>
                </a>
                <a href="add_reservation.php" style="text-decoration: none; color: white;">
                    <div class="menu-item">
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
                <h2>Quản lý đặt bàn</h2>
                <div class="user-actions">
                    <a href="add_reservation.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm đặt bàn mới</a>
                    <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
                </div>
            </div>
            
            <?php 
            // Hiển thị thông báo
            if(isset($_GET['success'])) {
                if($_GET['success'] == 'table_created') {
                    echo '<div class="alert alert-success">Tạo bảng đặt bàn thành công!</div>';
                } else if($_GET['success'] == 'add') {
                    echo '<div class="alert alert-success">Thêm đặt bàn thành công!</div>';
                } else if($_GET['success'] == 'update') {
                    echo '<div class="alert alert-success">Cập nhật đặt bàn thành công!</div>';
                } else if($_GET['success'] == 'delete') {
                    echo '<div class="alert alert-success">Xóa đặt bàn thành công!</div>';
                }
            }
            
            if(isset($_GET['error'])) {
                if($_GET['error'] == 'table_creation_failed') {
                    echo '<div class="alert alert-danger">Tạo bảng đặt bàn thất bại!</div>';
                }
            }
            ?>
            
            <!-- Welcome message -->
            <div class="welcome-container">
                <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['fullname'] ?: $_SESSION['username']); ?>!</h3>
                <p>Đây là trang quản lý đặt bàn. Bạn có thể xem tất cả các lịch đặt bàn, thêm đặt bàn mới hoặc quản lý trạng thái đặt bàn.</p>
            </div>
            
            <?php if(!$hasReservationsTable): ?>
                <!-- Empty state when reservations table doesn't exist -->
                <div class="empty-state">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Chưa có dữ liệu đặt bàn</h3>
                    <p>Bảng dữ liệu đặt bàn chưa được tạo trong hệ thống.</p>
                    <a href="create_reservations_table.php" class="btn btn-primary">Tạo bảng đặt bàn</a>
                </div>
            <?php elseif($result->num_rows == 0): ?>
                <!-- Empty state when no reservations -->
                <div class="empty-state">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Chưa có đặt bàn nào</h3>
                    <p>Hiện tại chưa có khách hàng nào đặt bàn. Hãy bắt đầu bằng cách thêm đặt bàn mới!</p>
                    <a href="add_reservation.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm đặt bàn</a>
                </div>
            <?php else: ?>
                <!-- Reservation Grid -->
                <div class="reservation-grid">
                    <?php
                    while($row = $result->fetch_assoc()) {
                        // Xác định class của badge dựa trên trạng thái
                        $badgeClass = "badge-" . $row["status"];
                        
                        // Format ngày và giờ
                        $reservationDate = date("d/m/Y", strtotime($row["reservation_date"]));
                        $reservationTime = date("H:i", strtotime($row["reservation_time"]));
                        
                        // Format trạng thái
                        $statusText = "";
                        switch($row["status"]) {
                            case "confirmed":
                                $statusText = "Đã xác nhận";
                                break;
                            case "pending":
                                $statusText = "Đang chờ";
                                break;
                            case "cancelled":
                                $statusText = "Đã hủy";
                                break;
                            case "completed":
                                $statusText = "Đã hoàn thành";
                                break;
                        }
                        ?>
                        <div class="reservation-card">
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                            <div class="reservation-card-header">
                                <h3 class="reservation-card-title">
                                    <?php echo htmlspecialchars($row["name"]); ?>
                                </h3>
                                <div class="reservation-card-actions">
                                    <a href="view_reservation.php?id=<?php echo $row["id"]; ?>" class="action-btn view-btn" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                                    <a href="edit_reservation.php?id=<?php echo $row["id"]; ?>" class="action-btn edit-btn" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                    <?php if($row["status"] == "pending"): ?>
                                    <a href="confirm_reservation.php?id=<?php echo $row["id"]; ?>" class="action-btn confirm-btn" title="Xác nhận"><i class="fas fa-check"></i></a>
                                    <?php endif; ?>
                                    <a href="delete_reservation.php?id=<?php echo $row["id"]; ?>" class="action-btn delete-btn" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa đặt bàn này?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                            <div class="reservation-card-body">
                                <div class="reservation-info-item">
                                    <div class="reservation-info-label">Số điện thoại:</div>
                                    <div class="reservation-info-value"><?php echo htmlspecialchars($row["phone"]); ?></div>
                                </div>
                                
                                <div class="reservation-info-item">
                                    <div class="reservation-info-label">Email:</div>
                                    <div class="reservation-info-value"><?php echo htmlspecialchars($row["email"] ?: "Chưa cập nhật"); ?></div>
                                </div>
                                
                                <div class="reservation-info-item">
                                    <div class="reservation-info-label">Ngày đặt:</div>
                                    <div class="reservation-info-value"><?php echo $reservationDate; ?></div>
                                </div>
                                
                                <div class="reservation-info-item">
                                    <div class="reservation-info-label">Giờ đặt:</div>
                                    <div class="reservation-info-value"><?php echo $reservationTime; ?></div>
                                </div>
                                
                                <div class="reservation-info-item">
                                    <div class="reservation-info-label">Số người:</div>
                                    <div class="reservation-info-value"><?php echo htmlspecialchars($row["party_size"]); ?> người</div>
                                </div>
                                
                                <?php if(!empty($row["special_request"])): ?>
                                <div class="reservation-info-item">
                                    <div class="reservation-info-label">Yêu cầu đặc biệt:</div>
                                    <div class="reservation-info-value"><?php echo htmlspecialchars($row["special_request"]); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 