<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Prepare a statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a new session
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['birthday'] = $user['birthday'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['description'] = $user['description'];
            $_SESSION['job_position'] = $user['job_position'];
              // Redirect based on username
            if ($username === 'admin') {
                // Admin goes to profile page
                header("Location: admin_profile.php");
            } else {
                // Other users go to their profile page
                header("Location: profile.php");
            }
            exit;
        } else {
            $login_error = "Mật khẩu không đúng";
        }
    } else {
        $login_error = "Tài khoản không tồn tại";
    }
    
    $stmt->close();
}

$conn->close();
?> 