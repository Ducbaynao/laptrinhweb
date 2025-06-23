<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Select the database
    $conn->select_db($dbname);
    
    // Kiểm tra xem bảng users đã tồn tại chưa
    $tableExists = $conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0;
    
    if (!$tableExists) {
        // Tạo bảng users mới với đầy đủ các cột, không sử dụng AUTO_INCREMENT cho ID
        $sql = "CREATE TABLE users (
            id INT(6) UNSIGNED PRIMARY KEY,
            username VARCHAR(30) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            fullname VARCHAR(100),
            birthday DATE,
            address VARCHAR(255),
            email VARCHAR(100),
            description TEXT,
            job_position VARCHAR(100)
        )";
        $conn->query($sql);
    } else {
        // Bảng đã tồn tại - kiểm tra các cột
        
        // Kiểm tra và thêm các cột mới nếu chưa có
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'fullname'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE users ADD fullname VARCHAR(100)");
        }
        
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'birthday'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE users ADD birthday DATE");
        }
        
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'address'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE users ADD address VARCHAR(255)");
        }
        
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE users ADD email VARCHAR(100)");
        }
        
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'description'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE users ADD description TEXT");
        }
        
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'job_position'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE users ADD job_position VARCHAR(100)");
        }
    }
    
    // Kiểm tra xem tài khoản admin đã tồn tại chưa
    $checkUser = "SELECT * FROM users WHERE username = 'admin'";
    $result = $conn->query($checkUser);
    
    if ($result->num_rows == 0) {
        // Tạo tài khoản admin nếu chưa tồn tại
        $hashed_password = password_hash('123123', PASSWORD_DEFAULT);
        
        // Kiểm tra có bản ghi nào trong bảng users chưa
        $countUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        
        if ($countUsers == 0) {
            // Nếu chưa có bản ghi nào, sử dụng ID = 1
            $sql = "INSERT INTO users (id, username, password, fullname, birthday, address, email, description, job_position) 
                    VALUES (1, 'admin', '$hashed_password', 'Admin User', '1990-01-01', 'Hà Nội, Việt Nam', 'admin@example.com', 'Quản trị viên hệ thống', 'Quản trị viên')";
        } else {
            // Nếu đã có bản ghi, lấy ID lớn nhất + 1
            $maxId = $conn->query("SELECT MAX(id) as max_id FROM users")->fetch_assoc()['max_id'];
            $newId = $maxId + 1;
            
            $sql = "INSERT INTO users (id, username, password, fullname, birthday, address, email, description, job_position) 
                    VALUES ($newId, 'admin', '$hashed_password', 'Admin User', '1990-01-01', 'Hà Nội, Việt Nam', 'admin@example.com', 'Quản trị viên hệ thống', 'Quản trị viên')";
        }
        
        $conn->query($sql);
    } else {
        // Cập nhật thông tin cho tài khoản admin nếu các cột mới là NULL
        $sql = "UPDATE users SET 
                fullname = COALESCE(fullname, 'Admin User'),
                birthday = COALESCE(birthday, '1990-01-01'),
                address = COALESCE(address, 'Hà Nội, Việt Nam'),
                email = COALESCE(email, 'admin@example.com'),
                description = COALESCE(description, 'Quản trị viên hệ thống'),
                job_position = COALESCE(job_position, 'Quản trị viên')
                WHERE username = 'admin'";
        $conn->query($sql);
    }
}
?> 