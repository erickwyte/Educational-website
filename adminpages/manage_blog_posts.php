<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php"); // Redirect to login page
    exit;
}
?>
<?php

include '../config.php'; // Database connection file

// Handle post deletion
if (isset($_GET['delete'])) {
    $post_id = intval($_GET['delete']);

    // Delete associated media files first
    $mediaQuery = "SELECT media_url FROM blog_media WHERE blog_id = ?";
    $mediaStmt = $conn->prepare($mediaQuery);
    $mediaStmt->bind_param("i", $post_id);
    $mediaStmt->execute();
    $mediaResult = $mediaStmt->get_result();

    while ($media = $mediaResult->fetch_assoc()) {
        $filePath = "../uploads/blog_images/" . $media['media_url'];
        if (file_exists($filePath)) {
            unlink($filePath); // Delete media file
        }
    }
    $mediaStmt->close();

    // Delete media entries from the database
    $deleteMediaQuery = "DELETE FROM blog_media WHERE blog_id = ?";
    $deleteMediaStmt = $conn->prepare($deleteMediaQuery);
    $deleteMediaStmt->bind_param("i", $post_id);
    $deleteMediaStmt->execute();
    $deleteMediaStmt->close();

    // Delete the blog post
    $deletePostQuery = "DELETE FROM blog_posts WHERE id = ?";
    $deletePostStmt = $conn->prepare($deletePostQuery);
    $deletePostStmt->bind_param("i", $post_id);
    if ($deletePostStmt->execute()) {
        echo "<script>alert('Post deleted successfully!'); window.location.href='manage_blog_posts.php';</script>";
    } else {
        echo "<script>alert('Error deleting post.');</script>";
    }
    $deletePostStmt->close();
}

// Fetch all blog posts
$query = "SELECT b.id, b.title, b.created_at, c.category_name 
          FROM blog_posts b 
          JOIN blog_categories c ON b.category_id = c.id 
          ORDER BY b.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog Posts</title>
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
            max-width: 1300px;
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

        .delete-btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: #dc3545;
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: var(--transition);
        }

        .delete-btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: var(--primary-green);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-btn:hover {
            background-color: var(--primary-green-hover);
            transform: translateY(-2px);
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
            
            .delete-btn, .back-btn {
                padding: 6px 12px;
                font-size: 14px;
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
            
            .delete-btn, .back-btn {
                padding: 5px 10px;
                font-size: 13px;
                width: 100%;
                text-align: center;
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
    <h2>Manage Blog Posts</h2>
    
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Date Uploaded</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($post = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td><?php echo htmlspecialchars($post['category_name']); ?></td>
                    <td><?php echo date("F j, Y", strtotime($post['created_at'])); ?></td>
                    <td>
                        <a href="manage_blog_posts.php?delete=<?php echo $post['id']; ?>" 
                           class="delete-btn" 
                           onclick="return confirm('Are you sure you want to delete this post?');">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>