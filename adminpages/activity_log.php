<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php");
    exit;
}

require '../config.php';

// Set default filter values
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_user = isset($_GET['user']) ? $_GET['user'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build the query with filters
$where_conditions = [];
$query_params = [];

if (!empty($filter_type)) {
    $where_conditions[] = "activity_type = ?";
    $query_params[] = $filter_type;
}

if (!empty($filter_user)) {
    $where_conditions[] = "username LIKE ?";
    $query_params[] = "%$filter_user%";
}

if (!empty($filter_date_from)) {
    $where_conditions[] = "DATE(created_at) >= ?";
    $query_params[] = $filter_date_from;
}

if (!empty($filter_date_to)) {
    $where_conditions[] = "DATE(created_at) <= ?";
    $query_params[] = $filter_date_to;
}

// Base query
$query = "
    SELECT 
        ua.*, 
        u.username,
        u.email
    FROM user_activity ua
    LEFT JOIN users u ON ua.user_id = u.id
";

// Add WHERE conditions if any
if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

// Order by
$query .= " ORDER BY ua.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);

if (!empty($query_params)) {
    $types = str_repeat('s', count($query_params));
    $stmt->bind_param($types, ...$query_params);
}

$stmt->execute();
$result = $stmt->get_result();

// Get activity types for filter dropdown
$activity_types = $conn->query("SELECT DISTINCT activity_type FROM user_activity ORDER BY activity_type")->fetch_all(MYSQLI_ASSOC);

// Get total count for stats
$total_activities = $conn->query("SELECT COUNT(*) as count FROM user_activity")->fetch_assoc()['count'];
$today_activities = $conn->query("SELECT COUNT(*) as count FROM user_activity WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
$unique_users = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM user_activity")->fetch_assoc()['count'];

// Get top activities
$top_activities = $conn->query("
    SELECT activity_type, COUNT(*) as count 
    FROM user_activity 
    GROUP BY activity_type 
    ORDER BY count DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Dasaplus Educational Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #003300;
            --primary-green-hover: #006400;
            --light-green: #e8f5e8;
            --text-dark: #222;
            --text-medium: #444;
            --text-light: #666;
            --background: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 6px 16px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
            --blue: #3498db;
            --purple: #9b59b6;
            --red: #e74c3c;
            --orange: #f39c12;
            --teal: #1abc9c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }

     

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
        }

        .breadcrumb a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: var(--transition);
        }

        .breadcrumb a:hover {
            opacity: 1;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary-green);
            display: flex;
            flex-direction: column;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--light-green);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            color: var(--primary-green);
            font-size: 24px;
        }

        .stat-content h3 {
            color: var(--text-medium);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 5px;
        }

        .stat-description {
            color: var(--text-light);
            font-size: 14px;
        }

        .content-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-green);
        }

        .section-header h2 {
            color: var(--primary-green);
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
            padding: 20px;
            background: var(--light-green);
            border-radius: 8px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--text-medium);
            font-size: 14px;
        }

        .form-group select,
        .form-group input {
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            background: white;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-green);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-green-hover);
        }

        .btn-secondary {
            background: #f1f1f1;
            color: var(--text-dark);
        }

        .btn-secondary:hover {
            background: #e5e5e5;
        }

        .actions-row {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            background-color: var(--light-green);
            color: var(--primary-green);
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        .data-table tr:hover {
            background-color: rgba(232, 245, 232, 0.5);
        }

        .activity-type {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .type-login { background: #e8f5e8; color: #2e7d32; }
        .type-logout { background: #ffebee; color: #c62828; }
        .type-upload { background: #e3f2fd; color: #1976d2; }
        .type-download { background: #fff3e0; color: #f57c00; }
        .type-view { background: #f3e5f5; color: #7b1fa2; }
        .type-comment { background: #e0f2f1; color: #00796b; }
        .type-like { background: #fce4ec; color: #ad1457; }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination a, .pagination span {
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }

        .pagination a {
            background: var(--light-green);
            color: var(--primary-green);
            transition: var(--transition);
        }

        .pagination a:hover, .pagination .current {
            background: var(--primary-green);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ccc;
        }

        .empty-state p {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .top-activities {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: var(--light-green);
            border-radius: 8px;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .activity-info {
            flex: 1;
        }

        .activity-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .activity-count {
            font-size: 14px;
            color: var(--text-light);
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .filter-form {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 15px;
            }
            
            .header h1 {
                font-size: 28px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-section {
                padding: 20px;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .data-table {
                font-size: 14px;
            }
            
            .data-table th, .data-table td {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>

<!-- Header Navigation -->
<?php include 'adminheader.php'; ?>

<!-- Main Content Area -->
<div class="container">
    

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-list-alt"></i></div>
            <div class="stat-content">
                <h3>Total Activities</h3>
                <div class="stat-number"><?php echo number_format($total_activities); ?></div>
                <p class="stat-description">All recorded user activities</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-content">
                <h3>Today's Activities</h3>
                <div class="stat-number"><?php echo number_format($today_activities); ?></div>
                <p class="stat-description">Activities recorded today</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <h3>Active Users</h3>
                <div class="stat-number"><?php echo number_format($unique_users); ?></div>
                <p class="stat-description">Users with recorded activity</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
            <div class="stat-content">
                <h3>Activity Types</h3>
                <div class="stat-number"><?php echo count($activity_types); ?></div>
                <p class="stat-description">Different types of activities</p>
            </div>
        </div>
    </div>

    <div class="two-column">
        <!-- Filters -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-filter"></i> Filter Activities</h2>
            </div>
            
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="type">Activity Type</label>
                    <select id="type" name="type">
                        <option value="">All Types</option>
                        <?php foreach ($activity_types as $type): ?>
                            <option value="<?php echo $type['activity_type']; ?>" <?php echo $filter_type == $type['activity_type'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst($type['activity_type']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="user">Username</label>
                    <input type="text" id="user" name="user" value="<?php echo htmlspecialchars($filter_user); ?>" placeholder="Search by username">
                </div>
                
                <div class="form-group">
                    <label for="date_from">Date From</label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo $filter_date_from; ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_to">Date To</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo $filter_date_to; ?>">
                </div>
                
                <div class="actions-row">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="activity_log.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Top Activities -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-chart-bar"></i> Top Activities</h2>
            </div>
            
            <div class="top-activities">
                <?php if (!empty($top_activities)): ?>
                    <?php foreach ($top_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-<?php echo get_activity_icon($activity['activity_type']); ?>"></i>
                            </div>
                            <div class="activity-info">
                                <div class="activity-name"><?php echo ucfirst($activity['activity_type']); ?></div>
                                <div class="activity-count"><?php echo number_format($activity['count']); ?> records</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No activity data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Activity Log Table -->
    <div class="content-section">
        <div class="section-header">
            <h2><i class="fas fa-table"></i> Activity Log</h2>
            <span>Showing <?php echo $result->num_rows; ?> records</span>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <div style="overflow-x: auto; max-height: 600px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Activity Type</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M j, Y H:i:s', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if (!empty($row['username'])): ?>
                                        <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                                        <br><small><?php echo htmlspecialchars($row['email']); ?></small>
                                    <?php else: ?>
                                        <em>Unknown User</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="activity-type type-<?php echo $row['activity_type']; ?>">
                                        <?php echo ucfirst($row['activity_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo !empty($row['activity_details']) ? htmlspecialchars(truncate_text($row['activity_details'], 100)) : 'No details'; ?></td>
                                <td><?php echo !empty($row['ip_address']) ? $row['ip_address'] : 'N/A'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination would go here if implemented -->
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No activities found matching your criteria</p>
                <a href="activity_log.php" class="btn btn-primary">
                    <i class="fas fa-refresh"></i> Reset Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Helper function to get icon for activity type
function get_activity_icon($type) {
    $icons = [
        'login' => 'sign-in-alt',
        'logout' => 'sign-out-alt',
        'upload' => 'cloud-upload',
        'download' => 'cloud-download',
        'view' => 'eye',
        'comment' => 'comment',
        'like' => 'heart'
    ];
    
    return $icons[$type] ?? 'history';
}

// Helper function to truncate text
function truncate_text($text, $length) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}
?>

<script>
    // Set date_to to today by default if not set
    document.addEventListener('DOMContentLoaded', function() {
        const dateTo = document.getElementById('date_to');
        if (!dateTo.value) {
            const today = new Date().toISOString().split('T')[0];
            dateTo.value = today;
        }
        
        // Set date_from to 7 days ago if not set
        const dateFrom = document.getElementById('date_from');
        if (!dateFrom.value) {
            const weekAgo = new Date();
            weekAgo.setDate(weekAgo.getDate() - 7);
            dateFrom.value = weekAgo.toISOString().split('T')[0];
        }
    });
</script>

</body>
</html>