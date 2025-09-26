<?php
require 'config.php';

// Secure session cookies
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Strict'
]);

include 'includes/session_check.php';

// Regenerate session to prevent fixation attacks
session_regenerate_id(true);

// Set security headers with proper CSP for inline styles
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'nonce-$nonce' https://www.google.com/recaptcha/;");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Validate topic ID
if (!isset($_GET['topic_id']) || !is_numeric($_GET['topic_id'])) {
    die("Invalid request.");
}

$topic_id = (int) $_GET['topic_id'];

// Fetch topic details
function getTopicDetails($conn, $topic_id) {
    $sql = "SELECT t.*, COALESCE(u.username, 'N/A') AS author 
            FROM discussion_topic t 
            LEFT JOIN users u ON t.user_id = u.id 
            WHERE t.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->fetch_assoc();
}

$topic = getTopicDetails($conn, $topic_id);
if (!$topic) {
    die("Topic not found.");
}

// Format creation date
$created_date = new DateTime($topic['date_created'] ?? 'now', new DateTimeZone('UTC'));
$created_date->setTimezone(new DateTimeZone('Asia/Manila'));
$formatted_date = $created_date->format("M d, Y g:i A");

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!isset($_POST['formToken']) || !isset($_SESSION['formToken']['comment-form']) || 
        !hash_equals($_SESSION['formToken']['comment-form'], $_POST['formToken'])) {
        die("Invalid CSRF token.");
    }

    $content = htmlspecialchars(trim($_POST['content']), ENT_QUOTES, 'UTF-8');

    if (!empty($content)) {
        // Rate limiting: prevent spam comments
        $sql = "SELECT date_posted FROM discussion_comments WHERE user_id = ? ORDER BY date_posted DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $lastComment = $result->fetch_assoc();
        $stmt->close();

        if ($lastComment) {
            $lastTime = strtotime($lastComment['date_posted']);
            if (time() - $lastTime < 30) { // 30 seconds cooldown
                die("You're posting too quickly! Please wait a few seconds.");
            }
        }

        // Insert comment
        $timestamp = date('Y-m-d H:i:s');
        $sql = "INSERT INTO discussion_comments (topic_id, user_id, content, date_posted) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiss', $topic_id, $_SESSION['user_id'], $content, $timestamp);
        
        if (!$stmt->execute()) {
            error_log("Database error: " . $stmt->error);
            die("An error occurred. Please try again later.");
        }
        $stmt->close();
        
        // Redirect to prevent form resubmission
        header("Location: comment.php?topic_id=" . $topic_id . "&posted=1");
        exit;
    }
}

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    if (!isset($_POST['comment_id']) || !is_numeric($_POST['comment_id'])) {
        die("Invalid comment ID.");
    }

    $comment_id = (int) $_POST['comment_id'];

    // Verify ownership
    $sql = "SELECT user_id FROM discussion_comments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();
    $stmt->close();

    if (!$comment || $comment['user_id'] != $_SESSION['user_id']) {
        die("You do not have permission to delete this comment.");
    }

    // Delete comment
    $sql_delete = "DELETE FROM discussion_comments WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('i', $comment_id);
    
    if (!$stmt_delete->execute()) {
        error_log("Delete error: " . $stmt_delete->error);
        die("An error occurred. Please try again.");
    }
    $stmt_delete->close();

    header("Location: comment.php?topic_id=" . $topic_id . "&deleted=1");
    exit;
}

// Fetch comments with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$comments_per_page = 10;
$offset = ($page - 1) * $comments_per_page;

function getComments($conn, $topic_id, $offset, $comments_per_page) {
    $sql = "SELECT c.id, c.content, c.date_posted, COALESCE(u.username, 'N/A') AS author, c.user_id, u.profile_photo
            FROM discussion_comments c 
            LEFT JOIN users u ON c.user_id = u.id 
            WHERE c.topic_id = ? 
            ORDER BY c.date_posted ASC
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $topic_id, $offset, $comments_per_page);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTotalComments($conn, $topic_id) {
    $sql = "SELECT COUNT(*) as total FROM discussion_comments WHERE topic_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    $stmt->close();
    return $total;
}

$comments = getComments($conn, $topic_id, $offset, $comments_per_page);
$total_comments = getTotalComments($conn, $topic_id);
$total_pages = ceil($total_comments / $comments_per_page);

// Generate CSRF token
$_SESSION['formToken']['comment-form'] = bin2hex(random_bytes(32));
?>
<?php
// When a user posts a comment
if (isset($_POST['comment'])) {
    $comment = $_POST['comment'];
    $post_id = $_POST['post_id'];
    
    // Save comment to database...
    
    // Track comment activity
    track_activity($_SESSION['user_id'], ACTIVITY_COMMENT, "Commented on post ID: " . $post_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic['title'], ENT_QUOTES, 'UTF-8'); ?> | Dasaplus Discussions</title>
    <link rel="stylesheet" href="css/comment.css">
   
</head>
<body>

    <?php include 'includes/header.php'; ?>
    <div class="container">
        
        <!-- Success Messages -->
        <?php if (isset($_GET['posted']) && $_GET['posted'] == 1): ?>
            <div class="alert alert-success">
                ✓ Your comment has been posted successfully!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">
                ✓ Comment deleted successfully!
            </div>
        <?php endif; ?>
    
        <div class="card fade-in">
            <div class="topic-header">
                <h1 class="topic-title"><?php echo htmlspecialchars($topic['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <div class="topic-meta">
                    <span>👤 <?php echo htmlspecialchars($topic['author'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span>🕒 <?php echo $formatted_date; ?></span>
                </div>
            </div>
            <div class="topic-content">
                <?php echo nl2br(htmlspecialchars($topic['content'], ENT_QUOTES, 'UTF-8')); ?>
            </div>
        </div>

        <div class="comments-section">
            <h2 class="section-title">Discussion (<?php echo $total_comments; ?> comments)</h2>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?topic_id=<?php echo $topic_id; ?>&page=<?php echo $page - 1; ?>">← Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?topic_id=<?php echo $topic_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?topic_id=<?php echo $topic_id; ?>&page=<?php echo $page + 1; ?>">Next →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="comment-list">
                <?php if (empty($comments)) : ?>
                    <div class="no-comments">
                        <p style="font-size: 48px; margin-bottom: 15px;">💬</p>
                        <p>No comments yet. Be the first to share your thoughts!</p>
                    </div>
                <?php else : ?>
                    <?php foreach ($comments as $comment) : ?>
                        <div class="comment-item fade-in">
                            <div class="comment-header">
                                <div class="comment-author">
                                    <div class="author-avatar">
                                        <?php 
                                        if (!empty($comment['profile_photo'])) {
                                            echo '<img src="' . htmlspecialchars($comment['profile_photo']) . '" alt="' . htmlspecialchars($comment['author']) . '" class="author-avatar">';
                                        } else {
                                            echo strtoupper(substr($comment['author'], 0, 1));
                                        }
                                        ?>
                                    </div>
                                    <?php echo htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <span class="comment-date">
                                    <?php 
                                        $commentDate = new DateTime($comment['date_posted'], new DateTimeZone('UTC'));
                                        $commentDate->setTimezone(new DateTimeZone('Asia/Manila'));
                                        echo $commentDate->format("M d, Y g:i A");
                                    ?>
                                </span>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                            <?php if ($_SESSION['user_id'] == $comment['user_id']) : ?>
                                <div class="comment-actions">
                                    <form method="POST">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <button type="submit" name="delete_comment" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this comment?');">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?topic_id=<?php echo $topic_id; ?>&page=<?php echo $page - 1; ?>">← Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?topic_id=<?php echo $topic_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?topic_id=<?php echo $topic_id; ?>&page=<?php echo $page + 1; ?>">Next →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="comment-form card">
                <h3 class="section-title">Join the Discussion</h3>
                <form method="POST" action="comment.php?topic_id=<?php echo $topic_id; ?>">
                    <input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>">
                    <input type="hidden" name="formToken" value="<?php echo $_SESSION['formToken']['comment-form']; ?>">
                    
                    <div class="form-group">
                        <label for="content" class="form-label">Your Comment</label>
                        <textarea name="content" id="content" rows="4" class="form-control" placeholder="Share your thoughts..." required></textarea>
                    </div>
                    
                    <button type="submit" name="submit_comment" class="btn btn-primary">
                        📤 Post Comment
                    </button>
                </form>
            </div>
        </div>

        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Dasaplus Discussions. All rights reserved.</p>
        </footer>
    </div>

    <script nonce="<?php echo $nonce; ?>">
        // Add subtle animations to page elements
        document.addEventListener('DOMContentLoaded', function() {
            const commentItems = document.querySelectorAll('.comment-item');
            commentItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Focus on comment textarea
            const commentTextarea = document.getElementById('content');
            if (commentTextarea) {
                commentTextarea.focus();
            }
            
            // Smooth scrolling for pagination
            document.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetUrl = this.href;
                    
                    document.body.style.opacity = '0.7';
                    document.body.style.transition = 'opacity 0.3s ease';
                    
                    setTimeout(() => {
                        window.location.href = targetUrl;
                    }, 300);
                });
            });
        });
    </script>
</body>
</html>

