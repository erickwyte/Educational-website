<?php
session_start();
include 'config.php';

// Check if token is provided
$token = isset($_GET['token']) ? htmlspecialchars($_GET['token']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    if ($password !== $confirm_password) {
        die("Error: Passwords do not match!");
    }

    // Hash the new password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Update the database
    $update_sql = "UPDATE users SET password = ? WHERE reset_token = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('ss', $hashed_password, $token);

    if ($stmt->execute()) {
        echo "Password reset successful. <a href='login.php'>Login here</a>";
    } else {
        echo "Error updating password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/reset_password.css">
</head>
<body>
    <form action="reset_password.php?token=<?= $token; ?>" method="POST">
        <h2>Reset Password</h2>
        <input type="password" name="password" placeholder="New password" required>
        <input type="password" name="confirm-password" placeholder="Confirm password" required>
        <button type="submit" name="reset">Reset Password</button>
    </form>
</body>
</html>
