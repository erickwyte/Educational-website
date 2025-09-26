<?php
session_start();
require '../config.php'; // Database connection

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = ''; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;

    // Ensure email and password are not empty
    if (empty($email) || empty($password)) {
        $error = "Email and password are required!";
    } else {
        // Ensure database connection is established
        if (!$conn) {
            die("Database connection failed: " . mysqli_connect_error());
        }

        // Fetch admin data from admin_list using email
        $stmt = $conn->prepare("SELECT id, email, password FROM admin_list WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $db_email, $db_password);
                $stmt->fetch();

                // Verify the password
                if (password_verify($password, $db_password)) {
                    $_SESSION['admin_id'] = $id;
                    $_SESSION['admin_email'] = $db_email;
                    header("Location: admin_panel.php"); // Redirect to admin panel
                    exit;
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "Admin not found!";
            }
            $stmt->close();
        } else {
            die("SQL error: " . $conn->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login</title>
    <style>
        /* General Page Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Login Container */
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 350px;
        }

        /* Heading */
        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        /* Form Styling */
        form {
            display: flex;
            flex-direction: column;
        }

        /* Labels */
        label {
            font-weight: bold;
            text-align: left;
            display: block;
            margin: 10px 0 5px;
        }

        /* Input Fields */
        input[type="email"], 
        input[type="password"] {
            width: 94%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        /* Button */
        button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Error Message */
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Admin Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
