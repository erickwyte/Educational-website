<?php
require 'config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token in the database
    $query = "UPDATE users SET verified = 1 WHERE token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();

    echo "Your email has been verified! You can now log in.";
}
?>
