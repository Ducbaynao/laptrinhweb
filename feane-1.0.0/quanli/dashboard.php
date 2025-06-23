<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Include config file
require_once "config.php";

// Define variables
$search = "";
$search_result = array();
$sort_by = isset($_GET["sort_by"]) ? $_GET["sort_by"] : "id";
$sort_order = isset($_GET["sort_order"]) ? $_GET["sort_order"] : "DESC";

// Validate sort parameters
$allowed_sort_fields = ["id", "name", "birthdate", "people_count"];
if (!in_array($sort_by, $allowed_sort_fields)) {
    $sort_by = "id"; // Default to ID if invalid sort field
}

$allowed_sort_orders = ["ASC", "DESC"];
if (!in_array($sort_order, $allowed_sort_orders)) {
    $sort_order = "DESC"; // Default to descending if invalid order
}

// Define opposite sort order for toggle
$opposite_order = ($sort_order == "ASC") ? "DESC" : "ASC";

// Process search form
if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["search"])) {
    $search = trim($_GET["search"]);
    
    if(!empty($search)) {
        // Prepare a select statement with sorting
        $sql = "SELECT * FROM customers WHERE name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY $sort_by $sort_order";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            $param_search = "%" . $search . "%";
            mysqli_stmt_bind_param($stmt, "sss", $param_search, $param_search, $param_search);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                $search_result = mysqli_stmt_get_result($stmt);
            } else {
                echo "Đã xảy ra lỗi. Vui lòng thử lại sau.";
            }
            
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

// Retrieve all customers if no search is performed
if(empty($search)) {
    $sql = "SELECT * FROM customers ORDER BY $sort_by $sort_order";
    $search_result = mysqli_query($conn, $sql);
}
?>
 
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khách Hàng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #333;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info span {
            margin-right: 15px;
            font-weight: bold;
        }
        .logout-btn, .reset-btn, .btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin-left: 10px;
        }
        .logout-btn {
            background-color: #f44336;
        }
        .search-btn {
            background-color: #2196F3;
        }
        .add-btn {
            background-color: #4CAF50;
        }
        .edit-btn {
            background-color: #FFC107;
            color: #000;
        }
        .delete-btn {
            background-color: #f44336;
        }
        .logout-btn:hover {
            background-color: #d32f2f;
        }
        .content {
            background-color: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .welcome-message {
            font-size: 24px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        th a {
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        th a:hover {
            text-decoration: underline;
            color: #4CAF50;
        }
        .sort-icon {
            margin-left: 5px;
            font-style: normal;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .search-container {
            margin: 20px 0;
            display: flex;
        }
        .search-container input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        /* Popup Form Styles */
        .form-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 3px solid #f1f1f1;
            z-index: 9;
            background-color: white;
            max-width: 500px;
            width: 100%;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        .form-container {
            max-width: 100%;
        }
        .form-container input[type=text], .form-container input[type=email], .form-container input[type=date], .form-container select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f1f1f1;
        }
        .form-container .btn-container {
            display: flex;
            justify-content: space-between;
        }
        .form-container .btn {
            margin: 0;
        }
        .form-container .cancel-btn {
            background-color: #aaa;
        }
        .form-container label {
            font-weight: bold;
        }
        .form-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 8;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Quản Lý Khách Hàng</h1>
        <div class="user-info">
            <span>Xin chào, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
            <a href="logout.php" class="logout-btn">Đăng xuất</a>
        </div>
    </div>
    
    <div class="dashboard-container">
        <div class="content">
            <div class="welcome-message">
                Hệ thống quản lý khách hàng
            </div>
            
            <button class="btn add-btn" onclick="openAddForm()">Thêm khách hàng mới</button>
            
            <div class="search-container">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
                    <input type="text" placeholder="Tìm kiếm theo tên, số điện thoại hoặc email..." name="search" value="<?php echo $search; ?>">
                    <button type="submit" class="btn search-btn">Tìm kiếm</button>
                </form>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="?sort_by=id&sort_order=<?php echo ($sort_by == 'id') ? $opposite_order : 'ASC'; ?><?php echo (!empty($search)) ? '&search='.$search : ''; ?>">
                                ID 
                                <?php if($sort_by == 'id'): ?>
                                    <i class="sort-icon"><?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort_by=name&sort_order=<?php echo ($sort_by == 'name') ? $opposite_order : 'ASC'; ?><?php echo (!empty($search)) ? '&search='.$search : ''; ?>">
                                Tên
                                <?php if($sort_by == 'name'): ?>
                                    <i class="sort-icon"><?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>
                            <a href="?sort_by=birthdate&sort_order=<?php echo ($sort_by == 'birthdate') ? $opposite_order : 'ASC'; ?><?php echo (!empty($search)) ? '&search='.$search : ''; ?>">
                                Ngày sinh
                                <?php if($sort_by == 'birthdate'): ?>
                                    <i class="sort-icon"><?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>Giới tính</th>
                        <th>
                            <a href="?sort_by=people_count&sort_order=<?php echo ($sort_by == 'people_count') ? $opposite_order : 'ASC'; ?><?php echo (!empty($search)) ? '&search='.$search : ''; ?>">
                                Số lượng người
                                <?php if($sort_by == 'people_count'): ?>
                                    <i class="sort-icon"><?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($search_result && mysqli_num_rows($search_result) > 0) {
                        while($row = mysqli_fetch_assoc($search_result)) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['name'] . "</td>";
                            echo "<td>" . $row['phone'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . $row['birthdate'] . "</td>";
                            echo "<td>" . $row['gender'] . "</td>";
                            echo "<td>" . $row['people_count'] . "</td>";
                            echo "<td class='action-buttons'>";
                            echo "<button class='btn edit-btn' onclick='openEditForm(" . $row['id'] . ", \"" . $row['name'] . "\", \"" . $row['phone'] . "\", \"" . $row['email'] . "\", \"" . $row['birthdate'] . "\", \"" . $row['gender'] . "\", \"" . $row['people_count'] . "\")'>Sửa</button>";
                            echo "<button class='btn delete-btn' onclick='confirmDelete(" . $row['id'] . ")'>Xóa</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align:center;'>Không tìm thấy khách hàng nào.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Overlay Background -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Add Customer Form -->
    <div class="form-popup" id="addCustomerForm">
        <form action="add_customer.php" class="form-container" method="POST">
            <h2 class="form-title">Thêm khách hàng mới</h2>
            
            <label for="name"><b>Tên</b></label>
            <input type="text" placeholder="Nhập tên" name="name" required>
            
            <label for="phone"><b>Số điện thoại</b></label>
            <input type="text" placeholder="Nhập số điện thoại" name="phone" required>
            
            <label for="email"><b>Email</b></label>
            <input type="email" placeholder="Nhập email" name="email">
            
            <label for="birthdate"><b>Ngày sinh</b></label>
            <input type="date" name="birthdate">
            
            <label for="gender"><b>Giới tính</b></label>
            <select name="gender" required>
                <option value="Nam">Nam</option>
                <option value="Nữ">Nữ</option>
                <option value="Khác">Khác</option>
            </select>
            
            <label for="people_count"><b>Số lượng người</b></label>
            <input type="number" name="people_count" min="1" max="20" value="1" required>
            
            <div class="btn-container">
                <button type="button" class="btn cancel-btn" onclick="closeAddForm()">Đóng</button>
                <button type="submit" class="btn add-btn">Thêm</button>
            </div>
        </form>
    </div>
    
    <!-- Edit Customer Form -->
    <div class="form-popup" id="editCustomerForm">
        <form action="edit_customer.php" class="form-container" method="POST">
            <h2 class="form-title">Sửa thông tin khách hàng</h2>
            
            <input type="hidden" id="edit_id" name="id">
            
            <label for="edit_name"><b>Tên</b></label>
            <input type="text" placeholder="Nhập tên" id="edit_name" name="name" required>
            
            <label for="edit_phone"><b>Số điện thoại</b></label>
            <input type="text" placeholder="Nhập số điện thoại" id="edit_phone" name="phone" required>
            
            <label for="edit_email"><b>Email</b></label>
            <input type="email" placeholder="Nhập email" id="edit_email" name="email">
            
            <label for="edit_birthdate"><b>Ngày sinh</b></label>
            <input type="date" id="edit_birthdate" name="birthdate">
            
            <label for="edit_gender"><b>Giới tính</b></label>
            <select id="edit_gender" name="gender" required>
                <option value="Nam">Nam</option>
                <option value="Nữ">Nữ</option>
                <option value="Khác">Khác</option>
            </select>
            
            <label for="edit_people_count"><b>Số lượng người</b></label>
            <input type="number" id="edit_people_count" name="people_count" min="1" max="20" required>
            
            <div class="btn-container">
                <button type="button" class="btn cancel-btn" onclick="closeEditForm()">Đóng</button>
                <button type="submit" class="btn edit-btn">Cập nhật</button>
            </div>
        </form>
    </div>
    
    <script>
        // Open and close add form
        function openAddForm() {
            document.getElementById("addCustomerForm").style.display = "block";
            document.getElementById("overlay").style.display = "block";
        }
        
        function closeAddForm() {
            document.getElementById("addCustomerForm").style.display = "none";
            document.getElementById("overlay").style.display = "none";
        }
        
        // Open and close edit form
        function openEditForm(id, name, phone, email, birthdate, gender, people_count) {
            document.getElementById("edit_id").value = id;
            document.getElementById("edit_name").value = name;
            document.getElementById("edit_phone").value = phone;
            document.getElementById("edit_email").value = email;
            document.getElementById("edit_birthdate").value = birthdate;
            document.getElementById("edit_gender").value = gender;
            document.getElementById("edit_people_count").value = people_count;
            
            document.getElementById("editCustomerForm").style.display = "block";
            document.getElementById("overlay").style.display = "block";
        }
        
        function closeEditForm() {
            document.getElementById("editCustomerForm").style.display = "none";
            document.getElementById("overlay").style.display = "none";
        }
        
        // Confirm delete
        function confirmDelete(id) {
            if(confirm("Bạn có chắc chắn muốn xóa khách hàng này?")) {
                window.location.href = "delete_customer.php?id=" + id;
            }
        }
    </script>
</body>
</html> 