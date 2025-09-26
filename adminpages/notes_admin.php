<?php
session_start();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php");
    exit;
}

require '../config.php';

// Initialize response messages
$messages = ['success' => [], 'errors' => []];

if (!$conn) {
    $messages['errors'][] = "Database connection failed: " . mysqli_connect_error();
}

// Validate CSRF token
function validate_csrf_token() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }
}

// Handle category actions
if (isset($_POST['add_category'])) {
    validate_csrf_token();
    
    $name = trim($_POST['category_name']);
    if (empty($name)) {
        $messages['errors'][] = "Category name cannot be empty";
    } elseif (strlen($name) > 100) {
        $messages['errors'][] = "Category name too long";
    } else {
        $stmt = $conn->prepare("INSERT INTO notes_categories (name) VALUES (?)");
        if (!$stmt) {
            $messages['errors'][] = "SQL Error (Add Category): " . $conn->error;
        } else {
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $messages['success'][] = "Category added successfully";
            } else {
                $messages['errors'][] = "Failed to add category";
            }
            $stmt->close();
        }
    }
}

if (isset($_POST['delete_category'])) {
    validate_csrf_token();
    
    $id = intval($_POST['category_id']);
    $stmt = $conn->prepare("DELETE FROM notes_categories WHERE id = ?");
    if (!$stmt) {
        $messages['errors'][] = "SQL Error (Delete Category): " . $conn->error;
    } else {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $messages['success'][] = "Category deleted successfully";
        } else {
            $messages['errors'][] = "Failed to delete category";
        }
        $stmt->close();
    }
}

// Handle PDF upload
if (isset($_POST['upload'])) {
    validate_csrf_token();
    
    $category_id = intval($_POST['category']);
    $pdf_title = trim($_POST['pdf_title']);
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Uploads/notes_pdfs/';
    $db_path_prefix = '/Uploads/notes_pdfs/';
    
    if (empty($pdf_title) || strlen($pdf_title) > 200) {
        $messages['errors'][] = "Invalid PDF title";
    } elseif ($category_id <= 0) {
        $messages['errors'][] = "Please select a valid category";
    } elseif (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $messages['errors'][] = "No file uploaded";
    } else {
        $pdf_file = $_FILES['pdf_file'];
        $file_ext = strtolower(pathinfo($pdf_file['name'], PATHINFO_EXTENSION));
        $max_file_size = 10 * 1024 * 1024; // 10MB
        $allowed_types = ['application/pdf'];
        
        if ($file_ext !== 'pdf' || !in_array($pdf_file['type'], $allowed_types)) {
            $messages['errors'][] = "Only PDF files are allowed";
        } elseif ($pdf_file['size'] > $max_file_size) {
            $messages['errors'][] = "File size exceeds 10MB limit";
        } else {
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $pdf_filename = uniqid() . '.pdf';
            $pdf_path = $upload_dir . $pdf_filename;
            $db_path = $db_path_prefix . $pdf_filename;
            
            $conn->begin_transaction();
            try {
                if (move_uploaded_file($pdf_file['tmp_name'], $pdf_path)) {
                    chmod($pdf_path, 0644); // Set secure permissions
                    $uploaded_at = date('Y-m-d H:i:s');
                    
                    $stmt = $conn->prepare("INSERT INTO notes_pdfs (category_id, title, file_path, uploaded_at) VALUES (?, ?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception("SQL Error (Upload PDF): " . $conn->error);
                    }
                    $stmt->bind_param("isss", $category_id, $pdf_title, $db_path, $uploaded_at);
                    
                    if ($stmt->execute()) {
                        $conn->commit();
                        $messages['success'][] = "PDF uploaded successfully";
                        error_log("PDF uploaded to: " . $pdf_path); // Debugging
                    } else {
                        throw new Exception("Database error");
                    }
                    $stmt->close();
                } else {
                    throw new Exception("File upload error");
                }
            } catch (Exception $e) {
                $conn->rollback();
                $messages['errors'][] = "Error uploading PDF: " . $e->getMessage();
                if (file_exists($pdf_path)) {
                    unlink($pdf_path);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasaplus Admin Panel</title>
    <link rel="stylesheet" href="css/notes_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<?php include 'adminheader.php'; ?>
<div class="container">
    <!-- Display messages -->
    <?php foreach ($messages['success'] as $msg): ?>
        <div class="message success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?></div>
    <?php endforeach; ?>
    <?php foreach ($messages['errors'] as $msg): ?>
        <div class="message error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($msg); ?></div>
    <?php endforeach; ?>
    <div class="grid-container">
        <div class="card">
            <h2><i class="fas fa-folder-plus"></i> Manage Note Categories</h2>
            <form method="POST" class="form-group">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="input-group">
                    <i class="fas fa-tag"></i>
                    <input type="text" name="category_name" placeholder="Category Name" required>
                </div>
                <button type="submit" name="add_category"><i class="fas fa-plus"></i> Add Category</button>
            </form>
        </div>
        <div class="card">
            <h2><i class="fas fa-upload"></i> Upload Notes</h2>
            <form method="POST" enctype="multipart/form-data" class="form-group">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="input-group">
                    <i class="fas fa-list"></i>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <?php
                        $result = $conn->query("SELECT * FROM notes_categories");
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['name']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="input-group">
                    <i class="fas fa-file-alt"></i>
                    <input type="text" name="pdf_title" placeholder="Enter PDF Title" required>
                </div>
                <div class="input-group file-input">
                    <i class="fas fa-file-pdf"></i>
                    <input type="file" name="pdf_file" accept="application/pdf" required>
                </div>
                <button type="submit" name="upload"><i class="fas fa-cloud-upload-alt"></i> Upload</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>