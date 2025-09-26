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
$username = $phone_number = $course = $email = '';
$user_type_db = '';

// Rate limiting
$max_attempts = 5;
$lockout_time = 900; // 15 minutes in seconds
if (!isset($_SESSION['signup_attempts'])) {
    $_SESSION['signup_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

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

if (isset($_POST['signup'])) {
    // Check rate limiting
    if ($_SESSION['signup_attempts'] >= $max_attempts && 
        (time() - $_SESSION['last_attempt']) < $lockout_time) {
        $_SESSION['error'] = "Too many signup attempts. Please try again later.";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Too many attempts");
        header("Location: signup.php");
        exit();
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['signup_attempts']++;
        $_SESSION['last_attempt'] = time();
        $_SESSION['error'] = "Security token validation failed.";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Invalid CSRF token");
        header("Location: signup.php");
        exit();
    }
    
    // Sanitize and trim input data
    $username = trim(htmlspecialchars($_POST['username'] ?? ''));
    $phone_number = trim(htmlspecialchars($_POST['phone_number'] ?? ''));
    $course = trim(htmlspecialchars($_POST['course'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';

    // Validate username
    if (strlen($username) < 3 || strlen($username) > 50) {
        $_SESSION['signup_attempts']++;
        $_SESSION['last_attempt'] = time();
        $_SESSION['error'] = "Username must be between 3 and 50 characters.";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Invalid username length");
        header("Location: signup.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_attempts']++;
        $_SESSION['last_attempt'] = time();
        $_SESSION['error'] = "Invalid email format!";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Invalid email");
        header("Location: signup.php");
        exit();
    }

    // Validate phone number
    if (!preg_match('/^\+?[0-9]{10,15}$/', $phone_number)) {
        $_SESSION['signup_attempts']++;
        $_SESSION['last_attempt'] = time();
        $_SESSION['error'] = "Invalid phone number! Must be 10-15 digits, optionally starting with +.";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Invalid phone number");
        header("Location: signup.php");
        exit();
    }

    // Stronger password validation
    if (strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[\W]/', $password)) {
        $_SESSION['signup_attempts']++;
        $_SESSION['last_attempt'] = time();
        $_SESSION['error'] = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Weak password");
        header("Location: signup.php");
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['signup_attempts']++;
        $_SESSION['last_attempt'] = time();
        $_SESSION['error'] = "Passwords do not match!";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Passwords do not match");
        header("Location: signup.php");
        exit();
    }

    // Check if email, phone number, or username already exists
    try {
        $check_sql = "SELECT id FROM users WHERE email = ? OR phone_number = ? OR username = ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'sss', $email, $phone_number, $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION['signup_attempts']++;
            $_SESSION['last_attempt'] = time();
            $_SESSION['error'] = "An account with these details already exists.";
            track_guest_activity(ACTIVITY_REGISTER, "Failed: Duplicate email, phone, or username");
            header("Location: signup.php");
            mysqli_stmt_close($stmt);
            exit();
        }
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        error_log("Database check error: " . $e->getMessage(), 3, "error.log");
        $_SESSION['error'] = "An error occurred. Please try again later.";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Database error during check");
        header("Location: signup.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    if ($hashed_password === false) {
        error_log("Password hashing failed", 3, "error.log");
        $_SESSION['error'] = "An error occurred during registration. Please try again.";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Password hashing error");
        header("Location: signup.php");
        exit();
    }
    $user_type = 'user'; // Default user type

    // Check if the email exists in the admin_list table
    try {
        $admin_check_sql = "SELECT user_type FROM admin_list WHERE email = ?";
        $stmt = mysqli_prepare($conn, $admin_check_sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $user_type_db);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($user_type_db === 'admin') {
            $user_type = 'admin';
            
            // Update the password in admin_list
            $update_admin_sql = "UPDATE admin_list SET password = ? WHERE email = ?";
            $stmt = mysqli_prepare($conn, $update_admin_sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, 'ss', $hashed_password, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    } catch (Exception $e) {
        error_log("Admin check/update error: " . $e->getMessage(), 3, "error.log");
        $_SESSION['error'] = "An error occurred. Please try again later.";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Admin check error");
        header("Location: signup.php");
        exit();
    }

    // Set subscription end date
    $subscription_end = date('Y-m-d', strtotime('+3 months'));

    // Insert user data into the database
    try {
        $sql = "INSERT INTO users (username, course, phone_number, email, password, user_type, subscription_end) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'sssssss', $username, $course, $phone_number, $email, $hashed_password, $user_type, $subscription_end);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }

        // Get the last inserted user ID
        $user_id = mysqli_insert_id($conn);
        $_SESSION['user_id'] = $user_id;
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        error_log("Database insert error: " . $e->getMessage(), 3, "error.log");
        $_SESSION['error'] = "An error occurred during registration. Please try again.";
        track_guest_activity(ACTIVITY_REGISTER, "Failed: Database insert error");
        header("Location: signup.php");
        exit();
    }

    // Reset signup attempts
    $_SESSION['signup_attempts'] = 0;
    $_SESSION['last_attempt'] = time();

    // Track successful registration
    track_activity($user_id, ACTIVITY_REGISTER, "User registered successfully");

    // Regenerate CSRF token
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        error_log("CSRF token regeneration failed: " . $e->getMessage(), 3, "error.log");
    }
    
    // Store user info in session
    $_SESSION['email'] = $email;
    $_SESSION['username'] = $username;
    $_SESSION['user_type'] = $user_type;
    
    // Redirect to welcome page
    $_SESSION['signup_success'] = "Account created successfully!";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Dasaplus</title>
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
            max-width: 500px;
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
        
        .password-requirements {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .password-requirements ul {
            margin-left: 20px;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .requirement span {
            margin-left: 8px;
        }
        
        .requirement.met {
            color: var(--success-color);
        }
        
        .requirement.unmet {
            color: #888;
        }
        
        .custom-dropdown {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .dropdown-container {
            position: relative;
        }
        
        #course-search {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .dropdown-list {
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
            display: none;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .dropdown-item {
            padding: 10px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .dropdown-item:hover {
            background: #f0f0f0;
        }
        
        .terms-container {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .terms-checkbox {
            display: flex;
            align-items: center;
            margin-right: 10px;
        }
        
        .terms-label {
            font-size: 0.9rem;
        }
        
        .terms-label a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .terms-label a:hover {
            text-decoration: underline;
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
            
            .password-requirements {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
<header>
    <h2><a href="signup.php">Dasaplus</a></h2>
</header>

<div class="form-container">
    <form action="signup.php" method="POST" class="form-box" onsubmit="return validateForm()">
        <h2>Create Your Account</h2>
        
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['signup_success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['signup_success']) . '</div>';
            unset($_SESSION['signup_success']);
        }
        ?>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

        <div class="input-group">
            <label for="username">Full Name</label>
            <input type="text" name="username" id="username" placeholder="Enter your full name" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>

        <div class="custom-dropdown">
            <label for="course">Course</label>
            <div class="dropdown-container">
                <input type="text" id="course-search" placeholder="Search for your course..." onkeyup="filterCourses()" onclick="toggleDropdown()">
                <div class="dropdown-list" id="dropdown-list">
                    <!-- Courses will be inserted here dynamically -->
                </div>
            </div>
            <input type="hidden" name="course" id="selected-course" required>
        </div>

        <div class="input-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="your.email@example.com" value="<?php echo htmlspecialchars($email); ?>" required onblur="validateEmail()">
        </div>

        <div class="input-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" placeholder="+1234567890 or 0712345678" value="<?php echo htmlspecialchars($phone_number); ?>" required onblur="validatePhoneNumber()">
            <small style="color: #666; font-size: 0.8rem;">Enter your phone number with country code (e.g., +1 for US)</small>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <div class="input-password">
                <input type="password" name="password" placeholder="Create a strong password" id="password" onkeyup="checkPasswordStrength()">
                <img src="images&icons/eye-closed.png" alt="Show/Hide password" id="eyeicon1">
            </div>
            <div class="password-requirements">
                <p>Password must meet the following requirements:</p>
                <div class="requirement" id="length-req">
                    <span>✓</span>
                    <span>At least 8 characters long</span>
                </div>
                <div class="requirement" id="uppercase-req">
                    <span>✓</span>
                    <span>Contains at least one uppercase letter</span>
                </div>
                <div class="requirement" id="lowercase-req">
                    <span>✓</span>
                    <span>Contains at least one lowercase letter</span>
                </div>
                <div class="requirement" id="number-req">
                    <span>✓</span>
                    <span>Contains at least one number</span>
                </div>
                <div class="requirement" id="special-req">
                    <span>✓</span>
                    <span>Contains at least one special character</span>
                </div>
            </div>
        </div>

        <div class="input-group">
            <label for="confirm-password">Confirm Password</label>
            <div class="input-password">
                <input type="password" name="confirm-password" placeholder="Re-enter your password" id="confirm-password">
                <img src="images&icons/eye-closed.png" alt="Show/Hide password" id="eyeicon2">
            </div>
            <div id="password-match" style="margin-top: 5px;"></div>
        </div>

        <div class="terms-container">
            <label class="terms-checkbox">
                <input type="checkbox" required>
                <span class="checkmark"></span>
            </label>
            <div class="terms-label">
                I agree to the <a href="terms&conditions.php">Terms & Conditions</a>
            </div>
        </div>

        <button type="submit" name="signup" class="form-btn">Create Account</button>

        <p class="form-text">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

<script>
    // Toggle password visibility
    const eyeicon1 = document.getElementById("eyeicon1");
    const eyeicon2 = document.getElementById("eyeicon2");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm-password");

    eyeicon1.onclick = function() {
        if (password.type === "password") {
            password.type = "text";
            eyeicon1.src = "images&icons/eye-open.png";
        } else {
            password.type = "password";
            eyeicon1.src = "images&icons/eye-closed.png";
        }
    }

    eyeicon2.onclick = function() {
        if (confirmPassword.type === "password") {
            confirmPassword.type = "text";
            eyeicon2.src = "images&icons/eye-open.png";
        } else {
            confirmPassword.type = "password";
            eyeicon2.src = "images&icons/eye-closed.png";
        }
    }

    // Password strength indicator
    function checkPasswordStrength() {
        const passwordValue = password.value;
        const lengthReq = document.getElementById("length-req");
        const uppercaseReq = document.getElementById("uppercase-req");
        const lowercaseReq = document.getElementById("lowercase-req");
        const numberReq = document.getElementById("number-req");
        const specialReq = document.getElementById("special-req");
        
        lengthReq.classList.toggle("met", passwordValue.length >= 8);
        lengthReq.classList.toggle("unmet", passwordValue.length < 8);
        uppercaseReq.classList.toggle("met", /[A-Z]/.test(passwordValue));
        uppercaseReq.classList.toggle("unmet", !/[A-Z]/.test(passwordValue));
        lowercaseReq.classList.toggle("met", /[a-z]/.test(passwordValue));
        lowercaseReq.classList.toggle("unmet", !/[a-z]/.test(passwordValue));
        numberReq.classList.toggle("met", /[0-9]/.test(passwordValue));
        numberReq.classList.toggle("unmet", !/[0-9]/.test(passwordValue));
        specialReq.classList.toggle("met", /[\W]/.test(passwordValue));
        specialReq.classList.toggle("unmet", !/[\W]/.test(passwordValue));
        
        if (confirmPassword.value) {
            validatePasswords();
        }
    }

    // Validate password confirmation
    function validatePasswords() {
        const matchElement = document.getElementById("password-match");
        if (password.value !== confirmPassword.value) {
            matchElement.innerHTML = "<span style='color: var(--error-color);'>Passwords do not match!</span>";
            return false;
        } else {
            matchElement.innerHTML = "<span style='color: var(--success-color);'>Passwords match!</span>";
            return true;
        }
    }

    // Validate phone number
    function validatePhoneNumber() {
        const phoneInput = document.getElementById("phone_number").value;
        const phonePattern = /^\+?[0-9]{10,15}$/;
        if (!phonePattern.test(phoneInput)) {
            alert("Invalid phone number! Must be 10-15 digits, optionally starting with +.");
            return false;
        }
        return true;
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

    // Validate username
    function validateUsername() {
        const username = document.getElementById("username").value;
        if (username.length < 3 || username.length > 50) {
            alert("Username must be between 3 and 50 characters.");
            return false;
        }
        return true;
    }

    // Form validation
    function validateForm() {
        let isValid = true;
        if (!validateEmail()) isValid = false;
        if (!validatePhoneNumber()) isValid = false;
        if (!validateUsername()) isValid = false;
        if (!validatePasswords()) isValid = false;
        
        const unmetReqs = document.querySelectorAll(".requirement.unmet");
        if (unmetReqs.length > 0) {
            alert("Please ensure your password meets all requirements.");
            isValid = false;
        }
        
        return isValid;
    }

    // Event listeners
    confirmPassword.addEventListener("input", validatePasswords);
    password.addEventListener("input", checkPasswordStrength);

    // Course dropdown functionality
    document.addEventListener("DOMContentLoaded", function () {
        fetch('fetch_courses.php')
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch courses');
                return response.json();
            })
            .then(courses => {
                const dropdownList = document.getElementById('dropdown-list');
                courses.sort().forEach(course => {
                    const div = document.createElement('div');
                    div.classList.add('dropdown-item');
                    div.textContent = course;
                    div.onclick = function () {
                        document.getElementById('course-search').value = course;
                        document.getElementById('selected-course').value = course;
                        dropdownList.style.display = "none";
                    };
                    dropdownList.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Error loading courses:', error);
                alert('Failed to load courses. Please try again later.');
            });
    });

    function filterCourses() {
        const input = document.getElementById('course-search').value.toLowerCase();
        const dropdownList = document.getElementById('dropdown-list');
        const items = dropdownList.getElementsByClassName('dropdown-item');
        
        for (let item of items) {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(input) ? '' : 'none';
        }
        
        if (input.length > 0) {
            dropdownList.style.display = "block";
        }
    }

    function toggleDropdown() {
        const dropdownList = document.getElementById('dropdown-list');
        dropdownList.style.display = dropdownList.style.display === "block" ? "none" : "block";
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('dropdown-list');
        const search = document.getElementById('course-search');
        
        if (event.target !== search && !search.contains(event.target) && 
            event.target !== dropdown && !dropdown.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
</script>
</body>
</html>