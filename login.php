<?php
// Include session configuration
if (!file_exists('session_config.php')) {
    error_log("Session config file not found: session_config.php", 3, "error.log");
    http_response_code(500);
    die("Session configuration file missing. Please contact the administrator.");
}
require_once 'session_config.php';

// Include config file
if (!file_exists('config.php')) {
    error_log("Config file not found: config.php", 3, "error.log");
    http_response_code(500);
    die("Configuration file missing. Please contact the administrator.");
}
require_once 'config.php';

// Start session
if (!session_start()) {
    error_log("Failed to start session", 3, "error.log");
    http_response_code(500);
    die("Session initialization failed. Please try again later.");
}

// Initialize variables
$email = '';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

// Rate limiting
$max_attempts = 5;
$lockout_time = 900; // 15 minutes in seconds

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        error_log("CSRF token generation failed: " . $e->getMessage(), 3, "error.log");
        http_response_code(500);
        die("Security token generation failed. Please try again later.");
    }
}

if (isset($_POST['login'])) {
    // Check rate limiting
    if (check_login_attempts($ip_address, $max_attempts, $lockout_time)) {
        $_SESSION['error'] = "Too many login attempts. Please try again later.";
        track_guest_activity(ACTIVITY_LOGIN, "Failed: Too many attempts");
        header("Location: login.php");
        exit();
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        log_login_attempt($ip_address);
        $_SESSION['error'] = "Security token validation failed.";
        track_guest_activity(ACTIVITY_LOGIN, "Failed: Invalid CSRF token");
        header("Location: login.php");
        exit();
    }

    // Sanitize and trim input data
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        log_login_attempt($ip_address);
        $_SESSION['error'] = "Invalid email format!";
        track_guest_activity(ACTIVITY_LOGIN, "Failed: Invalid email");
        header('Location: login.php');
        exit();
    }

    // Validate password
    if (empty($password)) {
        log_login_attempt($ip_address);
        $_SESSION['error'] = "Password cannot be empty!";
        track_guest_activity(ACTIVITY_LOGIN, "Failed: Empty password");
        header('Location: login.php');
        exit();
    }

    // Fetch user from database
    try {
        $sql = "SELECT id, username, email, password, user_type FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // Check if user exists
        if (!$user) {
            log_login_attempt($ip_address);
            $_SESSION['error'] = "No account found with this email!";
            track_guest_activity(ACTIVITY_LOGIN, "Failed: Email not found");
            header('Location: login.php');
            exit();
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            log_login_attempt($ip_address);
            $_SESSION['error'] = "Invalid password!";
            $_SESSION['show_forgot_password'] = true; // Flag for forgot password link
            track_guest_activity(ACTIVITY_LOGIN, "Failed: Invalid password");
            header('Location: login.php');
            exit();
        }

        // Regenerate session ID to prevent session fixation
        if (!session_regenerate_id(true)) {
            error_log("Session regeneration failed", 3, "error.log");
            $_SESSION['error'] = "An error occurred. Please try again later.";
            track_guest_activity(ACTIVITY_LOGIN, "Failed: Session regeneration error");
            header('Location: login.php');
            exit();
        }

        // Clear login attempts on successful login
        clear_login_attempts($ip_address);

        // Store user information in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['username'];

        // Track successful login
        track_activity($user['id'], ACTIVITY_LOGIN, "User logged in successfully");

        // Regenerate CSRF token
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            error_log("CSRF token regeneration failed: " . $e->getMessage(), 3, "error.log");
        }

        // Redirect based on user type
        if ($user['user_type'] === 'admin') {
            header('Location: adminpages/admin_panel.php');
        } else {
            header('Location: index.php');
        }
        exit();
    } catch (Exception $e) {
        log_login_attempt($ip_address);
        error_log("Login error: " . $e->getMessage(), 3, "error.log");
        $_SESSION['error'] = "An error occurred. Please try again later.";
        track_guest_activity(ACTIVITY_LOGIN, "Failed: Database error");
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dasaplus</title>
    <style>
        :root {
            --primary-color: #003300;
            --secondary-color: #006600;
            --accent-color: #00cc00;
            --light-color: #f5f5f5;
            --error-color: #ff3333;
            --success-color: #00aa00;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f8f0;
            color: #333;
            line-height: 1.6;
        }
        
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        header h2 a {
            color: white;
            text-decoration: none;
            cursor: pointer;
        }
        
        .form-container {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .form-box h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .input-group {
            margin-bottom: 1.5rem;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #444;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .input-group input:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 204, 0, 0.2);
        }
        
        .input-password {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
            overflow: hidden;
        }
        
        .input-password input {
            flex: 1;
            border: none;
            outline: none;
        }
        
        .input-password img {
            padding: 0 10px;
            cursor: pointer;
            height: 40px;
            width: 60px;
        }
        
        .form-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .form-btn:hover {
            background-color: var(--secondary-color);
        }
        
        .form-text {
            text-align: center;
            margin-top: 1rem;
        }
        
        .form-text a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .form-text a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .alert-error {
            background-color: #ffe6e6;
            color: var(--error-color);
            border: 1px solid #ffcccc;
        }
        
        .alert-success {
            background-color: #e6ffe6;
            color: var(--success-color);
            border: 1px solid #ccffcc;
        }
        
        @media (max-width: 600px) {
            .form-container {
                margin: 1rem auto;
                padding: 1.5rem;
            }
            
            .form-box h2 {
                font-size: 1.5rem;
            }
            
            .input-group input {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
<header>
    <h2><a href="login.php">Dasaplus</a></h2>
</header>

<div class="form-container">
    <form action="login.php" method="post" class="form-box" onsubmit="return validateForm()">
        <h2>Log In</h2>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</div>';
            if (isset($_SESSION['show_forgot_password']) && $_SESSION['show_forgot_password']) {
                echo '<div class="form-text"><a href="forgot_password.php">Forgot Password?</a></div>';
                unset($_SESSION['show_forgot_password']);
            }
            unset($_SESSION['error']);
        }
        ?>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="e.g. elonmusk@gmail.com" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required onblur="validateEmail()">
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <div class="input-password">
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                <img src="images&icons/eye-closed.png" alt="Show/Hide password" id="eyeicon">
            </div>
        </div>
        <button type="submit" name="login" class="form-btn">Login</button>

        <p class="form-text">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </p>
    </form>
</div>

<script>
    // Toggle password visibility
    const eyeicon = document.getElementById("eyeicon");
    const password = document.getElementById("password");

    eyeicon.onclick = function() {
        if (password.type === "password") {
            password.type = "text";
            eyeicon.src = "images&icons/eye-open.png";
        } else {
            password.type = "password";
            eyeicon.src = "images&icons/eye-closed.png";
        }
    }

    // Validate email
    function validateEmail() {
        const email = document.getElementById("email").value;
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailPattern.test(email)) {
            alert("Please enter a valid email address.");
            return false;
        }
        return true;
    }

    // Validate form
    function validateForm() {
        let isValid = true;
        if (!validateEmail()) {
            isValid = false;
        }
        if (!document.getElementById("password").value) {
            alert("Password cannot be empty!");
            isValid = false;
        }
        return isValid;
    }
</script>
</body>
</html>