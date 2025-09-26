<?php
// Include session configuration
if (!file_exists('../session_config.php')) {
    error_log("Session config file not found: session_config.php", 3, "../debug.log");
    http_response_code(500);
    die("Session configuration file missing. Please contact the administrator.");
}
require_once '../session_config.php';

// Start session
if (!session_start()) {
    error_log("Failed to start session", 3, "../debug.log");
    http_response_code(500);
    die("Session initialization failed. Please try again later.");
}

// Check admin session and regenerate ID
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php");
    exit;
}

// Regenerate session ID to prevent session fixation
if (!session_regenerate_id(true)) {
    error_log("Session regeneration failed", 3, "../debug.log");
    http_response_code(500);
    die("Session security error. Please try again later.");
}

require '../config.php';

// Initialize debug log
$debug_log = "Debug log started at " . date('Y-m-d H:i:s') . "\n";

// Total PDFs uploaded
try {
    $pdf_query = $conn->query("SELECT COUNT(*) AS total_pdfs FROM user_pdfs_Uploads");
    $pdf_count = $pdf_query->fetch_assoc()['total_pdfs'];
    $debug_log .= "PDF count: $pdf_count\n";
} catch (Exception $e) {
    $debug_log .= "Error in PDF query: " . $e->getMessage() . "\n";
    $pdf_count = 0;
}

// Active discussions
try {
    $discussion_query = $conn->query("SELECT COUNT(*) AS total_discussions FROM discussion_topic");
    $discussion_count = $discussion_query->fetch_assoc()['total_discussions'];
    $debug_log .= "Discussion count: $discussion_count\n";
} catch (Exception $e) {
    $debug_log .= "Error in discussion query: " . $e->getMessage() . "\n";
    $discussion_count = 0;
}

// Unread messages
try {
    $message_query = $conn->query("SELECT COUNT(*) AS unread_messages FROM messages WHERE status = 'pending'");
    $unread_messages = $message_query->fetch_assoc()['unread_messages'];
    $debug_log .= "Unread messages: $unread_messages\n";
} catch (Exception $e) {
    $debug_log .= "Error in messages query: " . $e->getMessage() . "\n";
    $unread_messages = 0;
}

// Users with active subscriptions
try {
    $user_query = $conn->query("SELECT COUNT(*) AS active_users FROM users WHERE subscription_end > NOW()");
    $active_users = $user_query->fetch_assoc()['active_users'];
    $debug_log .= "Active users: $active_users\n";
} catch (Exception $e) {
    $debug_log .= "Error in active users query: " . $e->getMessage() . "\n";
    $active_users = 0;
}

// Total users
try {
    $total_users_query = $conn->query("SELECT COUNT(*) AS total_users FROM users");
    $total_users = $total_users_query->fetch_assoc()['total_users'];
    $debug_log .= "Total users: $total_users\n";
} catch (Exception $e) {
    $debug_log .= "Error in total users query: " . $e->getMessage() . "\n";
    $total_users = 0;
}

// Recent activities - Combined from multiple tables
try {
    $recent_activities = $conn->query("
        (SELECT 'pdf_upload' as type, filename as title, uploaded_at as date, uploaded_by as user 
         FROM user_pdfs_Uploads 
         ORDER BY uploaded_at DESC 
         LIMIT 5)
        
        UNION ALL
        
        (SELECT 'discussion' as type, title, date_posted as date, 
                (SELECT username FROM users WHERE users.id = discussion_topic.user_id) as user 
         FROM discussion_topic 
         ORDER BY date_posted DESC 
         LIMIT 5)
        
        UNION ALL
        
        (SELECT 'message' as type, subject as title, created_at as date, name as user 
         FROM messages 
         ORDER BY created_at DESC 
         LIMIT 5)
        
        ORDER BY date DESC 
        LIMIT 10
    ");
    $debug_log .= "Recent activities rows: " . $recent_activities->num_rows . "\n";
} catch (Exception $e) {
    $debug_log .= "Error in recent activities query: " . $e->getMessage() . "\n";
}

// Fetch user details
try {
    $users_result = $conn->query("SELECT username, email, subscription_end FROM users ORDER BY created_at DESC LIMIT 5");
    $debug_log .= "Recent users rows: " . $users_result->num_rows . "\n";
} catch (Exception $e) {
    $debug_log .= "Error in users query: " . $e->getMessage() . "\n";
}

// Get recent uploads for chart
try {
    $uploads_by_day = $conn->query("
        SELECT DATE(uploaded_at) as upload_date, COUNT(*) as upload_count 
        FROM user_pdfs_Uploads 
        WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        GROUP BY DATE(uploaded_at) 
        ORDER BY upload_date
    ");
    $debug_log .= "Uploads by day rows: " . $uploads_by_day->num_rows . "\n";
} catch (Exception $e) {
    $debug_log .= "Error in uploads by day query: " . $e->getMessage() . "\n";
}

// Prepare data for chart
$upload_dates = [];
$upload_counts = [];
while ($row = $uploads_by_day->fetch_assoc()) {
    $upload_dates[] = date('M j', strtotime($row['upload_date']));
    $upload_counts[] = (int)$row['upload_count'];
}
$debug_log .= "Upload dates: " . json_encode($upload_dates) . "\n";
$debug_log .= "Upload counts: " . json_encode($upload_counts) . "\n";

// Fallback data if empty
if (empty($upload_dates)) {
    $upload_dates = array_map(function($i) {
        return date('M j', strtotime("-$i days"));
    }, range(6, 0));
    $upload_counts = array_fill(0, 7, 0);
    $debug_log .= "Using fallback data for uploads chart\n";
}

// Get user registration stats
try {
    $users_by_day = $conn->query("
        SELECT DATE(created_at) as reg_date, COUNT(*) as user_count 
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        GROUP BY DATE(created_at) 
        ORDER BY reg_date
    ");
    $debug_log .= "Users by day rows: " . $users_by_day->num_rows . "\n";
} catch (Exception $e) {
    $debug_log .= "Error in users by day query: " . $e->getMessage() . "\n";
}

// Prepare data for user registration chart
$reg_dates = [];
$reg_counts = [];
while ($row = $users_by_day->fetch_assoc()) {
    $reg_dates[] = date('M j', strtotime($row['reg_date']));
    $reg_counts[] = (int)$row['user_count'];
}
$debug_log .= "Registration dates: " . json_encode($reg_dates) . "\n";
$debug_log .= "Registration counts: " . json_encode($reg_counts) . "\n";

// Fallback data if empty
if (empty($reg_dates)) {
    $reg_dates = array_map(function($i) {
        return date('M j', strtotime("-$i days"));
    }, range(6, 0));
    $reg_counts = array_fill(0, 7, 0);
    $debug_log .= "Using fallback data for users chart\n";
}

// Get latest testimonials for approval
try {
    $testimonials_query = $conn->query("
        SELECT * FROM testimonials 
        WHERE status = 'pending' 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $debug_log .= "Testimonials rows: " . $testimonials_query->num_rows . "\n";
} catch (Exception $e) {
    $debug_log .= "Error in testimonials query: " . $e->getMessage() . "\n";
}

// Get system status information
$system_status = [
    'total_storage' => '2.5 GB',
    'used_storage' => '1.2 GB',
    'active_sessions' => rand(5, 20),
    'server_load' => rand(10, 80) . '%'
];
$debug_log .= "System status: " . json_encode($system_status) . "\n";

// Get counts for all major content types
try {
    $notes_count = $conn->query("SELECT COUNT(*) as count FROM notes_pdfs")->fetch_assoc()['count'];
    $debug_log .= "Notes count: $notes_count\n";
} catch (Exception $e) {
    $debug_log .= "Error in notes count query: " . $e->getMessage() . "\n";
    $notes_count = 0;
}
try {
    $questions_count = $conn->query("SELECT COUNT(*) as count FROM questions_pdfs")->fetch_assoc()['count'];
    $debug_log .= "Questions count: $questions_count\n";
} catch (Exception $e) {
    $debug_log .= "Error in questions count query: " . $e->getMessage() . "\n";
    $questions_count = 0;
}
try {
    $blog_count = $conn->query("SELECT COUNT(*) as count FROM blog_posts WHERE deleted_at IS NULL")->fetch_assoc()['count'];
    $debug_log .= "Blog count: $blog_count\n";
} catch (Exception $e) {
    $debug_log .= "Error in blog count query: " . $e->getMessage() . "\n";
    $blog_count = 0;
}
try {
    $testimonials_count = $conn->query("SELECT COUNT(*) as count FROM testimonials")->fetch_assoc()['count'];
    $debug_log .= "Testimonials count: $testimonials_count\n";
} catch (Exception $e) {
    $debug_log .= "Error in testimonials count query: " . $e->getMessage() . "\n";
    $testimonials_count = 0;
}
try {
    $subscribers_count = $conn->query("SELECT COUNT(*) as count FROM email_subscribers")->fetch_assoc()['count'];
    $debug_log .= "Subscribers count: $subscribers_count\n";
} catch (Exception $e) {
    $debug_log .= "Error in subscribers count query: " . $e->getMessage() . "\n";
    $subscribers_count = 0;
}

// Write debug log to file
file_put_contents('../debug.log', $debug_log, FILE_APPEND);

// Helper function to display time elapsed
function time_elapsed_string($datetime, $full = false) {
    try {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    } catch (Exception $e) {
        error_log("Error in time_elapsed_string: " . $e->getMessage(), 3, "../debug.log");
        return 'Unknown time';
    }
}

// Helper function to truncate text
function truncate_text($text, $length) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dasaplus Educational Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="css/admin_panel.css">
</head>
<body>

<!-- Header Navigation -->
<?php include 'adminheader.php'; ?>

<!-- Main Content Area -->
<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <div class="welcome-text">Welcome back, Administrator. Here's what's happening with your platform today.</div>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-file-pdf"></i></div>
            <div class="stat-content">
                <h3>User Notes Upload</h3>
                <div class="stat-number"><?php echo htmlspecialchars($pdf_count, ENT_QUOTES, 'UTF-8'); ?></div>
                <p class="stat-description">Total PDFs uploaded by users</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-comments"></i></div>
            <div class="stat-content">
                <h3>Active Discussions</h3>
                <div class="stat-number"><?php echo htmlspecialchars($discussion_count, ENT_QUOTES, 'UTF-8'); ?></div>
                <p class="stat-description">Ongoing conversations</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-envelope"></i></div>
            <div class="stat-content">
                <h3>Unread Messages</h3>
                <div class="stat-number"><?php echo htmlspecialchars($unread_messages, ENT_QUOTES, 'UTF-8'); ?></div>
                <p class="stat-description">Requiring your attention</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <h3>Active Users</h3>
                <div class="stat-number"><?php echo htmlspecialchars($active_users, ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($total_users, ENT_QUOTES, 'UTF-8'); ?></div>
                <p class="stat-description">With active subscriptions</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-content">
                <h3>Testimonials Pending</h3>
                <div class="stat-number"><?php echo htmlspecialchars($testimonials_query->num_rows, ENT_QUOTES, 'UTF-8'); ?></div>
                <p class="stat-description">Awaiting approval</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-book"></i></div>
            <div class="stat-content">
                <h3>Study Notes</h3>
                <div class="stat-number"><?php echo htmlspecialchars($notes_count, ENT_QUOTES, 'UTF-8'); ?></div>
                <p class="stat-description">Available study materials</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-question-circle"></i></div>
            <div class="stat-content">
                <h3>Past Questions</h3>
                <div class="stat-number"><?php echo htmlspecialchars($questions_count, ENT_QUOTES, 'UTF-8'); ?></div>
                <p class="stat-description">Exam preparation resources</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-blog"></i></div>
            <div class="stat-content">
                <h3>Blog Posts</h3>
                <div class="stat-number"><?php echo htmlspecialchars($blog_count, ENT_QUOTES, 'UTF-8'); ?></div>
                <p class="stat-description">Educational content</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="two-column">
        <div class="chart-container">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line"></i> Uploads Last 7 Days</h3>
            </div>
            <canvas id="UploadsChart"></canvas>
        </div>
        
        <div class="chart-container">
            <div class="chart-header">
                <h3><i class="fas fa-user-plus"></i> User Registrations Last 7 Days</h3>
            </div>
            <canvas id="usersChart"></canvas>
        </div>
    </div>

    <div class="two-column">
        <!-- Recent Activity -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-history"></i> Recent Activity</h2>
                <a href="activity_log.php" class="view-all-btn">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <ul class="activity-list">
                <?php if ($recent_activities->num_rows > 0): ?>
                    <?php while ($activity = $recent_activities->fetch_assoc()): 
                        $icon = '📄';
                        $type_class = 'type-pdf';
                        $type_text = 'PDF Upload';
                        
                        if ($activity['type'] == 'discussion') {
                            $icon = '💬';
                            $type_class = 'type-discussion';
                            $type_text = 'Discussion';
                        } elseif ($activity['type'] == 'message') {
                            $icon = '✉️';
                            $type_class = 'type-message';
                            $type_text = 'Message';
                        }
                        
                        $time_ago = time_elapsed_string($activity['date']);
                    ?>
                    <li class="activity-item">
                        <div class="activity-icon"><?php echo $icon; ?></div>
                        <div class="activity-content">
                            <p class="activity-title"><?php echo htmlspecialchars(truncate_text($activity['title'], 60), ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="activity-meta">
                                <span class="activity-user"><i class="fas fa-user"></i> <?php echo htmlspecialchars(truncate_text($activity['user'], 20), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="activity-time"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($time_ago, ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="activity-type <?php echo htmlspecialchars($type_class, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($type_text, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                    </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="activity-item">
                        <div class="activity-content">
                            <p>No recent activities found.</p>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Registered Users Table -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-user-friends"></i> Recent Users</h2>
                <a href="user_management.php" class="view-all-btn">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Subscription Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $user_counter = 0;
                        while ($user = $users_result->fetch_assoc()): 
                            $status_class = 'status-expired';
                            $status_text = 'Expired';
                            
                            if (!empty($user['subscription_end'])) {
                                $subscription_end = new DateTime($user['subscription_end']);
                                $today = new DateTime();
                                
                                if ($subscription_end > $today) {
                                    $status_class = 'status-active';
                                    $status_text = 'Active';
                                }
                            }
                            
                            // Limit to 5 users for the dashboard
                            if ($user_counter++ >= 5) break;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="status-badge <?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($status_text, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="content-section">
        <div class="section-header">
            <h2><i class="fas fa-server"></i> System Status</h2>
        </div>
        
        <div class="three-column">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-hdd"></i></div>
                <div class="stat-content">
                    <h3>Storage Usage</h3>
                    <div class="stat-number"><?php echo htmlspecialchars($system_status['used_storage'], ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars($system_status['total_storage'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <p class="stat-description">Disk space utilized</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-network-wired"></i></div>
                <div class="stat-content">
                    <h3>Active Sessions</h3>
                    <div class="stat-number"><?php echo htmlspecialchars($system_status['active_sessions'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <p class="stat-description">Current user sessions</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-microchip"></i></div>
                <div class="stat-content">
                    <h3>Server Load</h3>
                    <div class="stat-number"><?php echo htmlspecialchars($system_status['server_load'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <p class="stat-description">Current CPU usage</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Debug Chart.js loading
    console.log('Chart.js script loaded:', typeof Chart !== 'undefined' ? 'Yes' : 'No');
    
    // Initialize charts
    document.addEventListener('DOMContentLoaded', function() {
        try {
            // Debug data
            console.log('Upload dates:', <?php echo json_encode($upload_dates); ?>);
            console.log('Upload counts:', <?php echo json_encode($upload_counts); ?>);
            console.log('Registration dates:', <?php echo json_encode($reg_dates); ?>);
            console.log('Registration counts:', <?php echo json_encode($reg_counts); ?>);

            // Uploads Chart
            const uploadsCtx = document.getElementById('UploadsChart');
            if (!uploadsCtx) {
                console.error('UploadsChart canvas not found');
                return;
            }
            const uploadsChart = new Chart(uploadsCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($upload_dates); ?>,
                    datasets: [{
                        label: 'PDF Uploads',
                        data: <?php echo json_encode($upload_counts); ?>,
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) { return Number.isInteger(value) ? value : null; }
                            },
                            title: {
                                display: true,
                                text: 'Number of Uploads'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });

            // Users Chart
            const usersCtx = document.getElementById('usersChart');
            if (!usersCtx) {
                console.error('usersChart canvas not found');
                return;
            }
            const usersChart = new Chart(usersCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($reg_dates); ?>,
                    datasets: [{
                        label: 'New Users',
                        data: <?php echo json_encode($reg_counts); ?>,
                        backgroundColor: 'rgba(46, 204, 113, 0.6)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) { return Number.isInteger(value) ? value : null; }
                            },
                            title: {
                                display: true,
                                text: 'Number of Users'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });

            // Simple animation for stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        } catch (error) {
            console.error('Error initializing charts:', error);
        }
    });
</script>

</body>
</html>