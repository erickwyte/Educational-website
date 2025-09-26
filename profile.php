<?php
include 'includes/session_check.php';
require 'config.php';

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Fetch user details with proper error handling
$sql = "SELECT id, username, email, course, phone_number, profile_photo, subscription_end FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Database error: " . $conn->error);
    die("System error. Please try again later.");
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle case where user is not found
if (!$user) {
    session_destroy();
    header("Location: login.php?error=user_not_found");
    exit();
}

// Check subscription status
$days_left = null;
$subscription_status = 'inactive';
if (!empty($user['subscription_end'])) {
    $expiry_date = strtotime($user['subscription_end']);
    $current_date = time();
    $days_left = ($expiry_date - $current_date) / 86400;
    
    if ($days_left > 0) {
        $subscription_status = 'active';
    } elseif ($days_left > -30) {
        $subscription_status = 'expired';
    } else {
        $subscription_status = 'lapsed';
    }
}

// Initialize messages
$message = '';
$error = '';
$testimonial_message = '';
$testimonial_error = '';

// Handle topic deletion with CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_topic_id'])) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $error = "Security validation failed. Please try again.";
    } else {
        $topic_id = intval($_POST['delete_topic_id']);

        $sql_check = "SELECT id FROM discussion_topic WHERE id = ? AND user_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param('ii', $topic_id, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Begin transaction for atomic operations
            $conn->begin_transaction();
            
            try {
                $sql_delete_comments = "DELETE FROM discussion_comments WHERE topic_id = ?";
                $stmt_delete_comments = $conn->prepare($sql_delete_comments);
                $stmt_delete_comments->bind_param('i', $topic_id);
                $stmt_delete_comments->execute();
                $stmt_delete_comments->close();

                $sql_delete_topic = "DELETE FROM discussion_topic WHERE id = ?";
                $stmt_delete_topic = $conn->prepare($sql_delete_topic);
                $stmt_delete_topic->bind_param('i', $topic_id);
                $stmt_delete_topic->execute();
                $stmt_delete_topic->close();

                $conn->commit();
                $message = "Topic deleted successfully.";
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Topic deletion error: " . $e->getMessage());
                $error = "Error deleting topic. Please try again.";
            }
        } else {
            $error = "Unauthorized action.";
        }
        $stmt_check->close();
    }
}

// Fetch saved questions
$saved_questions = [];
$sql_saved = "SELECT qp.id, qp.title, qp.file_path 
              FROM user_saved_questions sq 
              JOIN questions_pdfs qp ON sq.question_id = qp.id 
              WHERE sq.user_id = ?";
$stmt_saved = $conn->prepare($sql_saved);
if ($stmt_saved) {
    $stmt_saved->bind_param('i', $user_id);
    $stmt_saved->execute();
    $saved_result = $stmt_saved->get_result();
    while ($row = $saved_result->fetch_assoc()) {
        $saved_questions[] = $row;
    }
    $stmt_saved->close();
}

// Fetch saved PDFs
$saved_pdfs = [];
$sql_pdfs = "SELECT notes_pdfs.id, notes_pdfs.title, notes_pdfs.file_path 
             FROM user_saved_pdfs 
             JOIN notes_pdfs ON user_saved_pdfs.pdf_id = notes_pdfs.id 
             WHERE user_saved_pdfs.user_id = ?";
$stmt_pdfs = $conn->prepare($sql_pdfs);
if ($stmt_pdfs) {
    $stmt_pdfs->bind_param("i", $user_id);
    $stmt_pdfs->execute();
    $pdfs_result = $stmt_pdfs->get_result();
    while ($row = $pdfs_result->fetch_assoc()) {
        $saved_pdfs[] = $row;
    }
    $stmt_pdfs->close();
}

// Handle saved question deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question_id']) && isset($_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $csrf_token) {
        $error = "Security validation failed.";
    } else {
        $question_id = intval($_POST['question_id']);

        $stmt = $conn->prepare("DELETE FROM user_saved_questions WHERE user_id = ? AND question_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $user_id, $question_id);
            if ($stmt->execute()) {
                header("Location: profile.php?msg=deleted");
                exit();
            } else {
                $error = "Error deleting question.";
            }
            $stmt->close();
        }
    }
}

// Handle saved PDF deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_pdf_id']) && isset($_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $csrf_token) {
        $error = "Security validation failed.";
    } else {
        $pdf_id = intval($_POST['delete_pdf_id']);

        $stmt = $conn->prepare("DELETE FROM user_saved_pdfs WHERE user_id = ? AND pdf_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $user_id, $pdf_id);
            if ($stmt->execute()) {
                header("Location: profile.php?msg=pdf_deleted");
                exit();
            } else {
                $error = "Error deleting PDF.";
            }
            $stmt->close();
        }
    }
}

// Handle testimonial submission with user association (without name field)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'], $_POST['university'])) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $testimonial_error = "Security validation failed. Please try again.";
    } else {
        $message_text = trim($_POST['message']);
        $university = trim($_POST['university']);
        $maxLength = 200;

        // Get user's name from their profile for the testimonial
        $user_sql = "SELECT username FROM users WHERE id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        $user_stmt->close();
        
        $name = $user_data['username'] ?? 'Anonymous';

        if (!empty($message_text) && !empty($university)) {
            if (strlen($message_text) > $maxLength) {
                $testimonial_error = "Your testimonial is too long. Please limit it to $maxLength characters.";
            } else {
                // Check if user already submitted a testimonial
                $check = $conn->prepare("SELECT id FROM testimonials WHERE user_id = ?");
                $check->bind_param("i", $user_id);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $testimonial_error = "You've already submitted a testimonial. Thank you!";
                } else {
                    $query = "INSERT INTO testimonials (message, name, university, user_id, status) VALUES (?, ?, ?, ?, 'pending')";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssi", $message_text, $name, $university, $user_id);
                    if ($stmt->execute()) {
                        $testimonial_message = "Thank you! Your testimonial has been submitted and is awaiting approval.";
                    } else {
                        $testimonial_error = "Error: Unable to submit your testimonial.";
                    }
                    $stmt->close();
                }
                $check->close();
            }
        } else {
            $testimonial_error = "Please fill in all fields.";
        }
    }
}


// Fetch user's discussion topics
$user_topics = [];
$sql_topics = "SELECT dt.id, dt.title, dt.content, dt.date_posted, 
               (SELECT COUNT(*) FROM discussion_comments WHERE topic_id = dt.id) AS reply_count 
               FROM discussion_topic dt 
               WHERE dt.user_id = ? 
               ORDER BY dt.date_posted DESC";
$stmt_topics = $conn->prepare($sql_topics);
if ($stmt_topics) {
    $stmt_topics->bind_param('i', $user_id);
    $stmt_topics->execute();
    $topics_result = $stmt_topics->get_result();
    while ($row = $topics_result->fetch_assoc()) {
        $user_topics[] = $row;
    }
    $stmt_topics->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Dasaplus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <!-- Profile Photo Section -->
            <div class="profile-photo-section">
                <div class="profile-photo-container">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo" class="profile-photo"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="profile-photo-placeholder" style="display: none;">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    <?php else: ?>
                        <div class="profile-photo-placeholder">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="edit_profile.php" class="edit-profile-btn">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>
            </div>

            <!-- Profile Details -->
            <div class="profile-details">
                <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?></h2>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($user['course']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></p>
                <p><strong>Subscription ends on:</strong> <?php echo htmlspecialchars($user['subscription_end']); ?></p>
                
                <?php if ($subscription_status === 'active' && $days_left <= 5) : ?>
                    <div class="subscription-warning">
                        ⚠️ Your subscription expires in <strong><?php echo floor($days_left); ?></strong> days!
                        <a href="renew_subscription.php" class="renew-btn">Renew Now</a>
                    </div>
                <?php elseif ($subscription_status === 'expired') : ?>
                    <div class="subscription-expired">
                        ❌ Your subscription has expired!
                        <a href="renew_subscription.php" class="renew-btn">Renew Now</a>
                    </div>
                <?php elseif ($subscription_status === 'lapsed') : ?>
                    <div class="subscription-lapsed">
                        ⏳ Your subscription lapsed a while ago
                        <a href="renew_subscription.php" class="renew-btn">Renew Now</a>
                    </div>
                <?php elseif ($subscription_status === 'inactive') : ?>
                    <div class="subscription-warning">
                        ℹ️ You don't have an active subscription
                        <a href="renew_subscription.php" class="renew-btn">Subscribe Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Display Messages -->
        <?php if (!empty($message)) : ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)) : ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($testimonial_message)) : ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($testimonial_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($testimonial_error)) : ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($testimonial_error); ?>
            </div>
        <?php endif; ?>

        <!-- Saved Questions -->
        <div class="toggle-section" onclick="toggleSection('question-section')">
            <i class="fas fa-question-circle"></i> Your Saved Questions
        </div>
        <div id="question-section" class="toggle-content">
            <?php if (!empty($saved_questions)) : ?>
                <div class="saved-questions-container">
                    <?php foreach ($saved_questions as $saved_question) : ?>
                        <div class="saved-question-block">
                            <h4>
                                <a href="view_questions.php?id=<?php echo $saved_question['id']; ?>">
                                    <?php echo htmlspecialchars($saved_question['title']); ?>
                                </a>
                            </h4>
                            <form action="profile.php" method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" name="question_id" value="<?php echo $saved_question['id']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this saved question?');" class="delete-button">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p>No saved questions yet.</p>
            <?php endif; ?>
        </div>

        <!-- Saved PDFs -->
        <div class="toggle-section" onclick="toggleSection('pdf-section')">
            <i class="fas fa-file-pdf"></i> Your Saved PDFs
        </div>
        <div id="pdf-section" class="toggle-content">
            <?php if (!empty($saved_pdfs)) : ?>
                <ul class="saved-pdfs-list">
                    <?php foreach ($saved_pdfs as $pdf) : ?>
                        <li>
                            <a href="view_note.php?id=<?php echo $pdf['id']; ?>">
                                <h5><?php echo htmlspecialchars($pdf['title']); ?></h5>
                            </a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" name="delete_pdf_id" value="<?php echo $pdf['id']; ?>">
                                <button type="submit" onclick="return confirm('Delete this PDF?');" class="delete-button">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>No saved PDFs.</p>
            <?php endif; ?>
        </div>
        
<hr/>

<!-- Testimonials -->
<div class="testimonials">
    <h3>Share Your Experience</h3>
    <form id="testimonialForm" action="" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <textarea name="message" id="testimonialMessage" placeholder="Share your experience with our platform..." maxlength="200" required></textarea>
        <small id="charCounter">0 / 200</small>
        <input type="text" name="university" placeholder="Your university" required>
        <button type="submit">Submit Testimonial</button>
    </form>
</div>

<hr/>

      <!-- New Topic Form -->
        <div class="new-topic-form">
            <h2>Start a New Discussion</h2>
            
            <?php if (!empty($success_msg)): ?>
                <div class="success-msg"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_msg)): ?>
                <div class="error-msg"><?php echo $error_msg; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="discussion_topic_handler.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="form-group">
                    <label for="title">Discussion Title</label>
                    <input type="text" id="title" name="title" 
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                           placeholder="Enter a title for your discussion" required maxlength="200">
                </div>
                
                <div class="form-group">
                    <label for="content">Discussion Content</label>
                    <textarea id="content" name="content" 
                              placeholder="Share your thoughts, questions, or ideas..." 
                              required maxlength="5000"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                </div>
                
                <button type="submit" name="submit_topic" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Post Discussion
                </button>
            </form>
        </div>
            <hr/>

        <!-- User's Discussion Topics -->
        <div class="topics-list">
            <h3>Your Discussion Topics</h3>
            <?php if (!empty($user_topics)) : ?>
                <?php foreach ($user_topics as $topic) : ?>
                    <div class='topic-item'>
                        <div class='topic-content'>
                            <a href='comment.php?topic_id=<?php echo $topic['id']; ?>' class='topic-link'>
                                <h4><?php echo htmlspecialchars($topic['title']); ?></h4>
                                <p class='meta'>Posted on: <?php echo (new DateTime($topic['date_posted']))->format('F j, Y, g:i a'); ?> | Replies: <?php echo $topic['reply_count']; ?></p>
                            </a>
                        </div>
                        <form method='POST' onsubmit='return confirmDelete();' class='delete-form'>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type='hidden' name='delete_topic_id' value='<?php echo $topic['id']; ?>'>
                            <button type='submit' class='delete-btn'><i class='fas fa-trash'></i> Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>You haven't posted any topics yet.</p>
            <?php endif; ?>
        </div>
    </div>


    <!-- Footer -->
<footer>
  &copy; 2025 Dasaplus. All rights reserved.
</footer>

    <script>
        function toggleSection(id) {
            const section = document.getElementById(id);
            if (section.style.display === "none" || section.style.display === "") {
                section.style.display = "block";
            } else {
                section.style.display = "none";
            }
        }

        function confirmDelete() {
            return confirm("Are you sure you want to delete this topic? This action cannot be undone.");
        }

        document.addEventListener('DOMContentLoaded', function () {
            const textarea = document.getElementById('testimonialMessage');
            const counter = document.getElementById('charCounter');
            const maxChars = 200;

            if (textarea && counter) {
                textarea.addEventListener('input', function () {
                    const currentLength = textarea.value.length;
                    counter.textContent = `${currentLength} / ${maxChars}`;
                    if (currentLength >= maxChars - 20) {
                        counter.style.color = '#DC2626';
                    } else {
                        counter.style.color = '#444';
                    }
                    if (currentLength > maxChars) {
                        textarea.value = textarea.value.substring(0, maxChars);
                    }
                });

                document.getElementById('testimonialForm').addEventListener('submit', function (e) {
                    if (textarea.value.length > maxChars) {
                        e.preventDefault();
                        alert(`Your testimonial exceeds the ${maxChars} character limit.`);
                    }
                });
            }

            // Auto-hide messages after 5 seconds
            const messages = document.querySelectorAll('.success-message, .error-message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 600);
                }, 5000);
            });
        });
    </script>
    <script>
        // Character counter for textarea
        const contentTextarea = document.getElementById('content');
        const charLimit = 5000;

        if (contentTextarea) {
            // Create character counter element
            const charCounter = document.createElement('small');
            charCounter.className = 'char-counter';
            charCounter.style.display = 'block';
            charCounter.style.textAlign = 'right';
            charCounter.style.color = '#666';
            charCounter.style.marginTop = '0.5rem';
            charCounter.textContent = `${charLimit} characters remaining`;
            contentTextarea.parentNode.insertBefore(charCounter, contentTextarea.nextSibling);

            contentTextarea.addEventListener('input', function() {
                const remaining = charLimit - this.value.length;
                charCounter.textContent = `${remaining} characters remaining`;
                
                if (remaining < 100) {
                    charCounter.style.color = '#DC2626';
                } else {
                    charCounter.style.color = '#666';
                }
            });
        }

        // Auto-resize textarea
        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }

        if (contentTextarea) {
            contentTextarea.addEventListener('input', function() {
                autoResize(this);
            });
            // Initial resize
            autoResize(contentTextarea);
        }
    </script>
</body>
</html>
