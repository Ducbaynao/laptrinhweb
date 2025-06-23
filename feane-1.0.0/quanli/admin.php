<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        .text-center {
            text-align: center;
            margin-top: 15px;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Đăng Nhập Hệ Thống</h2>
        
        <?php
        // Include config file
        require_once "config.php";
        
        // Initialize the session
        session_start();
        
        // Check if the user is already logged in, if yes then redirect to dashboard
        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
            header("location: dashboard.php");
            exit;
        }
        
        $username = $password = "";
        $usernameErr = $passwordErr = $loginErr = "";
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validate username
            if (empty($_POST["username"])) {
                $usernameErr = "Vui lòng nhập tên đăng nhập";
            } else {
                $username = trim($_POST["username"]);
            }
            
            // Validate password
            if (empty($_POST["password"])) {
                $passwordErr = "Vui lòng nhập mật khẩu";
            } else {
                $password = trim($_POST["password"]);
            }
            
            // Validate credentials
            if (empty($usernameErr) && empty($passwordErr)) {
                // Prepare a select statement
                $sql = "SELECT id, username, password FROM users WHERE username = ?";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "s", $param_username);
                    
                    // Set parameters
                    $param_username = $username;
                    
                    // Attempt to execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                        // Store result
                        mysqli_stmt_store_result($stmt);
                        
                        // Check if username exists, if yes then verify password
                        if (mysqli_stmt_num_rows($stmt) == 1) {                    
                            // Bind result variables
                            mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                            if (mysqli_stmt_fetch($stmt)) {
                                if (password_verify($password, $hashed_password)) {
                                    // Password is correct, so start a new session
                                    session_start();
                                    
                                    // Store data in session variables
                                    $_SESSION["loggedin"] = true;
                                    $_SESSION["id"] = $id;
                                    $_SESSION["username"] = $username;                            
                                    
                                    // Redirect user to dashboard page
                                    header("location: dashboard.php");
                                    exit;
                                } else {
                                    // Password is not valid
                                    $loginErr = "Tên đăng nhập hoặc mật khẩu không đúng";
                                }
                            }
                        } else {
                            // Username doesn't exist
                            $loginErr = "Tên đăng nhập hoặc mật khẩu không đúng";
                        }
                    } else {
                        $loginErr = "Đã xảy ra lỗi. Vui lòng thử lại sau.";
                    }
                    
                    // Close statement
                    mysqli_stmt_close($stmt);
                }
            }
            
            // Close connection
            mysqli_close($conn);
        }
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" value="<?php echo $username; ?>">
                <span class="error"><?php echo $usernameErr; ?></span>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password">
                <span class="error"><?php echo $passwordErr; ?></span>
            </div>
            
            <div class="form-group">
                <button type="submit">Đăng Nhập</button>
                <span class="error"><?php echo $loginErr; ?></span>
            </div>
            <p class="text-center">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
        </form>
    </div>
</body>
</html>
