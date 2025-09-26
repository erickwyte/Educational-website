<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php"); // Redirect to login page
    exit;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 50px auto;
            text-align: center;
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            color: #333;
        }
        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }
        .nav-links a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: white;
            background: #007BFF;
            border-radius: 5px;
            font-size: 18px;
            transition: 0.3s;
        }
        .nav-links a:hover {
            background: #0056b3;
        }
        @media (max-width: 600px) {
            .container {
                width: 95%;
            }
            .nav-links a {
                font-size: 16px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <?php include 'adminheader.php'; ?>
    <div class="container">
        <h2>Content Management</h2>
        <div class="nav-links">
            <a href="manage_notes.php">Manage notes</a>
            <a href="manage_questions.php">mange Questions</a>
            <a href="manage_blog_posts.php">manage blogs</a>
            <a href="admin_testimonials.php">User's Testimonials</a>            
        <a href="users_pdfs_uploads.php">User's Notes upload</a>
        <a href="admin_messages.php">User's Messages</a>
        <a href="admin_email_subscribers.php">Email-subcribers</a>
        </div>
    </div>
</body>
</html>
