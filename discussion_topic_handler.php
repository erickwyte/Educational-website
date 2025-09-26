<?php
// discussion_topic_handler.php

session_start();
require 'config.php'; // Database connection

// Set security headers
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize messages
$success_msg = "";
$error_msg = "";

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_topic'])) {
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_msg = "Security token validation failed. Please try again.";
    } else {
        // Get and sanitize form data
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $user_id = $_SESSION['user_id'];
        
        // Validate inputs
        if (empty($title)) {
            $error_msg = "Please enter a title for your discussion.";
        } elseif (strlen($title) > 200) {
            $error_msg = "Title must be less than 200 characters.";
        } elseif (empty($content)) {
            $error_msg = "Please enter content for your discussion.";
        } elseif (strlen($content) > 5000) {
            $error_msg = "Content must be less than 5000 characters.";
        } else {
            // Check for duplicate topics (prevent spam)
            $check_sql = "SELECT id FROM discussion_topic WHERE user_id = ? AND title = ? AND date_posted > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param('is', $user_id, $title);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error_msg = "You've already posted a similar topic recently. Please wait before posting again.";
            } else {
                // Insert new topic into database
                $insert_sql = "INSERT INTO discussion_topic (user_id, title, content, date_posted) VALUES (?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                
                if ($insert_stmt) {
                    $insert_stmt->bind_param('iss', $user_id, $title, $content);
                    
                    if ($insert_stmt->execute()) {
                        $success_msg = "Your discussion topic has been posted successfully!";
                        // Clear form fields
                        $title = $content = '';
                        
                        // Regenerate CSRF token after successful submission
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } else {
                        $error_msg = "Error posting your topic. Please try again later.";
                        error_log("Database error: " . $insert_stmt->error);
                    }
                    
                    $insert_stmt->close();
                } else {
                    $error_msg = "Database error. Please try again later.";
                    error_log("Prepare error: " . $conn->error);
                }
            }
            
            $check_stmt->close();
        }
    }
}

// Redirect back to forum page with messages
$_SESSION['form_messages'] = [
    'success' => $success_msg,
    'error' => $error_msg,
    'form_data' => [
        'title' => $title ?? '',
        'content' => $content ?? ''
    ]
];

header("Location: discussion_forum.php");
exit;
?>