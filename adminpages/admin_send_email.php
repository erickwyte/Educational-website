<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php"); // Redirect to login page
    exit;
}

require '../config.php'; // Database connection

// Handle email sending when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_email'])) {
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Set email headers
    $headers  = "From: dasaplus01@gmail.com\r\n";
    $headers .= "Reply-To: admin@yourwebsite.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Fetch all subscriber emails
    $result = $conn->query("SELECT * FROM email_subscribers");
    $subscriber_count = $result->num_rows;
    $emails = [];
    while ($row = $result->fetch_assoc()) {
        $emails[] = $row['email'];
    }
    
    if ($subscriber_count > 0) {
        $to = implode(",", $emails); // Create comma-separated email list

        // Send email and provide feedback
        if (mail($to, $subject, nl2br($message), $headers)) {
            $success_message = "Email sent successfully to {$subscriber_count} subscribers!";
        } else {
            $error_message = "Failed to send emails. Please check your server configuration.";
        }
    } else {
        $error_message = "No subscribers found to send emails to.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Send Email to Subscribers</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-green: #003300;
      --primary-green-hover: #004d00;
      --yellow: #FFD700;
      --white: #FFFFFF;
      --light-gray: #f5f5f5;
      --border-color: #e0e0e0;
      --shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Roboto', sans-serif;
      background-color: var(--light-gray);
      color: #333;
      line-height: 1.6;
    }

    .empty {
      height: 20px;
    }

    .email-container {
      max-width: 800px;
      margin: 20px auto;
      padding: 30px;
      background-color: var(--white);
      border-radius: 8px;
      box-shadow: var(--shadow);
    }

    h2 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      color: var(--primary-green);
      margin-bottom: 25px;
      text-align: center;
      font-size: 28px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--primary-green);
    }

    .alert {
      padding: 12px 15px;
      margin-bottom: 20px;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    label {
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      color: var(--primary-green);
      font-size: 16px;
    }

    input[type="text"], textarea {
      padding: 12px 15px;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-family: 'Roboto', sans-serif;
      font-size: 16px;
      transition: var(--transition);
    }

    input[type="text"]:focus, textarea:focus {
      outline: none;
      border-color: var(--primary-green);
      box-shadow: 0 0 0 2px rgba(0, 51, 0, 0.2);
    }

    textarea {
      resize: vertical;
      min-height: 200px;
    }

    button {
      background-color: var(--primary-green);
      color: var(--white);
      border: none;
      padding: 12px 20px;
      border-radius: 4px;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      font-size: 16px;
      transition: var(--transition);
      margin-top: 10px;
    }

    button:hover {
      background-color: var(--primary-green-hover);
      transform: translateY(-2px);
    }

    .back-button {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background-color: var(--yellow);
      color: #000;
      text-decoration: none;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      transition: var(--transition);
      text-align: center;
    }

    .back-button:hover {
      background-color: #e6c300;
      transform: translateY(-2px);
      color: #000;
    }

    .subscriber-info {
      background-color: var(--light-gray);
      padding: 15px;
      border-radius: 4px;
      margin-bottom: 20px;
      font-family: 'Poppins', sans-serif;
    }

    .subscriber-count {
      font-weight: 600;
      color: var(--primary-green);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .email-container {
        padding: 20px;
        margin: 15px;
      }
      
      h2 {
        font-size: 24px;
      }
      
      input[type="text"], textarea {
        padding: 10px;
        font-size: 14px;
      }
      
      button {
        padding: 10px 16px;
        font-size: 14px;
      }
    }

    @media (max-width: 480px) {
      .email-container {
        padding: 15px;
        margin: 10px;
      }
      
      h2 {
        font-size: 20px;
      }
      
      .alert {
        padding: 10px;
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <!-- Header Navigation -->
  <?php include 'adminheader.php'; ?>
  <div class="empty"></div>

  <!-- Email Sending Container -->
  <div class="email-container">
    <h2>Send Email to All Subscribers</h2>
    
    <?php 
    // Get subscriber count for display
    $count_result = $conn->query("SELECT COUNT(*) as count FROM email_subscribers");
    $subscriber_count = $count_result->fetch_assoc()['count'];
    ?>
    
    <div class="subscriber-info">
      You are sending this email to <span class="subscriber-count"><?php echo $subscriber_count; ?></span> subscribers.
    </div>
    
    <?php if (isset($success_message)): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
      <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="admin_send_email.php">
      <div class="form-group">
        <label for="subject">Subject:</label>
        <input type="text" name="subject" id="subject" required placeholder="Enter email subject">
      </div>
      
      <div class="form-group">
        <label for="message">Message:</label>
        <textarea name="message" id="message" required placeholder="Write your email message here..."></textarea>
      </div>
      
      <button type="submit" name="send_email">Send Email to All Subscribers</button>
    </form>
    
    <a href="admin_email_subscribers.php" class="back-button">← Back to Subscribers List</a>
  </div>
</body>
</html>