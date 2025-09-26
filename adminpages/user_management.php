<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_management_login.php");
    exit;
}

require '../config.php';

// Pagination setup
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter parameters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build the query with filters
$where_conditions = [];
if (!empty($search)) {
    $where_conditions[] = "(username LIKE '%$search%' OR email LIKE '%$search%' OR course LIKE '%$search%')";
}

if ($status_filter === 'active') {
    $where_conditions[] = "subscription_end > NOW()";
} elseif ($status_filter === 'expired') {
    $where_conditions[] = "(subscription_end IS NULL OR subscription_end <= NOW())";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
$count_result = $conn->query($count_query);
$total_users = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Get users with pagination
$users_query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$users_result = $conn->query($users_query);

// Get user statistics
$stats_query = $conn->query("
    SELECT 
        COUNT(*) as total_users,
        COUNT(CASE WHEN subscription_end > NOW() THEN 1 END) as active_users,
        COUNT(CASE WHEN subscription_end IS NULL OR subscription_end <= NOW() THEN 1 END) as expired_users
    FROM users
");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Dasaplus University</title>
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
            --gold: #FFD700;
            --blue: #3498db;
            --purple: #9b59b6;
            --red: #e74c3c;
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
            padding-top: 70px;
        }

        .empty {
            height: 20px;
        }

        /* Dashboard Styles */
        .dashboard-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-green) 0%, #004d00 100%);
            border-radius: 12px;
            color: white;
            box-shadow: var(--shadow);
        }

        .dashboard-header h1 {
            font-size: 32px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .dashboard-header h1 i {
            font-size: 36px;
            color: var(--gold);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 5px;
        }

        .stat-title {
            color: var(--text-medium);
            font-size: 16px;
        }

        /* Filter Section */
        .filter-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-medium);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 16px;
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

        /* Table Styles */
        .table-container {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th {
            background: var(--light-green);
            color: var(--primary-green);
            text-align: left;
            padding: 15px;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-medium);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background: var(--light-green);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-expired {
            background: #ffebee;
            color: #d32f2f;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 14px;
        }

        .btn-danger {
            background: var(--red);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-info {
            background: var(--blue);
            color: white;
        }

        .btn-info:hover {
            background: #2980b9;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 8px;
        }

        .page-item {
            display: inline-block;
        }

        .page-link {
            padding: 8px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .page-link:hover {
            background: var(--light-green);
        }

        .page-link.active {
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .form-group {
                min-width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 15px;
            }
            
            .dashboard-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 15px;
            }
            
            .dashboard-header h1 {
                font-size: 28px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 480px) {
            .dashboard-header h1 {
                font-size: 24px;
            }
            
            .stat-number {
                font-size: 28px;
            }
            
            .data-table th, 
            .data-table td {
                padding: 10px;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-hover);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-title {
            font-size: 24px;
            color: var(--primary-green);
        }

        .close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-light);
        }

        .user-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item {
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-medium);
            margin-bottom: 5px;
        }

        .detail-value {
            color: var(--text-dark);
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-card, .filter-section, .table-container {
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>
<body>

<!-- Header Navigation -->
<?php include 'adminheader.php'; ?>
<div class="empty"></div>

<!-- Main Content Area -->
<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-users-cog"></i> User Management</h1>
        <div>Manage all users and their subscriptions</div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
            <div class="stat-title">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['active_users']; ?></div>
            <div class="stat-title">Active Subscriptions</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['expired_users']; ?></div>
            <div class="stat-title">Expired Subscriptions</div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="search">Search Users</label>
                <input type="text" id="search" name="search" class="form-control" placeholder="Search by username, email, or course" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group">
                <label for="status">Subscription Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="">All Users</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active Subscriptions</option>
                    <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>Expired Subscriptions</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Apply Filters</button>
                <a href="user_management.php" class="btn btn-secondary"><i class="fas fa-times"></i> Clear Filters</a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="table-container">
        <h2>User List (<?php echo $total_users; ?> users found)</h2>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Subscription End</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users_result->num_rows > 0): ?>
                    <?php while ($user = $users_result->fetch_assoc()): 
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
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['course']); ?></td>
                        <td><?php echo $user['subscription_end'] ? date('M j, Y', strtotime($user['subscription_end'])) : 'Never'; ?></td>
                        <td>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-info btn-sm view-user" data-userid="<?php echo $user['id']; ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-danger btn-sm delete-user" data-userid="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No users found matching your criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" class="page-link">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" class="page-link">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- User Detail Modal -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">User Details</h3>
            <button class="close">&times;</button>
        </div>
        <div id="userDetails">
            <!-- User details will be loaded here via AJAX -->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Deletion</h3>
            <button class="close">&times;</button>
        </div>
        <div>
            <p>Are you sure you want to delete user <strong id="deleteUsername"></strong>? This action cannot be undone.</p>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn btn-secondary" id="cancelDelete">Cancel</button>
                <button class="btn btn-danger" id="confirmDelete">Delete User</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal functionality
    const userModal = document.getElementById('userModal');
    const deleteModal = document.getElementById('deleteModal');
    const closeButtons = document.querySelectorAll('.close');
    const cancelDelete = document.getElementById('cancelDelete');
    let currentUserId = null;

    // Open user detail modal
    document.querySelectorAll('.view-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-userid');
            fetchUserDetails(userId);
        });
    });

    // Open delete confirmation modal
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-userid');
            const username = this.getAttribute('data-username');
            document.getElementById('deleteUsername').textContent = username;
            currentUserId = userId;
            deleteModal.style.display = 'flex';
        });
    });

    // Close modals
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            userModal.style.display = 'none';
            deleteModal.style.display = 'none';
        });
    });

    cancelDelete.addEventListener('click', () => {
        deleteModal.style.display = 'none';
    });

    // Confirm delete
    document.getElementById('confirmDelete').addEventListener('click', () => {
        if (currentUserId) {
            window.location.href = `delete_user.php?id=${currentUserId}`;
        }
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === userModal) userModal.style.display = 'none';
        if (e.target === deleteModal) deleteModal.style.display = 'none';
    });

    // Fetch user details via AJAX
    function fetchUserDetails(userId) {
        fetch(`get_user_details.php?id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    const userDetails = document.getElementById('userDetails');
                    
                    userDetails.innerHTML = `
                        <div class="user-details">
                            <div class="detail-item">
                                <div class="detail-label">Username</div>
                                <div class="detail-value">${user.username}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">${user.email}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Phone Number</div>
                                <div class="detail-value">${user.phone_number}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Course</div>
                                <div class="detail-value">${user.course}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">User Type</div>
                                <div class="detail-value">${user.user_type}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Subscription End</div>
                                <div class="detail-value">${user.subscription_end ? new Date(user.subscription_end).toLocaleDateString() : 'Never'}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Account Created</div>
                                <div class="detail-value">${new Date(user.created_at).toLocaleDateString()}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Last Updated</div>
                                <div class="detail-value">${new Date(user.updated_at).toLocaleDateString()}</div>
                            </div>
                        </div>
                    `;
                    
                    userModal.style.display = 'flex';
                } else {
                    alert('Error loading user details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading user details');
            });
    }
</script>

</body>
</html>