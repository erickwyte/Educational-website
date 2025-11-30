<?php
/**
 * scripts/migrate_user_pdfs.php
 * Move existing user-pdf files from webroot uploads into the secure folder (SECURE_PDF_DIR)
 * and update DB rows to use filename-only file_path.
 *
 * Usage:
 *  php scripts/migrate_user_pdfs.php --dry-run    # show actions without moving
 *  php scripts/migrate_user_pdfs.php --confirm    # actually move files and update DB
 *  php scripts/migrate_user_pdfs.php --limit=100  # limit rows processed (optional)
 *
 * IMPORTANT: Back up your DB and files BEFORE running without --dry-run.
 */

chdir(__DIR__ . '/../');
require 'config.php';

$opts = getopt('', ['dry-run', 'confirm', 'limit::']);
$dryRun = isset($opts['dry-run']) && !$opts['confirm'];
$confirm = isset($opts['confirm']);
$limit = isset($opts['limit']) ? intval($opts['limit']) : 0;

// logging
$logFile = defined('LOG_DIR') ? LOG_DIR . DIRECTORY_SEPARATOR . 'migrate_user_pdfs.log' : __DIR__ . '/../migrate_user_pdfs.log';
function out($msg) {
    echo $msg . PHP_EOL;
}
function logline($line) {
    global $logFile;
    $t = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$t] $line\n", FILE_APPEND);
}

out('Migration script for user_pdfs_uploads');
out('Dry run: ' . ($dryRun ? 'YES' : 'NO') . '; Confirm: ' . ($confirm ? 'YES' : 'NO'));
logline('Starting migration: dryRun=' . ($dryRun ? '1' : '0') . ' confirm=' . ($confirm ? '1' : '0'));

// Resolve search paths (legacy locations inside project)
$legacyPaths = [
    __DIR__ . '/../uploads/user_pdf_uploads/',
    __DIR__ . '/../uploads/'
];

// Ensure secure dir exists
if (!is_dir(SECURE_PDF_DIR)) {
    out('SECURE_PDF_DIR does not exist: ' . SECURE_PDF_DIR);
    logline('SECURE_PDF_DIR missing: ' . SECURE_PDF_DIR);
    if (!$dryRun && $confirm) {
        @mkdir(SECURE_PDF_DIR, 0755, true);
        out('Created SECURE_PDF_DIR: ' . SECURE_PDF_DIR);
    } else {
        out('Aborting - create secure directory first or run with --confirm');
        exit(1);
    }
}

// Build query for rows with non-empty file_path
$sql = "SELECT id, file_path FROM user_pdfs_uploads WHERE COALESCE(file_path, '') != '' ORDER BY uploaded_at DESC";
if ($limit > 0) {
    $sql .= " LIMIT " . intval($limit);
}

if (!$result = $conn->query($sql)) {
    out('DB query failed: ' . $conn->error);
    logline('DB query failed: ' . $conn->error);
    exit(1);
}

$moved = 0;
$errors = 0;
while ($row = $result->fetch_assoc()) {
    $id = (int)$row['id'];
    $filePath = $row['file_path'];

    // resolve candidate locations
    $candidates = [];
    // If file_path already contains a slash or looks absolute - test as-is
    if (preg_match('/^[\\\/]|^[A-Za-z]:\\\\?/', $filePath) || strpos($filePath, '/') !== false) {
        $candidates[] = $filePath; // absolute or relative path from DB
    }
    // Treat file_path as filename only - check secure dir
    $candidates[] = rtrim(SECURE_PDF_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($filePath);
    // legacy locations
    foreach ($legacyPaths as $p) {
        $candidates[] = $p . basename($filePath);
    }

    $found = false;
    $source = null;
    foreach ($candidates as $c) {
        if (file_exists($c) && is_file($c)) {
            $found = true;
            $source = $c;
            break;
        }
    }

    if (!$found) {
        out("[$id] SKIP - file not found for DB path: {$filePath}");
        logline("[$id] SKIP - missing: {$filePath}");
        $errors++;
        continue;
    }

    // Prepare target filename: unique and keep original extension
    $ext = pathinfo($source, PATHINFO_EXTENSION);
    $safeBase = time() . '_' . bin2hex(random_bytes(6)) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($source));
    if (!empty($ext)) {
        $safeBase = preg_replace('/\.' . preg_quote($ext, '/') . '$/i', '', $safeBase) . '.' . $ext;
    }
    $target = rtrim(SECURE_PDF_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeBase;

    out("[$id] FOUND: $source -> will move to $target");
    logline("[$id] FOUND: $source -> $target");

    if ($dryRun) continue;

    // create a backup copy (optional) and move
    $backupDir = rtrim(SECURE_UPLOAD_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'migrate_backup';
    if (!is_dir($backupDir)) @mkdir($backupDir, 0700, true);
    // copy original to backup first
    $backup = $backupDir . DIRECTORY_SEPARATOR . basename($source);
    if (!@copy($source, $backup)) {
        logline("[$id] WARNING: backup copy failed for $source");
    }

    if (!@rename($source, $target)) {
        out("[$id] ERROR: could not move file");
        logline("[$id] ERROR: could not move $source -> $target");
        $errors++;
        continue;
    }

    // Update DB file_path to store only the filename (safeBase)
    $stmt = $conn->prepare("UPDATE user_pdfs_uploads SET file_path = ? WHERE id = ?");
    if (!$stmt) {
        out("[$id] ERROR: prepare failed: " . $conn->error);
        logline("[$id] ERROR: prepare failed: " . $conn->error);
        $errors++;
        continue;
    }
    $stmt->bind_param('si', $safeBase, $id);
    if (!$stmt->execute()) {
        out("[$id] ERROR: DB update failed: " . $stmt->error);
        logline("[$id] ERROR: DB update failed: " . $stmt->error);
        $errors++;
        // attempt to roll back move: move back to source
        @rename($target, $source);
        $stmt->close();
        continue;
    }
    $stmt->close();
    out("[$id] OK: moved and DB updated -> {$safeBase}");
    logline("[$id] OK: moved and DB updated -> {$safeBase}");
    $moved++;
}

out('Done. Moved: ' . $moved . ', errors/skips: ' . $errors);
logline('Finished migration: moved=' . $moved . ' errors=' . $errors);

?>
