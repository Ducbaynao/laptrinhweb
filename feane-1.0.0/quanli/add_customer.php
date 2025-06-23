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
 
// Define variables and initialize with empty values
$name = $phone = $email = $birthdate = $gender = $people_count = "";
$name_err = $phone_err = $email_err = $birthdate_err = $gender_err = $people_count_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate name
    if(empty(trim($_POST["name"]))){
        $name_err = "Vui lòng nhập tên khách hàng.";
    } else{
        $name = trim($_POST["name"]);
    }
    
    // Validate phone
    if(empty(trim($_POST["phone"]))){
        $phone_err = "Vui lòng nhập số điện thoại.";
    } else{
        $phone = trim($_POST["phone"]);
    }
    
    // Validate email (optional)
    if(!empty(trim($_POST["email"]))){
        if(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
            $email_err = "Email không hợp lệ.";
        } else{
            $email = trim($_POST["email"]);
        }
    }
    
    // Process birthdate (optional)
    if(!empty(trim($_POST["birthdate"]))){
        $birthdate = trim($_POST["birthdate"]);
    }
    
    // Validate gender
    if(empty(trim($_POST["gender"]))){
        $gender_err = "Vui lòng chọn giới tính.";
    } else{
        $gender = trim($_POST["gender"]);
    }
    
    // Validate people count
    if(empty(trim($_POST["people_count"]))){
        $people_count_err = "Vui lòng nhập số lượng người.";
    } elseif(!is_numeric(trim($_POST["people_count"])) || intval(trim($_POST["people_count"])) < 1){
        $people_count_err = "Số lượng người phải là số nguyên dương.";
    } else{
        $people_count = intval(trim($_POST["people_count"]));
    }
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($phone_err) && empty($email_err) && empty($gender_err) && empty($people_count_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO customers (name, phone, email, birthdate, gender, people_count) VALUES (?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssi", $param_name, $param_phone, $param_email, $param_birthdate, $param_gender, $param_people_count);
            
            // Set parameters
            $param_name = $name;
            $param_phone = $phone;
            $param_email = $email;
            $param_birthdate = $birthdate ? $birthdate : NULL;
            $param_gender = $gender;
            $param_people_count = $people_count;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records created successfully. Redirect to dashboard page
                header("location: dashboard.php");
                exit();
            } else{
                echo "Đã xảy ra lỗi. Vui lòng thử lại sau.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?> 