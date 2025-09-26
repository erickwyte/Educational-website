<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php"); // Redirect to login page
    exit;
}
?>
<?php

require '../config.php'; // Database connection

// Handle PDF deletion
if (isset($_POST['delete_pdf'])) {
    $pdf_id = intval($_POST['pdf_id']);
    $result = $conn->query("SELECT file_path FROM questions_pdfs WHERE id = $pdf_id");
    $row = $result->fetch_assoc();
    
    if ($row && file_exists($row['file_path'])) {
        unlink($row['file_path']); // Delete file from server
    }
    
    $conn->query("DELETE FROM questions_pdfs WHERE id = $pdf_id");
    echo "<script>alert('PDF deleted successfully!'); window.location.href='manage_questions.php';</script>";
}

// Handle PDF title and university update
if (isset($_POST['update_pdf'])) {
    $pdf_id = intval($_POST['pdf_id']);
    $new_title = $conn->real_escape_string($_POST['new_title']);
    $new_university = intval($_POST['new_university']);
    
    $conn->query("UPDATE questions_pdfs SET title = '$new_title', university_id = $new_university WHERE id = $pdf_id");
    echo "<script>alert('PDF updated successfully!'); window.location.href='manage_questions.php';</script>";
}

// Fetch uploaded PDFs
$query = "SELECT qp.id, qp.title, qp.file_path, qc.name as category, u.id as university_id, u.name as university FROM questions_pdfs qp 
          JOIN questions_categories qc ON qp.category_id = qc.id 
          JOIN universities u ON qp.university_id = u.id 
          ORDER BY qp.id DESC";
$pdfs = $conn->query($query);

// Fetch universities for selection
$universities = $conn->query("SELECT * FROM universities");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Questions</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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

        input[type="text"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-size: 15px;
        }

        input[type="text"]:focus, select:focus {
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
            margin-right: 8px;
        }

        button[name="update_pdf"] {
            background-color: var(--primary-green);
            color: var(--white);
        }

        button[name="update_pdf"]:hover {
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
            margin: 0;
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
                margin-bottom: 5px;
            }
            
            td:last-child {
                min-width: 180px;
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
            
            input[type="text"], select {
                padding: 8px;
                font-size: 14px;
            }
            
            button {
                padding: 5px 10px;
                font-size: 13px;
                width: 100%;
                margin-bottom: 5px;
            }
            
            td:last-child {
                text-align: center;
            }
        }
    </style>
</head>
<body>

<?php include 'adminheader.php'; ?>
<div class="empty"></div>

<div class="container">
    <h2>Manage Questions</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>University</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($pdf = $pdfs->fetch_assoc()): ?>
            <tr>
                <form method="POST">
                    <td>
                        <input type="text" name="new_title" value="<?php echo htmlspecialchars($pdf['title']); ?>" required>
                    </td>
                    <td><?php echo htmlspecialchars($pdf['category']); ?></td>
                    <td>
                        <select name="new_university" required>
                            <?php
                            $universities->data_seek(0);
                            while ($univ = $universities->fetch_assoc()) {
                                $selected = ($univ['id'] == $pdf['university_id']) ? 'selected' : '';
                                echo "<option value='{$univ['id']}' $selected>{$univ['name']}</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="pdf_id" value="<?php echo $pdf['id']; ?>">
                        <button type="submit" name="update_pdf">Update</button>
                        <button type="submit" name="delete_pdf" onclick="return confirm('Are you sure you want to delete this PDF?');">Delete</button>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>