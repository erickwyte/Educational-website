<?php
// admin_moderation_queue.php
require 'config.php';
include 'includes/session_check.php';

// Admin authorization check
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Handle actions
if ($_POST['action'] ?? '') {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF validation failed');
    }
    
    switch ($_POST['action']) {
        case 'approve':
            approveContent($_POST['content_id'], $_POST['content_type']);
            break;
        case 'reject':
            rejectContent($_POST['content_id'], $_POST['content_type'], $_POST['reason']);
            break;
        case 'export':
            exportModerationLogs();
            break;
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get flagged content
$sql = "SELECT aml.*, u.username, u.email,
               COALESCE(dt.title, dc.comment) as content_preview,
               CASE 
                   WHEN aml.content_type = 'topic' THEN dt.title
                   WHEN aml.content_type = 'comment' THEN LEFT(dc.comment, 100)
               END as content_text,
               CASE 
                   WHEN aml.content_type = 'topic' THEN 'discussion_topic'
                   WHEN aml.content_type = 'comment' THEN 'discussion_comments' 
               END as content_table
        FROM ai_moderation_logs aml
        JOIN users u ON aml.user_id = u.id
        LEFT JOIN discussion_topic dt ON aml.content_type = 'topic' AND aml.content_id = dt.id
        LEFT JOIN discussion_comments dc ON aml.content_type = 'comment' AND aml.content_id = dc.id
        WHERE aml.action_taken = 'flagged'
        ORDER BY aml.created_at DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $per_page, $offset);
$stmt->execute();
$flagged_content = $stmt->get_result();

// Get counts for tabs
$counts = getModerationCounts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderation Queue - Dasaplus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .moderation-container { max-width: 1200px; margin: 80px auto 20px; padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; margin-bottom: 5px; }
        .flagged-item { background: white; border-left: 4px solid #e74c3c; margin-bottom: 15px; padding: 15px; border-radius: 4px; }
        .toxicity-high { color: #e74c3c; font-weight: bold; }
        .action-buttons { margin-top: 10px; display: flex; gap: 10px; }
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-approve { background: #27ae60; color: white; }
        .btn-reject { background: #e74c3c; color: white; }
        .pagination { margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="moderation-container">
        <h1>AI Moderation Queue</h1>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $counts['flagged']; ?></div>
                <div>Flagged Content</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $counts['approved']; ?></div>
                <div>Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $counts['rejected']; ?></div>
                <div>Rejected</div>
            </div>
        </div>

        <!-- Export Button -->
        <form method="post" style="margin-bottom: 20px;">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" name="action" value="export" class="btn">Export to CSV</button>
        </form>

        <!-- Flagged Content List -->
        <div class="flagged-list">
            <?php while ($item = $flagged_content->fetch_assoc()): ?>
            <div class="flagged-item">
                <div class="user-info">
                    <strong><?php echo htmlspecialchars($item['username']); ?></strong>
                    (<?php echo htmlspecialchars($item['email']); ?>)
                </div>
                <div class="content-preview">
                    <?php echo htmlspecialchars($item['content_preview']); ?>
                </div>
                <div class="moderation-details">
                    Toxicity Score: <span class="toxicity-high"><?php echo $item['toxicity_score']; ?></span>
                    • Flags: <?php echo implode(', ', json_decode($item['flags'] ?? '[]')); ?>
                </div>
                <form method="post" class="action-buttons">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="content_id" value="<?php echo $item['content_id']; ?>">
                    <input type="hidden" name="content_type" value="<?php echo $item['content_type']; ?>">
                    
                    <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                    <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                    <input type="text" name="reason" placeholder="Rejection reason" style="flex-grow: 1;">
                </form>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <!-- Similar pagination to discussion_forum.php -->
        </div>
    </div>
</body>
</html>

<?php
function getModerationCounts() {
    global $conn;
    
    $sql = "SELECT action_taken, COUNT(*) as count 
            FROM ai_moderation_logs 
            GROUP BY action_taken";
    $result = $conn->query($sql);
    
    $counts = ['flagged' => 0, 'approved' => 0, 'rejected' => 0];
    while ($row = $result->fetch_assoc()) {
        $counts[$row['action_taken']] = $row['count'];
    }
    
    return $counts;
}

function approveContent($content_id, $content_type) {
    global $conn;
    
    // Update moderation log
    $sql = "UPDATE ai_moderation_logs SET action_taken = 'approved' WHERE content_id = ? AND content_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $content_id, $content_type);
    $stmt->execute();
    
    // Content remains published
    $_SESSION['message'] = "Content approved successfully";
}

function rejectContent($content_id, $content_type, $reason) {
    global $conn;
    
    // Update moderation log
    $sql = "UPDATE ai_moderation_logs SET action_taken = 'rejected', admin_notes = ? WHERE content_id = ? AND content_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sis', $reason, $content_id, $content_type);
    $stmt->execute();
    
    // Hide/remove content based on content_type
    if ($content_type === 'topic') {
        $sql = "UPDATE discussion_topic SET is_hidden = 1 WHERE id = ?";
    } else {
        $sql = "UPDATE discussion_comments SET is_hidden = 1 WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $content_id);
    $stmt->execute();
    
    $_SESSION['message'] = "Content rejected and hidden";
}

function exportModerationLogs() {
    global $conn;
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="moderation_logs_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'User', 'Content Type', 'Toxicity Score', 'Flags', 'Action', 'Content Preview']);
    
    $sql = "SELECT aml.created_at, u.username, aml.content_type, aml.toxicity_score, aml.flags, aml.action_taken,
                   COALESCE(dt.title, LEFT(dc.comment, 100)) as content_preview
            FROM ai_moderation_logs aml
            JOIN users u ON aml.user_id = u.id
            LEFT JOIN discussion_topic dt ON aml.content_type = 'topic' AND aml.content_id = dt.id
            LEFT JOIN discussion_comments dc ON aml.content_type = 'comment' AND aml.content_id = dc.id
            ORDER BY aml.created_at DESC";
    
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['created_at'],
            $row['username'],
            $row['content_type'],
            $row['toxicity_score'],
            implode('; ', json_decode($row['flags'] ?? '[]')),
            $row['action_taken'],
            $row['content_preview']
        ]);
    }
    
    fclose($output);
    exit;
}
?>