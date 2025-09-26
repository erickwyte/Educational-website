<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/forgot_password.css">
    <title>Forgot Password - Dasaplus</title>
    <style>
      
    </style>
</head>
<body>
    <header>
        <h2><a href="index.php">Dasaplus</a></h2>
    </header>

    <div class="form-container">
        <h2>Forgot Password</h2>
        
        <?php
        include 'config.php';

        if (isset($_POST['submit'])) {
            $email = trim($_POST['email']);

            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 0) {
                echo '<div class="message error">Error: Email not found!</div>';
            } else {
                // Generate a unique token
                $token = bin2hex(random_bytes(50));
                $expires = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

                // Store the token in the database
                $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
                $stmt->bind_param("sss", $token, $expires, $email);
                
                if ($stmt->execute()) {
                    // Send reset email (in a real application, you would implement email sending)
                    $reset_link = "http://yourwebsite.com/reset_password.php?token=$token";
                    // mail($email, "Password Reset", "Click this link to reset your password: $reset_link");
                    
                    echo '<div class="message success">A password reset link has been sent to your email!</div>';
                } else {
                    echo '<div class="message error">Error: Could not process your request. Please try again.</div>';
                }
                
                $stmt->close();
            }
        }
        ?>

        <form action="forgot_password.php" method="POST">
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" placeholder="Enter your email address" required>
            </div>

            <button type="submit" name="submit" class="form-btn">Send Reset Link</button>
        </form>
        
        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>