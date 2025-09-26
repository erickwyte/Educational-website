<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php"); // Redirect to login page
    exit;
}

include '../config.php'; // Database connection file

// Fetch categories for dropdown
$categoryQuery = "SELECT id, category_name FROM blog_categories";
$categoryResult = $conn->query($categoryQuery);

// Handle category creation
if (isset($_POST['new_category']) && !empty($_POST['new_category'])) {
    $newCategory = trim($_POST['new_category']);
    $stmt = $conn->prepare("INSERT INTO blog_categories (category_name) VALUES (?) ON DUPLICATE KEY UPDATE category_name=category_name");
    $stmt->bind_param("s", $newCategory);
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page
        exit;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $content = preg_replace('/\s+/', ' ', trim($_POST['content'])); // Remove multiple spaces and trim
    $category_id = $_POST['category'];

    // Insert post into database
    $stmt = $conn->prepare("INSERT INTO blog_posts (title, author, content, category_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $author, $content, $category_id);
    if ($stmt->execute()) {
        $blog_id = $stmt->insert_id; // Get the last inserted blog post ID
        
        // Handle multiple image uploads
        foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
            $fileName = $_FILES['media']['name'][$key];
            $fileTmp = $_FILES['media']['tmp_name'][$key];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm', 'ogg'];
            
            if (in_array($fileExt, $allowedExts)) {
                $newFileName = uniqid("media_", true) . '.' . $fileExt;
                $target = "../Uploads/blog_images/" . $newFileName;
                move_uploaded_file($fileTmp, $target);
                
                // Determine media type
                $mediaType = in_array($fileExt, ['mp4', 'webm', 'ogg']) ? 'video' : 'image';
                
                // Insert into media table
                $mediaStmt = $conn->prepare("INSERT INTO blog_media (blog_id, media_url, media_type) VALUES (?, ?, ?)");
                $mediaStmt->bind_param("iss", $blog_id, $newFileName, $mediaType);
                $mediaStmt->execute();
                $mediaStmt->close();
            }
        }
        $message = "<p class='success'>Post uploaded successfully!</p>";
    } else {
        $message = "<p class='error'>Error uploading post.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Upload Blog Post</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        form {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="text"],
        textarea,
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        .error, .success {
            text-align: center;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 15px;
            }

            form {
                padding: 15px;
            }

            button {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <?php include 'adminheader.php'; ?>

    <div class="container">
        <h2>Upload Blog Post</h2>

        <?php if (isset($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <form action="" method="POST">
            <label>Create New Category:</label>
            <input type="text" name="new_category" placeholder="Enter new category name" required>
            <button type="submit">Add Category</button>
        </form>

        <form action="" method="POST" enctype="multipart/form-data">
            <label>Title:</label>
            <input type="text" name="title" placeholder="Enter blog post title" required>

            <label>Author:</label>
            <input type="text" name="author" placeholder="Enter author name" required>

            <label>Content:</label>
            <textarea name="content" rows="5" placeholder="Enter blog post content" required></textarea>

            <label>Category:</label>
            <select name="category" required>
                <option value="">Select Category</option>
                <?php while ($category = $categoryResult->fetch_assoc()) { ?>
                    <option value="<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php } ?>
            </select>

            <label>Upload Media (Images/Videos):</label>
            <input type="file" name="media[]" accept="image/*,video/*" multiple>

            <button type="submit">Upload</button>
        </form>
    </div>
</body>
</html>