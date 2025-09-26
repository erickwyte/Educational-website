<?php
require 'config.php';
include 'includes/session_check.php';

// CSRF Protection - Initialize if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch current user's profile information
$user_id = $_SESSION['user_id'];
$sql = "SELECT subscription_end, profile_photo, course, username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();
$stmt->close();

// TRACK VIEW ACTIVITY
$viewDetails = ($category === 'public') ? "Viewed Discussion Forum: All Courses" : "Viewed Discussion Forum: Course - " . htmlspecialchars($current_user['course']);
track_activity($user_id, ACTIVITY_VIEW, $viewDetails);

// Check if user has an active subscription
$has_active_subscription = false;
if ($current_user && !empty($current_user['subscription_end'])) {
    $expiry_date = strtotime($current_user['subscription_end']);
    $current_date = time();
    $days_left = ($expiry_date - $current_date) / 86400;
    $has_active_subscription = ($days_left > 0);
}

// Determine which category to show (default to course-specific)
$category = isset($_GET['category']) ? $_GET['category'] : 'course';
$category_filter = ($category === 'public') ? '' : $current_user['course'];

// Fetch discussion topics with pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total number of topics based on category
if ($category === 'public') {
    $count_sql = "SELECT COUNT(*) as total FROM discussion_topic dt 
                  JOIN users u ON dt.user_id = u.id";
    $count_stmt = $conn->prepare($count_sql);
} else {
    $count_sql = "SELECT COUNT(*) as total FROM discussion_topic dt 
                  JOIN users u ON dt.user_id = u.id 
                  WHERE u.course = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param('s', $category_filter);
}

if ($count_stmt) {
    if ($category !== 'public') {
        $count_stmt->bind_param('s', $category_filter);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_topics = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_topics / $per_page);
    $count_stmt->close();
} else {
    $total_topics = 0;
    $total_pages = 1;
}

// Fetch topics for current page with user profile information
if ($category === 'public') {
    $sql = "SELECT dt.id, dt.title, dt.content, dt.date_posted, 
                   u.username, u.course, u.profile_photo,
                   (SELECT COUNT(*) FROM discussion_comments WHERE topic_id = dt.id) as comment_count
            FROM discussion_topic dt 
            JOIN users u ON dt.user_id = u.id
            ORDER BY dt.date_posted DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $per_page, $offset);
} else {
    $sql = "SELECT dt.id, dt.title, dt.content, dt.date_posted, 
                   u.username, u.course, u.profile_photo,
                   (SELECT COUNT(*) FROM discussion_comments WHERE topic_id = dt.id) as comment_count
            FROM discussion_topic dt 
            JOIN users u ON dt.user_id = u.id
            WHERE u.course = ?
            ORDER BY dt.date_posted DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sii', $category_filter, $per_page, $offset);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
    $error_msg = "Error fetching discussions: " . $conn->error;
}

// At the top of discussion_forum.php, retrieve messages
$success_msg = $_SESSION['form_messages']['success'] ?? '';
$error_msg = $_SESSION['form_messages']['error'] ?? '';
$form_data = $_SESSION['form_messages']['form_data'] ?? ['title' => '', 'content' => ''];

// Clear messages after displaying
unset($_SESSION['form_messages']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Forum - Dasaplus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/discussion_forum.css">
    <style>
        .success-msg {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }

        .new-topic-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background-color: #003300;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #004d00;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="empty"></div>
    
    <div class="forum-container">
        <!-- Category Tabs -->
        <div class="category-tabs">
            <div class="category-tab <?php echo $category === 'course' ? 'active' : ''; ?>" 
                 onclick="window.location.href='?category=course'">
                <span class="category-indicator"><?php echo htmlspecialchars($current_user['course']); ?></span>
            </div>
            <div class="category-tab <?php echo $category === 'public' ? 'active' : ''; ?>" 
                 onclick="window.location.href='?category=public'">
                <span class="category-indicator">All Courses</span>
            </div>
        </div>
        
        <div class="discussion-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <a href="comment.php?topic_id=<?php echo $row['id']; ?>" class="discussion-link">
                        <div class="discussion-item">
                            <!-- User Profile Section -->
                            <div class="user-profile">
                                <div class="user-avatar">
                                    <?php if (!empty($row['profile_photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['profile_photo']); ?>" 
                                             alt="<?php echo htmlspecialchars($row['username']); ?>"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="user-avatar-placeholder" style="display: none;">
                                            <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="user-avatar-placeholder">
                                            <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <div class="username"><?php echo htmlspecialchars($row['username']); ?></div>
                                    <div class="user-course"><?php echo htmlspecialchars($row['course']); ?></div>
                                </div>
                            </div>

                            <div class="discussion-meta">
                                <span class="date">📅 <?php echo (new DateTime($row['date_posted']))->format('F j, Y, g:i a'); ?></span>
                            </div>
                            
                            <h4 class="discussion-title">📌 <?php echo htmlspecialchars($row['title']); ?></h4>
                            <p class="discussion-content"><?php echo nl2br(htmlspecialchars(substr($row['content'], 0, 300))); ?><?php echo strlen($row['content']) > 300 ? '...' : ''; ?></p>
                            
                            <div class="discussion-footer">
                                <div class="comment-count">
                                    <i class="fas fa-comments"></i> <?php echo $row['comment_count']; ?> comments
                                </div>
                                <span class="read-more">Read more →</span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-topics">
                    <p>No discussions found in this category. Be the first to start one!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?category=<?php echo $category; ?>&page=<?php echo $page - 1; ?>">&laquo; Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?category=<?php echo $category; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?category=<?php echo $category; ?>&page=<?php echo $page + 1; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add active state to category tabs
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const category = urlParams.get('category') || 'course';
            
            // Update active tab
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelector(`.category-tab:nth-child(${category === 'public' ? 2 : 1})`).classList.add('active');
        });
    </script>
    <!-- Footer -->
    <footer>
        &copy; 2025 Dasaplus. All rights reserved.
    </footer>
</body>
</html>