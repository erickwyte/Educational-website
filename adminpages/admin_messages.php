<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php"); // Redirect to login page
    exit;
}

require '../config.php'; // Database connection

// Handle admin reply
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply'])) {
    $id = $_POST['message_id'];
    $reply = $_POST['reply'];

    // Update database with admin reply
    $sql = "UPDATE messages SET reply=?, status='replied' WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $reply, $id);

    if ($stmt->execute()) {
        // Fetch user email to send reply
        $sql = "SELECT email FROM messages WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($email);
        $stmt->fetch();
        $stmt->close();

        // Send email to the user
        $subject = "Reply to Your Message";
        $headers = "From: dasaplus01@gmail.com\r\nReply-To: admin@yourwebsite.com";
        $body = "Dear User,\n\nThank you for contacting us. Here is our response:\n\n$reply\n\nBest regards,\nYour Support Team";

        mail($email, $subject, $body, $headers);

        $success_message = "Reply sent successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}

// Fetch messages
$sql = "SELECT * FROM messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Messages</title>
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
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        h1 {
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

        .message-content {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .message-content.expanded {
            white-space: normal;
            max-width: none;
        }

        .toggle-message {
            color: var(--primary-green);
            cursor: pointer;
            font-size: 12px;
            margin-top: 5px;
            display: inline-block;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-replied {
            background-color: #d4edda;
            color: #155724;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
        }

        textarea:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 2px rgba(0, 51, 0, 0.2);
        }

        button {
            background-color: var(--primary-green);
            color: var(--white);
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: var(--transition);
        }

        button:hover {
            background-color: var(--primary-green-hover);
            transform: translateY(-2px);
        }

        .action-completed {
            color: var(--primary-green);
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
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
            
            h1 {
                font-size: 24px;
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
            
            .message-content {
                max-width: 100%;
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
            
            th, td {
                padding: 8px;
            }
            
            button {
                padding: 6px 12px;
                font-size: 14px;
                width: 100%;
            }
            
            textarea {
                padding: 8px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<?php include 'adminheader.php'; ?>
<div class="empty"></div>

<div class="container">
    <h1>User Messages</h1>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Reply</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td data-label="ID"><?php echo $row['id']; ?></td>
                <td data-label="User"><?php echo htmlspecialchars($row['name']); ?></td>
                <td data-label="Email"><?php echo htmlspecialchars($row['email']); ?></td>
                <td data-label="Subject"><?php echo htmlspecialchars($row['subject']); ?></td>
                <td data-label="Message">
                    <div class="message-content" id="message-<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['message']); ?>
                    </div>
                    <span class="toggle-message" onclick="toggleMessage(<?php echo $row['id']; ?>)">Show more</span>
                </td>
                <td data-label="Reply">
                    <?php if ($row['reply']): ?>
                        <div class="message-content" id="reply-<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['reply']); ?>
                        </div>
                        <span class="toggle-message" onclick="toggleReply(<?php echo $row['id']; ?>)">Show more</span>
                    <?php else: ?>
                        No reply yet
                    <?php endif; ?>
                </td>
                <td data-label="Status">
                    <span class="status-badge status-<?php echo $row['status']; ?>">
                        <?php echo ucfirst($row['status']); ?>
                    </span>
                </td>
                <td data-label="Action">
                    <?php if ($row['status'] == 'pending'): ?>
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                            <textarea name="reply" placeholder="Write your reply here..." required></textarea>
                            <button type="submit">Send Reply</button>
                        </form>
                    <?php else: ?>
                        <p class="action-completed">Replied</p>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    function toggleMessage(id) {
        const messageElement = document.getElementById('message-' + id);
        const toggleButton = messageElement.nextElementSibling;
        
        if (messageElement.classList.contains('expanded')) {
            messageElement.classList.remove('expanded');
            toggleButton.textContent = 'Show more';
        } else {
            messageElement.classList.add('expanded');
            toggleButton.textContent = 'Show less';
        }
    }
    
    function toggleReply(id) {
        const replyElement = document.getElementById('reply-' + id);
        const toggleButton = replyElement.nextElementSibling;
        
        if (replyElement.classList.contains('expanded')) {
            replyElement.classList.remove('expanded');
            toggleButton.textContent = 'Show more';
        } else {
            replyElement.classList.add('expanded');
            toggleButton.textContent = 'Show less';
        }
    }
</script>

</body>
</html>

<?php $conn->close(); ?>