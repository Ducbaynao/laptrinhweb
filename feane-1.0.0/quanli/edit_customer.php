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
    
    // Get hidden input value
    $id = $_POST["id"];
    
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
    
    // Check input errors before updating the database
    if(empty($name_err) && empty($phone_err) && empty($email_err) && empty($gender_err) && empty($people_count_err)){
        
        // Prepare an update statement
        $sql = "UPDATE customers SET name = ?, phone = ?, email = ?, birthdate = ?, gender = ?, people_count = ? WHERE id = ?";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssii", $param_name, $param_phone, $param_email, $param_birthdate, $param_gender, $param_people_count, $param_id);
            
            // Set parameters
            $param_name = $name;
            $param_phone = $phone;
            $param_email = $email;
            $param_birthdate = $birthdate ? $birthdate : NULL;
            $param_gender = $gender;
            $param_people_count = $people_count;
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records updated successfully. Redirect to dashboard page
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
} else{
    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM customers WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            
            // Set parameters
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Retrieve individual field value
                    $name = $row["name"];
                    $phone = $row["phone"];
                    $email = $row["email"];
                    $birthdate = $row["birthdate"];
                    $gender = $row["gender"];
                    $people_count = $row["people_count"];
                } else{
                    // URL doesn't contain valid id parameter. Redirect to error page
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Đã xảy ra lỗi. Vui lòng thử lại sau.";
            }
        
            // Close statement
            mysqli_stmt_close($stmt);
        }
        
        // Close connection
        mysqli_close($conn);
    } else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}
?> 