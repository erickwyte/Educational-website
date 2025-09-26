<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php"); // Redirect to login page
    exit;
}
?>

<?php
require '../config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure database connection is established
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle Delete PDF Request
if (isset($_POST['delete_pdf'])) {
    $pdf_id = intval($_POST['pdf_id']);

    // Fetch PDF file path
    $stmt = $conn->prepare("SELECT file_path FROM notes_pdfs WHERE id = ?");
    $stmt->bind_param("i", $pdf_id);
    $stmt->execute();
    $stmt->bind_result($file_path);
    $stmt->fetch();
    $stmt->close();

    // Delete PDF file from server
    if (!empty($file_path) && file_exists($file_path)) {
        if (!unlink($file_path)) {
            die("Error: Failed to delete PDF file.");
        }
    }

    // Fetch and delete associated images
    $stmt = $conn->prepare("SELECT image_path FROM notes_images WHERE pdf_id = ?");
    $stmt->bind_param("i", $pdf_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $image_path = $row['image_path'];
        if (!empty($image_path) && file_exists($image_path)) {
            if (!unlink($image_path)) {
                die("Error: Failed to delete image file.");
            }
        }
    }
    $stmt->close();

    // Disable foreign key checks for deletion
    $conn->query("SET foreign_key_checks = 0");

    // Delete image records
    $stmt = $conn->prepare("DELETE FROM notes_images WHERE pdf_id = ?");
    $stmt->bind_param("i", $pdf_id);
    $stmt->execute();
    $stmt->close();

    // Delete the PDF record
    $stmt = $conn->prepare("DELETE FROM notes_pdfs WHERE id = ?");
    $stmt->bind_param("i", $pdf_id);
    $stmt->execute();
    $stmt->close();

    // Re-enable foreign key checks
    $conn->query("SET foreign_key_checks = 1");

    echo "<script>alert('PDF deleted successfully!'); window.location.href='manage_notes.php';</script>";
}

// Handle Edit PDF Title Request
if (isset($_POST['edit_pdf'])) {
    $pdf_id = intval($_POST['pdf_id']);
    $new_title = trim($_POST['new_title']);

    if (!empty($new_title)) {
        $stmt = $conn->prepare("UPDATE notes_pdfs SET title = ? WHERE id = ?");
        $stmt->bind_param("si", $new_title, $pdf_id);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Title updated successfully!'); window.location.href='manage_notes.php';</script>";
    } else {
        echo "<script>alert('Error: Title cannot be empty!'); window.location.href='manage_notes.php';</script>";
    }
}

// Fetch PDFs
$fetch_pdfs = $conn->query("SELECT * FROM notes_pdfs ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage PDFs</title>
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
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 20px;
            text-align: center;
            font-size: 28px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--primary-green);
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

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-size: 15px;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 2px rgba(0, 51, 0, 0.2);
        }

        button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: var(--transition);
        }

        button[name="edit_pdf"] {
            background-color: var(--primary-green);
            color: var(--white);
            margin-right: 10px;
        }

        button[name="edit_pdf"]:hover {
            background-color: var(--primary-green-hover);
        }

        button[name="delete_pdf"] {
            background-color: #dc3545;
            color: var(--white);
        }

        button[name="delete_pdf"]:hover {
            background-color: #c82333;
        }

        form {
            display: inline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
                margin: 10px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            th, td {
                padding: 10px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            button {
                padding: 6px 12px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            th, td {
                padding: 8px;
            }
            
            h2 {
                font-size: 20px;
            }
            
            input[type="text"] {
                padding: 8px;
                font-size: 14px;
            }
            
            button {
                padding: 5px 10px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

<?php include 'adminheader.php'; ?>
<div class="container">
    <h2>Manage Notes</h2>
    <table>
        <tr>
            <th>Title</th>
            <th>Actions</th>
        </tr>
        <?php while ($pdf = $fetch_pdfs->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($pdf['title']) ?></td>
            <td>
                <!-- Edit Form -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="pdf_id" value="<?= $pdf['id'] ?>">
                    <input type="text" name="new_title" value="<?= htmlspecialchars($pdf['title']) ?>" required>
                    <button type="submit" name="edit_pdf">Edit</button>
                </form>

                <!-- Delete Form -->
                <form method="post" onsubmit="return confirm('Are you sure you want to delete this PDF?');" style="display:inline;">
                    <input type="hidden" name="pdf_id" value="<?= $pdf['id'] ?>">
                    <button type="submit" name="delete_pdf">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>