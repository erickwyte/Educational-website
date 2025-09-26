<?php
$host = 'localhost';
$db = 'edu_website';
$user = 'root';
$pass = '';

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide errors from users

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection and log errors instead of showing them
if (!$conn) {
    error_log("Database Connection Error: " . mysqli_connect_error(), 3, "error.log");
    die("An error occurred. Please try again later.");
}

// Enable strict error reporting for MySQL
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function track_activity($user_id, $activity_type, $activity_details = '', $ip_address = null, $user_agent = null) {
    global $conn;
    
    // Get IP address if not provided
    if ($ip_address === null) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    // Get user agent if not provided
    if ($user_agent === null) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    try {
        // Insert into activity log
        $stmt = $conn->prepare("INSERT INTO user_activity (user_id, activity_type, activity_details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $activity_type, $activity_details, $ip_address, $user_agent);
        
        return $stmt->execute();
    } catch (Exception $e) {
        // Log error but don't break the application
        error_log("Activity tracking failed: " . $e->getMessage());
        return false;
    }
}

// Common activity types
define('ACTIVITY_LOGIN', 'login');
define('ACTIVITY_LOGOUT', 'logout');
define('ACTIVITY_VIEW', 'view');
define('ACTIVITY_DOWNLOAD', 'download');
define('ACTIVITY_UPLOAD', 'upload');
define('ACTIVITY_COMMENT', 'comment');
define('ACTIVITY_LIKE', 'like');
define('ACTIVITY_SEARCH', 'search');
define('ACTIVITY_PROFILE_UPDATE', 'profile_update');
define('ACTIVITY_REGISTER', 'register');

// For guest activities
function track_guest_activity($activity_type, $activity_details = '') {
    track_activity(0, $activity_type, "Guest: " . $activity_details);
}

// Function to check and log login attempts
function check_login_attempts($ip_address, $max_attempts = 5, $lockout_time = 900) {
    global $conn;
    try {
        // Count attempts within lockout period
        $cutoff_time = date('Y-m-d H:i:s', time() - $lockout_time);
        $sql = "SELECT COUNT(*) as attempt_count FROM login_attempts WHERE ip_address = ? AND attempt_time > ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'ss', $ip_address, $cutoff_time);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $attempt_count = $row['attempt_count'];
        mysqli_stmt_close($stmt);
        return $attempt_count >= $max_attempts;
    } catch (Exception $e) {
        error_log("Check login attempts error: " . $e->getMessage(), 3, "error.log");
        return false;
    }
}

// Function to log a failed login attempt
function log_login_attempt($ip_address) {
    global $conn;
    try {
        $sql = "INSERT INTO login_attempts (ip_address) VALUES (?)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 's', $ip_address);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        error_log("Log login attempt error: " . $e->getMessage(), 3, "error.log");
    }
}

// Function to clear login attempts on successful login
function clear_login_attempts($ip_address) {
    global $conn;
    try {
        $sql = "DELETE FROM login_attempts WHERE ip_address = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 's', $ip_address);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        error_log("Clear login attempts error: " . $e->getMessage(), 3, "error.log");
    }
}
?>