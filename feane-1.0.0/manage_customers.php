<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Nếu người dùng là admin, chuyển hướng đến trang quản lý tài khoản
if($_SESSION['username'] === 'admin') {
    header("location: manage_users.php");
    exit;
}

// Kết nối database
require_once 'config.php';

// Lấy danh sách khách hàng (giả định)
$sql = "SELECT * FROM customers";
$hasCustomersTable = false;

// Kiểm tra bảng customers có tồn tại không
$tableExists = $conn->query("SHOW TABLES LIKE 'customers'")->num_rows > 0;

// Nếu bảng tồn tại thì truy vấn dữ liệu
if ($tableExists) {
    $hasCustomersTable = true;
    $result = $conn->query($sql);
} else {
    // Bảng chưa tồn tại
    $hasCustomersTable = false;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng</title>
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
        
        /* Customer card styles */
        .customer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .customer-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
            overflow: hidden;
        }
        
        .customer-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .customer-card-title {
            font-size: 18px;
            color: #2c3e50;
            margin: 0;
        }
        
        .customer-card-actions {
            display: flex;
            gap: 5px;
        }
        
        .customer-card-body {
            margin-bottom: 15px;
        }
        
        .customer-info-item {
            margin-bottom: 10px;
            display: flex;
        }
        
        .customer-info-label {
            font-weight: bold;
            min-width: 120px;
            color: #7f8c8d;
        }
        
        .customer-info-value {
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
        
        /* Search Bar Styles */
        .search-bar-container {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            border-radius: 30px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.07);
            padding: 8px 18px;
            width: 400px;
            max-width: 100%;
            margin-bottom: 25px;
            border: 1px solid #e1e4ea;
            transition: box-shadow 0.2s;
        }
        .search-bar-container:focus-within {
            box-shadow: 0 4px 16px rgba(52,152,219,0.13);
            border-color: #3498db;
        }
        .search-bar-icon {
            color: #b2bec3;
            font-size: 20px;
            margin-right: 8px;
        }
        #searchInput.form-control {
            border: none;
            outline: none;
            background: transparent;
            font-size: 16px;
            width: 100%;
            color: #2c3e50;
            padding: 0;
            box-shadow: none;
        }
        #searchInput.form-control::placeholder {
            color: #b2bec3;
            opacity: 1;
        }
        /* Search Results Animation */
        #searchResults {
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">        <?php
        require_once 'includes/sidebar.php';
        echo getSidebarMenu('customers');
        ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Quản lý khách hàng</h2>
                <div class="user-actions">
                    <a href="add_customer.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm khách hàng mới</a>
                    <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
                </div>
            </div>
            
            <!-- Welcome message -->
            <div class="welcome-container">
                <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['fullname'] ?: $_SESSION['username']); ?>!</h3>
                <p>Chào mừng đến với hệ thống quản lý khách hàng. Tại đây bạn có thể xem và quản lý thông tin của tất cả khách hàng.</p>
            </div>
            
            <!-- Search Bar -->
            <div style="margin-bottom: 25px;">
                <div class="search-bar-container">
                    <i class="fas fa-search search-bar-icon"></i>
                    <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm khách hàng theo tên, số điện thoại, email..." style="width: 350px; display: inline-block;">
                </div>
            </div>
            <div id="searchResults" class="customer-grid" style="display:none;"></div>
            <div id="customerGridWrapper">
            <?php if(!$hasCustomersTable): ?>
                <!-- Empty state when customers table doesn't exist -->
                <div class="empty-state">
                    <i class="fas fa-database"></i>
                    <h3>Chưa có dữ liệu khách hàng</h3>
                    <p>Bảng dữ liệu khách hàng chưa được tạo trong hệ thống.</p>
                    <button class="btn btn-primary" id="createTableBtn">Tạo bảng khách hàng</button>
                </div>
            <?php elseif($result->num_rows == 0): ?>
                <!-- Empty state when no customers -->
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>Chưa có khách hàng nào</h3>
                    <p>Bạn chưa có khách hàng nào. Hãy bắt đầu bằng cách thêm khách hàng đầu tiên!</p>
                    <a href="add_customer.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm khách hàng</a>
                </div>
            <?php else: ?>
                <!-- Customer Grid -->
                <div class="customer-grid">
                    <?php
                    while($row = $result->fetch_assoc()) {
                        ?>
                        <div class="customer-card">
                            <div class="customer-card-header">
                                <h3 class="customer-card-title">                                    <?php echo htmlspecialchars($row["fullname"]); ?>
                                    <span class="badge">ID: <?php echo $row["id"]; ?></span>
                                </h3>
                                <div class="customer-card-actions">
                                    <a href="edit_customer.php?id=<?php echo $row["id"]; ?>" class="action-btn edit-btn" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                    <a href="manage_customers.php?delete_id=<?php echo $row["id"]; ?>" class="action-btn delete-btn" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa khách hàng này?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                            <div class="customer-card-body">
                                <div class="customer-info-item">
                                    <div class="customer-info-label">Số điện thoại:</div>
                                    <div class="customer-info-value"><?php echo htmlspecialchars($row["phone"] ?: "Chưa cập nhật"); ?></div>
                                </div>
                                
                                <div class="customer-info-item">
                                    <div class="customer-info-label">Email:</div>
                                    <div class="customer-info-value"><?php echo htmlspecialchars($row["email"] ?: "Chưa cập nhật"); ?></div>
                                </div>
                                
                                <div class="customer-info-item">
                                    <div class="customer-info-label">Số lượng người:</div>
                                    <div class="customer-info-value"><?php echo htmlspecialchars($row["people_count"] ?: "Chưa cập nhật"); ?></div>
                                </div>
                                
                                <div class="customer-info-item">
                                    <div class="customer-info-label">Ngày đặt:</div>
                                    <div class="customer-info-value"><?php echo htmlspecialchars($row["booking_date"] ?: "Chưa cập nhật"); ?></div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Script to create customers table
        document.getElementById('createTableBtn')?.addEventListener('click', function() {
            if(confirm('Bạn có chắc muốn tạo bảng khách hàng?')) {
                window.location.href = 'create_customers_table.php';
            }
        });
    </script>
    <script src="js/jquery-3.4.1.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#searchInput').on('input', function() {
            var query = $(this).val().trim();
            if(query.length === 0) {
                $('#searchResults').hide();
                $('#customerGridWrapper').show();
                return;
            }
            $.ajax({
                url: 'search_customers.php',
                method: 'GET',
                data: { q: query },
                success: function(data) {
                    $('#searchResults').html(data).show();
                    $('#customerGridWrapper').hide();
                },
                error: function() {
                    $('#searchResults').html('<div class="alert alert-danger">Lỗi khi tìm kiếm khách hàng.</div>').show();
                    $('#customerGridWrapper').hide();
                }
            });
        });
    });
    </script>
</body>
</html>