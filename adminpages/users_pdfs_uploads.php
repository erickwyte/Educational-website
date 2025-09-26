<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

require '../config.php'; // Ensure database connection

// Handle delete request
if (isset($_POST['delete_pdf'])) {
    $pdf_id = $_POST['pdf_id'];

    // Get file path before deleting
    $query = "SELECT file_path FROM user_pdfs_uploads WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $pdf_id);
    $stmt->execute();
    $stmt->bind_result($file_path);
    $stmt->fetch();
    $stmt->close();

    if (!empty($file_path) && file_exists($file_path)) {
        unlink($file_path); // Delete file from server
    }

    // Delete record from database
    $delete_query = "DELETE FROM user_pdfs_uploads WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $pdf_id);
    $stmt->execute();
    $stmt->close();

    header("Location: users_pdfs_uploads.php"); // Redirect to refresh page
    exit();
}

// Handle status update (approve/reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $pdf_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    }
    
    if (isset($status)) {
        $update_query = "UPDATE user_pdfs_uploads SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $status, $pdf_id);
        $stmt->execute();
        $stmt->close();
        
        header("Location: users_pdfs_uploads.php");
        exit();
    }
}

// Fetch all uploaded PDFs with user information if available
$query = "SELECT up.*, u.username as user_name 
          FROM user_pdfs_uploads up 
          LEFT JOIN users u ON up.user_id = u.id 
          ORDER BY up.uploaded_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage PDFs</title>
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
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 25px;
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

        .status-filter {
            margin-bottom: 20px;
            text-align: center;
        }

        .status-filter a {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            background-color: var(--light-gray);
            color: var(--black);
            transition: var(--transition);
        }

        .status-filter a.active {
            background-color: var(--primary-green);
            color: var(--white);
        }

        .status-filter a:hover {
            background-color: var(--primary-green-hover);
            color: var(--white);
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

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .view-btn, .download-btn, .delete-btn, .approve-btn, .reject-btn {
            display: inline-block;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
            text-align: center;
            border: none;
            cursor: pointer;
            margin: 2px;
        }

        .view-btn {
            background-color: var(--primary-green);
            color: var(--white);
        }

        .view-btn:hover {
            background-color: var(--primary-green-hover);
            transform: translateY(-2px);
        }

        .download-btn {
            background-color: var(--yellow);
            color: var(--black);
        }

        .download-btn:hover {
            background-color: var(--yellow-hover);
            transform: translateY(-2px);
        }

        .delete-btn {
            background-color: #dc3545;
            color: var(--white);
        }

        .delete-btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .approve-btn {
            background-color: #28a745;
            color: var(--white);
        }

        .approve-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .reject-btn {
            background-color: #ffc107;
            color: var(--black);
        }

        .reject-btn:hover {
            background-color: #e0a800;
            transform: translateY(-2px);
        }

        form {
            display: inline;
            margin: 0;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                padding: 20px;
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
            .container {
                padding: 15px;
                margin: 10px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .view-btn, .download-btn, .delete-btn, .approve-btn, .reject-btn {
                padding: 6px 12px;
                font-size: 13px;
                margin-bottom: 5px;
                display: block;
                width: 100%;
            }
            
            .status-filter a {
                display: block;
                margin: 5px 0;
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            th, td {
                padding: 8px;
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
            
            h2 {
                font-size: 20px;
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
            
            .action-buttons {
                text-align: center;
            }
            
            .action-buttons form, .action-buttons a {
                display: block;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
<?php include 'adminheader.php'; ?>

<div class="container">
    <h2>Uploaded PDFs</h2>
    
    <div class="status-filter">
        <a href="users_pdfs_uploads.php" class="<?php echo (!isset($_GET['status']) ? 'active' : ''); ?>">All</a>
        <a href="users_pdfs_uploads.php?status=pending" class="<?php echo (isset($_GET['status']) && $_GET['status'] == 'pending' ? 'active' : ''); ?>">Pending</a>
        <a href="users_pdfs_uploads.php?status=approved" class="<?php echo (isset($_GET['status']) && $_GET['status'] == 'approved' ? 'active' : ''); ?>">Approved</a>
        <a href="users_pdfs_uploads.php?status=rejected" class="<?php echo (isset($_GET['status']) && $_GET['status'] == 'rejected' ? 'active' : ''); ?>">Rejected</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Filename</th>
                <th>Subject</th>
                <th>Uploaded By</th>
                <th>Status</th>
                <th>Uploaded At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) { 
                    // Skip if filtering by status and doesn't match
                    if (isset($_GET['status']) && $row['status'] != $_GET['status']) {
                        continue;
                    }
                    
                    $uploaded_by = !empty($row['user_name']) ? $row['user_name'] : $row['uploaded_by'];
            ?>
                <tr>
                    <td data-label="Filename"><?php echo htmlspecialchars($row['filename']); ?></td>
                    <td data-label="Subject"><?php echo htmlspecialchars($row['subject']); ?></td>
                    <td data-label="Uploaded By"><?php echo htmlspecialchars($uploaded_by); ?></td>
                    <td data-label="Status">
                        <span class="status-badge status-<?php echo $row['status']; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td data-label="Uploaded At"><?php echo date('M j, Y g:i A', strtotime($row['uploaded_at'])); ?></td>
                    <td data-label="Actions" class="action-buttons">
                        <a href="view_pdf.php?id=<?php echo $row['id']; ?>" class="view-btn">View</a>
                    <!--    <a href="<?php echo $row['file_path']; ?>" download class="download-btn">Download</a>-->
                        
                        <?php if ($row['status'] == 'pending') { ?>
                            <a href="users_pdfs_uploads.php?action=approve&id=<?php echo $row['id']; ?>" class="approve-btn" onclick="return confirm('Approve this PDF?')">Approve</a>
                            <a href="users_pdfs_uploads.php?action=reject&id=<?php echo $row['id']; ?>" class="reject-btn" onclick="return confirm('Reject this PDF?')">Reject</a>
                        <?php } ?>
                        
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this PDF?');">
                            <input type="hidden" name="pdf_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_pdf" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php 
                }
            } else { ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">
                        No PDFs found.
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>