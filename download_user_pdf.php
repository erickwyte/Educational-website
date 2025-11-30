<?php
require 'config.php';

// Require session so we can check authorization
if (!session_id()) session_start();

// Allow both inline viewing (for viewers) and attachment (download)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mode = isset($_GET['mode']) && $_GET['mode'] === 'download' ? 'download' : 'inline';

if ($id <= 0) {
    http_response_code(400);
    echo 'Bad request';
    exit;
}

try {
    $stmt = $conn->prepare("SELECT file_path, original_name FROM user_pdfs_uploads WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($file_path, $original_name);
    $stmt->fetch();
    $stmt->close();
} catch (Exception $e) {
    error_log('Download lookup error: ' . $e->getMessage());
    http_response_code(500);
    exit;
}

if (empty($file_path)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

// Authorization checks:
// - Admins may access any file (admin users are indicated by user_type == 'admin' or admin_id session)
// - Owners (uploaded_by === current user id) may access their file
// - Approved files (approved = 'approved') are allowed for public download/viewing

// Re-query to include uploader and approval status
try {
    $stmt2 = $conn->prepare("SELECT uploaded_by, approved FROM user_pdfs_uploads WHERE id = ? LIMIT 1");
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $stmt2->bind_result($uploaded_by, $approved);
    $stmt2->fetch();
    $stmt2->close();
} catch (Exception $e) {
    error_log('Download auth lookup error: ' . $e->getMessage());
    http_response_code(500);
    exit;
}

// Helper to check admin session
function current_user_is_admin() {
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') return true;
    if (isset($_SESSION['admin_id'])) return true;
    return false;
}

// Determine access
$hasAccess = false;
if (current_user_is_admin()) {
    $hasAccess = true;
}
// owner
if (!$hasAccess && isset($_SESSION['user_id']) && intval($_SESSION['user_id']) === intval($uploaded_by)) {
    $hasAccess = true;
}
// published/approved
if (!$hasAccess && isset($approved) && $approved === 'approved') {
    $hasAccess = true;
}

if (!$hasAccess) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

// If file_path already contains a directory or absolute path, try that first (legacy)
$candidate = $file_path;
if (!file_exists($candidate)) {
    // Otherwise, treat file_path as filename and look in SECURE_PDF_DIR
    $candidate = SECURE_PDF_DIR . DIRECTORY_SEPARATOR . basename($file_path);
}

if (!file_exists($candidate)) {
    http_response_code(404);
    echo 'File not found';
    error_log('Download failed - file missing: ' . $candidate);
    exit;
}

$filesize = filesize($candidate);
// Force headers
header('Content-Type: application/pdf');
header('Content-Length: ' . $filesize);
$dispName = $original_name ?: basename($candidate);
if ($mode === 'download') {
    header('Content-Disposition: attachment; filename="' . addslashes($dispName) . '"');
} else {
    header('Content-Disposition: inline; filename="' . addslashes($dispName) . '"');
}
// prevent caching issues for sensitive files
header('Cache-Control: private, max-age=0, must-revalidate');
readfile($candidate);
exit;

?>
