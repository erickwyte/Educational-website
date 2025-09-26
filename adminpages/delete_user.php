<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php");
    exit;
}

require '../config.php';

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Optional: Add confirmation step and backup mechanism
    $query = "DELETE FROM users WHERE id = $user_id";
    
    if ($conn->query($query)) {
        header("Location: user_management.php?message=User deleted successfully");
    } else {
        header("Location: user_management.php?error=Error deleting user");
    }
} else {
    header("Location: user_management.php?error=User ID required");
}
exit;
?>