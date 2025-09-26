<?php
session_start();
require 'config.php';

$allowedTypes = ['application/pdf'];
$maxFileSizeMB = 20;
$maxFileSize = $maxFileSizeMB * 1024 * 1024;
$maxUploads = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_files'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "❌ CSRF token validation failed.";
        $_SESSION['message_type'] = "error";
        header("Location: upload.php");
        exit();
    }

    $uploadedBy = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "guest";
    $uploadDir = 'uploads/user_pdf_uploads/';
    $approved = 0;
    $uploadSuccess = true;
    $uploadedFiles = [];

    if (!empty($_FILES['files']['name'][0])) {
        if (count($_FILES['files']['name']) > $maxUploads) {
            $_SESSION['message'] = "⚠️ You can upload a maximum of $maxUploads files at a time.";
            $_SESSION['message_type'] = "warning";
            header("Location: user_pdfs_uploads.php");
            exit();
        }

        foreach ($_FILES['files']['name'] as $key => $name) {
            $tmpName = $_FILES['files']['tmp_name'][$key];
            $error = $_FILES['files']['error'][$key];
            $size = $_FILES['files']['size'][$key];
            $type = $_FILES['files']['type'][$key];
            $subject = isset($_POST['category'][$key]) ? $conn->real_escape_string($_POST['category'][$key]) : '';

            if (!isset($_SESSION['message'])) $_SESSION['message'] = '';

            if ($error === UPLOAD_ERR_OK) {
                if (in_array($type, $allowedTypes)) {
                    if ($size <= $maxFileSize) {
                        $fileName = time() . '_' . bin2hex(random_bytes(8)) . '_' . basename($name);
                        $targetFilePath = $uploadDir . $fileName;

                        if (move_uploaded_file($tmpName, $targetFilePath)) {
                            $stmt = $conn->prepare("INSERT INTO user_pdfs_uploads (filename, file_path, uploaded_by, approved, subject, original_name) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param('sssiss', $fileName, $targetFilePath, $uploadedBy, $approved, $subject, basename($name));
                            if (!$stmt->execute()) {
                                $_SESSION['message'] .= "Error saving file info for {$name}: " . $stmt->error . "<br>";
                                $_SESSION['message_type'] = "error";
                                $uploadSuccess = false;
                            }
                            $stmt->close();
                            $uploadedFiles[] = basename($name);
                        } else {
                            $_SESSION['message'] .= "Failed to upload {$name}.<br>";
                            $_SESSION['message_type'] = "error";
                            $uploadSuccess = false;
                        }
                    } else {
                        $_SESSION['message'] .= "{$name} exceeds the max size of {$maxFileSizeMB}MB.<br>";
                        $_SESSION['message_type'] = "error";
                        $uploadSuccess = false;
                    }
                } else {
                    $_SESSION['message'] .= "{$name} is not a valid PDF.<br>";
                    $_SESSION['message_type'] = "error";
                    $uploadSuccess = false;
                }
            } else {
                $_SESSION['message'] .= "Error uploading {$name}. Error code: {$error}<br>";
                $_SESSION['message_type'] = "error";
                $uploadSuccess = false;
            }
        }

        if ($uploadSuccess && !isset($_SESSION['message_type'])) {
            $_SESSION['message'] = "✅ " . count($uploadedFiles) . " file(s) uploaded successfully";
            $_SESSION['message_type'] = "success";
        }
    } else {
        $_SESSION['message'] = "⚠️ Please select at least one file to upload.";
        $_SESSION['message_type'] = "warning";
    }

    header("Location: user_pdfs_uploads.php");
    exit();
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$categories = ["Mathematics", "Computer Science", "Physics", "Chemistry", "Economics", "Law", "Agriculture", "Humanities", "Other"];
?>
<?php
// After successful file upload
if ($upload_success) {
    track_activity($_SESSION['user_id'], ACTIVITY_UPLOAD, "Uploaded file: " . $filename);
    echo "File uploaded successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload PDF Files</title>
  <link rel="stylesheet" href="css/userpdfuploads.css">
  <style>
    #dropZone {
      padding: 20px;
      border: 2px dashed #ccc;
      border-radius: 10px;
      text-align: center;
      margin-bottom: 20px;
      background: #f9f9f9;
      transition: background 0.3s;
    }
    #dropZone.dragover {
      background: #e3ffe3;
    }
    #progress-container {
      display: none;
      margin-top: 10px;
    }
    #progressBar {
      width: 100%;
      height: 20px;
    }
  </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="empty"></div>
<section class="container">

<?php if (isset($_SESSION['message'])): ?>
    <p class='popup-message <?= $_SESSION['message_type'] ?>'><?= $_SESSION['message'] ?></p>
    <?php if ($_SESSION['message_type'] === 'success'): ?>
        <div class='thank-you-note'>
            <h3>🎉 Thank You!</h3>
            <p>Your contributions help students across universities to learn better and faster.</p>
            <p>All files will be reviewed before they appear on the platform.</p>
        </div>
    <?php endif; ?>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<form class="body" id="uploadForm" method="post" enctype="multipart/form-data">
  <h2>Upload Notes</h2>
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<!--
  <div id="dropZone">📁 Drag & drop PDFs here or click below</div>
    -->
  <div id="fileInputs">
    <div class="file-input-group">
      <label>Select Subject:</label>
      <select name="category[]" required>
        <option value="">-- Choose a Subject --</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat ?>"><?= $cat ?></option>
        <?php endforeach; ?>
      </select><br><br>

      <label>Select File:</label>
      <input type="file" name="files[]" accept=".pdf" required>
    </div>
  </div>

  <button type="button" onclick="addFileInput()">Add Another File</button><br><br>
  <button type="submit" name="upload_files">Upload</button>

  <div id="progress-container">
    <progress id="progressBar" value="0" max="100"></progress>
    <span id="progressText">0%</span>
  </div>
</form>
</section>

<script>
const categories = <?= json_encode($categories) ?>;
let fileCount = 1;
const maxFiles = 5;

function addFileInput() {
  if (fileCount >= maxFiles) {
    alert("You can upload a maximum of " + maxFiles + " files.");
    return;
  }

  const group = document.createElement('div');
  group.classList.add('file-input-group');
  const options = categories.map(cat => `<option value="${cat}">${cat}</option>`).join('');

  group.innerHTML = `
    <label>Select Subject:</label>
    <select name="category[]" required>
      <option value="">-- Choose a Subject --</option>
      ${options}
    </select><br><br>

    <label>Select File:</label>
    <input type="file" name="files[]" accept=".pdf" required><br><br>
  `;

  document.getElementById('fileInputs').appendChild(group);
  fileCount++;
}

// Handling the drag-and-drop functionality

dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('dragover');

  const files = e.dataTransfer.files;
  for (let i = 0; i < files.length && fileCount < maxFiles; i++) {
    const group = document.createElement('div');
    group.classList.add('file-input-group');
    const options = categories.map(cat => `<option value="${cat}">${cat}</option>`).join('');
    group.innerHTML = `
      <label>Select Subject:</label>
      <select name="category[]" required>
        <option value="">-- Choose a Subject --</option>
        ${options}
      </select><br><br>
      <label>Selected File:</label>
      <input type="file" name="files[]" accept=".pdf" required><br><br>
    `;

    document.getElementById('fileInputs').appendChild(group);
    const inputFile = group.querySelector('input[type="file"]');
    inputFile.files = createFileList([files[i]]);
    fileCount++;
  }
});

function createFileList(files) {
  const dataTransfer = new DataTransfer();
  files.forEach(file => dataTransfer.items.add(file));
  return dataTransfer.files;
}

// Auto-hide message
setTimeout(() => {
  const msg = document.querySelector('.popup-message');
  if (msg) {
    msg.style.opacity = 0;
    setTimeout(() => msg.remove(), 500);
  }
}, 4000);
</script>

</body>
</html>
