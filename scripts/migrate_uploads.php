<?php
/**
 * scripts/migrate_uploads.php
 * Generic migration tool to move uploaded files for different tables into SECURE_UPLOAD_ROOT and update DB.
 *
 * Usage example (migrate notes_pdfs):
 *  php scripts/migrate_uploads.php --table=notes_pdfs --file=file_path --id=id --limit=100 --dry-run
 *  php scripts/migrate_uploads.php --table=questions_pdfs --file=file_path --id=id --confirm
 *
 * WARNING: backup DB and files before running with --confirm
 */

chdir(__DIR__ . '/../');
require 'config.php';

$opts = getopt('', ['table:', 'file:', 'id::', 'limit::', 'dry-run', 'confirm']);
$table = $opts['table'] ?? '';
$fileCol = $opts['file'] ?? 'file_path';
$idCol = $opts['id'] ?? 'id';
$limit = isset($opts['limit']) ? intval($opts['limit']) : 0;
$dryRun = isset($opts['dry-run']) && !isset($opts['confirm']);
$confirm = isset($opts['confirm']);

if (empty($table) || empty($fileCol)) {
    echo "Usage: php scripts/migrate_uploads.php --table=<table> --file=<file_column> [--id=<id_column>] [--limit=100] [--dry-run|--confirm]\n";
    exit(1);
}

// Validate identifiers (table and column names) to prevent SQL injection via table/column parameters.
function is_valid_identifier($s) {
    return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $s);
}

if (!is_valid_identifier($table) || !is_valid_identifier($fileCol) || !is_valid_identifier($idCol)) {
    outln("Invalid table or column name provided. Only alphanumeric and underscore characters are allowed and must start with a letter or underscore.");
    lg("Invalid identifiers: table={$table}, fileCol={$fileCol}, idCol={$idCol}");
    exit(2);
}

$logFile = defined('LOG_DIR') ? LOG_DIR . DIRECTORY_SEPARATOR . "migrate_{$table}.log" : __DIR__ . "/../migrate_{$table}.log";
function outln($s) { echo $s . PHP_EOL; }
function lg($s) { global $logFile; file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $s\n", FILE_APPEND); }

outln("Migrating table: $table, file column: $fileCol, id column: $idCol");
outln('Dry run: ' . ($dryRun ? 'YES' : 'NO') . '; Confirm: ' . ($confirm ? 'YES' : 'NO'));
lg("Start migration for $table (dryRun={$dryRun})");

// Set legacy paths to search for files in the webroot uploads
$legacyPaths = [__DIR__ . '/../uploads/', __DIR__ . '/../uploads/user_pdf_uploads/', __DIR__ . '/../uploads/profile_photos/'];

// Ensure SECURE_UPLOAD_ROOT exists
if (!is_dir(SECURE_UPLOAD_ROOT)) {
    outln("SECURE_UPLOAD_ROOT not found: " . SECURE_UPLOAD_ROOT);
    if ($confirm && !$dryRun) {
        @mkdir(SECURE_UPLOAD_ROOT, 0700, true);
        outln("Created: " . SECURE_UPLOAD_ROOT);
    } else {
        outln("Set SECURE_UPLOAD_ROOT in .env or run with --confirm after creating the directory.");
        exit(1);
    }
}

$sql = "SELECT $idCol, $fileCol FROM $table WHERE COALESCE($fileCol, '') != ''" . ($limit > 0 ? " LIMIT $limit" : "");
$result = $conn->query($sql);
if (!$result) { outln('DB query failed: ' . $conn->error); lg('DB query failed: ' . $conn->error); exit(1); }

$done = 0; $errs = 0;
while ($r = $result->fetch_assoc()) {
    $id = intval($r[$idCol]);
    $path = $r[$fileCol];
    $candidates = [];
    if (preg_match('/^[\\\/]|^[A-Za-z]:\\\\?/', $path) || strpos($path, '/') !== false) $candidates[] = $path;
    // treat as filename-only
    $candidates[] = rtrim(SECURE_UPLOAD_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($path);
    foreach ($legacyPaths as $p) $candidates[] = $p . basename($path);

    $found = false; $source = null;
    foreach ($candidates as $cand) { if (file_exists($cand) && is_file($cand)) { $found = true; $source = $cand; break; } }
    if (!$found) { outln("[$id] SKIP missing: $path"); lg("[$id] SKIP missing: $path"); $errs++; continue; }

    // Build destination
    $ext = pathinfo($source, PATHINFO_EXTENSION);
    $safe = time() . '_' . bin2hex(random_bytes(6)) . '_' . preg_replace('/[^A-Za-z0-9._-]/','_', basename($source));
    if (!empty($ext)) $safe = preg_replace('/\.' . preg_quote($ext, '/') . '$/i','', $safe) . '.' . $ext;
    $dest = rtrim(SECURE_UPLOAD_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safe;

    outln("[$id] $source -> $dest"); lg("[$id] $source -> $dest");
    if ($dryRun) continue;

    // backup and move
    $backupDir = rtrim(SECURE_UPLOAD_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'migrate_backup'; if (!is_dir($backupDir)) @mkdir($backupDir, 0700, true);
    @copy($source, $backupDir . DIRECTORY_SEPARATOR . basename($source));
    if (!@rename($source, $dest)) { outln("[$id] ERROR move"); lg("[$id] ERROR move"); $errs++; continue; }

    // update db
    $stmt = $conn->prepare("UPDATE $table SET $fileCol = ? WHERE $idCol = ?");
    if (!$stmt) { outln("[$id] ERROR prepare: " . $conn->error); lg("[$id] ERROR prepare: " . $conn->error); $errs++; continue; }
    $stmt->bind_param('si', $safe, $id);
    if (!$stmt->execute()) { outln("[$id] ERROR update: " . $stmt->error); lg("[$id] ERROR update: " . $stmt->error); $errs++; $stmt->close(); continue; }
    $stmt->close();
    outln("[$id] OK: moved & updated"); lg("[$id] OK"); $done++;
}

outln("Done. success: $done, errors: $errs"); lg("Finished. success: $done, errors: $errs");

?>
