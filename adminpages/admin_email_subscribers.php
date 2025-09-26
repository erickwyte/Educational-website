<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php"); // Redirect to login page
    exit;
}

require '../config.php';

// Fetch all subscribers
$result = $conn->query("SELECT * FROM email_subscribers ORDER BY subscribed_at DESC");
$subscriber_count = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Email Subscribers</title>
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

    .container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 20px;
      background-color: var(--white);
      border-radius: 8px;
      box-shadow: var(--shadow);
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid var(--primary-green);
    }

    h1 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      color: var(--primary-green);
      font-size: 28px;
    }

    .subscriber-count {
      background-color: var(--primary-green);
      color: var(--white);
      padding: 8px 16px;
      border-radius: 20px;
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      font-size: 16px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: var(--white);
      border-radius: 8px;
      overflow: hidden;
    }

    thead {
      background-color: var(--primary-green);
    }

    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }

    th {
      color: var(--white);
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      font-size: 16px;
    }

    tr:nth-child(even) {
      background-color: var(--light-gray);
    }

    tr:hover {
      background-color: rgba(0, 51, 0, 0.05);
    }

    .send-email-button {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background-color: var(--primary-green);
      color: var(--white);
      padding: 15px 25px;
      border-radius: 50px;
      text-decoration: none;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 8px;
      z-index: 100;
    }

    .send-email-button:hover {
      background-color: var(--primary-green-hover);
      transform: translateY(-3px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
      color: var(--white);
    }

    .send-email-button:active {
      transform: translateY(-1px);
    }

    .no-subscribers {
      text-align: center;
      padding: 40px;
      color: #666;
      font-style: italic;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
      .container {
        padding: 15px;
        margin: 15px;
      }
      
      table {
        display: block;
        overflow-x: auto;
      }
      
      th, td {
        padding: 12px;
      }
    }

    @media (max-width: 768px) {
      .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      h1 {
        font-size: 24px;
      }
      
      th, td {
        padding: 10px;
        display: block;
        width: 100%;
      }
      
      tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid var(--border-color);
        border-radius: 5px;
        padding: 10px;
      }
      
      thead {
        display: none;
      }
      
      td:before {
        content: attr(data-label);
        float: left;
        font-weight: bold;
        font-family: 'Poppins', sans-serif;
        color: var(--primary-green);
      }
      
      td {
        text-align: right;
        padding-left: 50%;
        position: relative;
      }
      
      td:before {
        position: absolute;
        left: 10px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        text-align: left;
      }
      
      .send-email-button {
        bottom: 20px;
        right: 20px;
        padding: 12px 20px;
        font-size: 14px;
      }
    }

    @media (max-width: 480px) {
      .container {
        padding: 10px;
        margin: 10px;
      }
      
      h1 {
        font-size: 20px;
      }
      
      .subscriber-count {
        padding: 6px 12px;
        font-size: 14px;
      }
      
      th, td {
        padding: 8px;
      }
      
      .send-email-button {
        bottom: 15px;
        right: 15px;
        padding: 10px 16px;
        font-size: 13px;
      }
    }
  </style>
</head>
<body>
  <!-- Header Navigation -->
  <?php include 'adminheader.php'; ?>
  <div class="empty"></div>
  
  <div class="container">
    <div class="header">
      <h1>Email Subscribers</h1>
      <div class="subscriber-count">
        <?php echo $subscriber_count; ?> Subscribers
      </div>
    </div>
    
    <!-- Subscribers Table -->
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Email Address</th>
          <th>Subscribed At</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($subscriber_count > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td data-label="ID"><?php echo $row['id']; ?></td>
            <td data-label="Email"><?php echo htmlspecialchars($row['email']); ?></td>
            <td data-label="Subscribed At"><?php echo date('M j, Y g:i A', strtotime($row['subscribed_at'])); ?></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" class="no-subscribers">No subscribers found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <!-- Fixed Button to Send Email -->
  <a href="admin_send_email.php" class="send-email-button">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
      <polyline points="22,6 12,13 2,6"></polyline>
    </svg>
    Send Email to All
  </a>
  
  <?php $conn->close(); ?>
</body>
</html>