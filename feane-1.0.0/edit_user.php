<?php
// Chuyển hướng từ edit_user.php sang edit_account.php
if(isset($_GET['id'])) {
    $id = $_GET['id'];
    header("Location: edit_account.php?id=" . $id);
} else {
    header("Location: manage_users.php");
}
exit;
?> 