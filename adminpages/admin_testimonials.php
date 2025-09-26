<?php
require '../config.php'; // DB connection now included from this file

// Hide errors from users, log internally
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Handle approve or delete actions
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE testimonials SET status = 'approved' WHERE id = ?");
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
    }

    if (isset($stmt)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_testimonials.php");
        exit();
    }
}

// Fetch pending testimonials
$pending = [];
$result = $conn->query("SELECT * FROM testimonials WHERE status = 'pending' ORDER BY id DESC");
if ($result) {
    $pending = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}

// Fetch approved testimonials
$approved = [];
$result = $conn->query("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY id DESC");
if ($result) {
    $approved = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Testimonials</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #003300;
            --primary-green-hover: #004d00;
            --yellow: #FFD700;
            --yellow-hover: #e6c300;
            --white: #FFFFFF;
            --black: #000000;
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
            color: var(--black);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--white);
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        h1, h2 {
            font-family: 'Poppins', sans-serif;
            color: var(--primary-green);
            margin-bottom: 20px;
        }

        h1 {
            font-weight: 700;
            font-size: 32px;
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-green);
            margin-bottom: 30px;
        }

        h2 {
            font-weight: 600;
            font-size: 24px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .section {
            margin-bottom: 40px;
        }

        .testimonial {
            background: var(--white);
            padding: 20px;
            margin-bottom: 20px;
            border-left: 6px solid var(--primary-green);
            border-radius: 8px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .testimonial:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .testimonial p {
            margin: 10px 0;
            font-size: 16px;
        }

        .testimonial strong {
            color: var(--primary-green);
        }

        .actions {
            margin-top: 15px;
        }

        .actions a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
            margin-right: 10px;
        }

        .approve {
            background-color: var(--primary-green);
            color: var(--white);
        }

        .approve:hover {
            background-color: var(--primary-green-hover);
            transform: translateY(-2px);
        }

        .delete {
            background-color: #dc3545;
            color: var(--white);
        }

        .delete:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .no-data {
            color: #777;
            font-style: italic;
            padding: 20px;
            text-align: center;
            background-color: var(--light-gray);
            border-radius: 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 28px;
            }
            
            h2 {
                font-size: 22px;
            }
            
            .testimonial {
                padding: 15px;
            }
            
            .actions a {
                padding: 6px 12px;
                font-size: 13px;
                margin-bottom: 5px;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 24px;
            }
            
            h2 {
                font-size: 20px;
            }
            
            .testimonial {
                padding: 12px;
            }
            
            .testimonial p {
                font-size: 14px;
            }
            
            .actions a {
                display: block;
                width: 100%;
                text-align: center;
                margin-bottom: 8px;
            }
        }
    </style>

    <script>
        function confirmAction(action, name) {
            return confirm(`Are you sure you want to ${action} the testimonial from "${name}"?`);
        }
    </script>
</head>
<body>

    <div class="container">
        <h1>Admin Panel - Manage Testimonials</h1>

        <div class="section">
            <h2>Pending Testimonials</h2>
            <?php if (count($pending) > 0): ?>
                <?php foreach ($pending as $t): ?>
                    <div class="testimonial">
                        <p><strong>Name:</strong> <?= htmlspecialchars($t['name']) ?></p>
                        <p><strong>University:</strong> <?= htmlspecialchars($t['university']) ?></p>
                        <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($t['message'])) ?></p>
                        <div class="actions">
                            <a href="?action=approve&id=<?= $t['id'] ?>" class="approve" onclick="return confirmAction('approve', '<?= addslashes($t['name']) ?>')">Approve</a>
                            <a href="?action=delete&id=<?= $t['id'] ?>" class="delete" onclick="return confirmAction('delete', '<?= addslashes($t['name']) ?>')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">No pending testimonials.</p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Approved Testimonials</h2>
            <?php if (count($approved) > 0): ?>
                <?php foreach ($approved as $t): ?>
                    <div class="testimonial">
                        <p><strong>Name:</strong> <?= htmlspecialchars($t['name']) ?></p>
                        <p><strong>University:</strong> <?= htmlspecialchars($t['university']) ?></p>
                        <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($t['message'])) ?></p>
                        <div class="actions">
                            <a href="?action=delete&id=<?= $t['id'] ?>" class="delete" onclick="return confirmAction('delete', '<?= addslashes($t['name']) ?>')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">No approved testimonials yet.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>