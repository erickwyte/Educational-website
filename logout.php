
<?php
session_start();
require 'config.php'; // Database connection
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Track logout activity
    track_activity($user_id, ACTIVITY_LOGOUT, "User logged out");
}
session_destroy();
header("Location: login.php");
exit;
?>
