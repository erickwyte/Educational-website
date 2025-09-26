<?php
session_start();
// Database connection
require 'config.php';

// Debug database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch categories
$categories = $conn->query("SELECT * FROM questions_categories");

// Fetch universities
$universities = $conn->query("SELECT * FROM universities");

// Initialize filter variables
$selected_category = isset($_GET['category']) ? intval($_GET['category']) : null;
$selected_university = isset($_GET['university']) ? intval($_GET['university']) : null;

// Build WHERE clause for filtering
$whereClause = "";
$params = [];

if ($selected_category) {
    $whereClause = "WHERE category_id = ?";
    $params[] = $selected_category;
}

if ($selected_university) {
    if ($whereClause) {
        $whereClause .= " AND university_id = ?";
    } else {
        $whereClause = "WHERE university_id = ?";
    }
    $params[] = $selected_university;
}

// Prepare and execute query with parameters
$query = "SELECT * FROM questions_pdfs $whereClause ORDER BY uploaded_at DESC";
$stmt = $conn->prepare($query);

if ($params) {
    $types = str_repeat('i', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$questions = $stmt->get_result();

// Debugging
if (!$questions) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questions - Dasaplus</title>
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
            height: 100vh;
            overflow: hidden; /* Prevent body scrolling */
        }

        .empty {
            height: 10px;
        }

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            gap: 30px;
            height: calc(100vh - 70px); /* Full height minus header and padding */
        }

        /* Sidebar for Filters - Desktop */
        .sidebar {
            flex: 0 0 280px;
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            height: 100%; /* Full height of container */
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Contain the scrolling */
        }

        .sidebar-content {
            overflow-y: auto; /* Enable scrolling for sidebar content */
            height: 100%; /* Take full height of sidebar */
            padding-right: 5px; /* Space for scrollbar */
        }

        .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: var(--light-green);
            border-radius: 10px;
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: var(--primary-green);
            border-radius: 10px;
        }

        .filter-section {
            background: var(--light-green);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .filter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background-color: var(--primary-green);
            color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-header:hover {
            background-color: var(--primary-green-hover);
        }

        .filter-header h2 {
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .filter-header i {
            transition: var(--transition);
        }

        .filter-header.active i {
            transform: rotate(180deg);
        }

        .filter-list {
            list-style: none;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
        }

        .filter-list.active {
            max-height: 300px;
            overflow-y: auto; /* Enable scrolling for long lists */
        }

        .filter-list::-webkit-scrollbar {
            width: 4px;
        }

        .filter-list::-webkit-scrollbar-thumb {
            background: var(--primary-green);
            border-radius: 10px;
        }

        .filter-item {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .filter-item:last-child {
            border-bottom: none;
        }

        .filter-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            text-decoration: none;
            color: var(--text-dark);
            transition: var(--transition);
            font-weight: 500;
        }

        .filter-link:hover {
            background-color: rgba(0, 80, 0, 0.1);
            color: var(--primary-green);
        }

        .filter-link.active-filter {
            background-color: var(--primary-green);
            color: white;
        }

        .filter-link i {
            font-size: 14px;
            width: 20px;
            text-align: center;
        }

        /* Questions Section */
        .questions-section {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            height: 100%; /* Full height of container */
        }

        .questions-content {
            overflow-y: auto; /* Enable scrolling for questions content */
            flex: 1; /* Take remaining space */
            padding-right: 5px; /* Space for scrollbar */
        }

        .questions-content::-webkit-scrollbar {
            width: 6px;
        }

        .questions-content::-webkit-scrollbar-track {
            background: var(--light-green);
            border-radius: 10px;
        }

        .questions-content::-webkit-scrollbar-thumb {
            background: var(--primary-green);
            border-radius: 10px;
        }

        .questions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .questions-header h1 {
            color: var(--primary-green);
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
        }

        .questions-header h1 i {
            font-size: 32px;
        }

        /* Search Container */
        .search-container {
            position: relative;
            max-width: 500px;
            width: 100%;
        }

        .search-container input {
            width: 100%;
            padding: 14px 20px 14px 50px;
            font-size: 16px;
            border: 2px solid var(--border-color);
            border-radius: 30px;
            outline: none;
            transition: var(--transition);
            background-color: var(--card-bg);
        }

        .search-container input:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(0, 80, 0, 0.1);
        }

        .search-container i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 18px;
        }

        /* Active Filters */
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .active-filter-tag {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--light-green);
            color: var(--primary-green);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .active-filter-tag .remove-filter {
            background: none;
            border: none;
            color: var(--primary-green);
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            width: 20px;
            height: 20px;
        }

        .active-filter-tag .remove-filter:hover {
            background: var(--primary-green);
            color: white;
        }

        /* Questions List */
        .questions-list {
            display: grid;
            gap: 20px;
        }

        .question-item {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary-green);
            position: relative;
            overflow: hidden;
        }

        .question-item:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-3px);
        }

        .question-item a {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .question-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            line-height: 1.4;
        }

        .question-title i {
            font-size: 22px;
            flex-shrink: 0;
        }

        .question-meta {
            color: var(--text-light);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .question-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
            background-color: var(--light-green);
            padding: 6px 12px;
            border-radius: 18px;
            font-weight: 500;
        }

        .question-date {
            margin-left: auto;
            font-style: italic;
        }

        /* No results message */
        .no-results {
            text-align: center;
            padding: 60px 40px;
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            color: var(--text-light);
        }

        .no-results i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--primary-green);
            opacity: 0.7;
        }

        .no-results h3 {
            color: var(--primary-green);
            margin-bottom: 15px;
            font-size: 24px;
        }

        .no-results p {
            margin-bottom: 25px;
            font-size: 16px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .clear-filters {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-green);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .clear-filters:hover {
            background: var(--primary-green-hover);
            transform: translateY(-2px);
        }

        /* Mobile Filters Toggle */
        .mobile-filters-toggle {
            display: none;
            background-color: var(--primary-green);
            color: white;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            cursor: pointer;
            font-weight: 600;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }

        .mobile-filters-toggle i {
            transition: var(--transition);
        }

        .mobile-filters-toggle.active i {
            transform: rotate(180deg);
        }

        .mobile-filters {
            display: none;
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        .mobile-filters.active {
            display: block;
        }

        .mobile-filters::-webkit-scrollbar {
            width: 6px;
        }

        .mobile-filters::-webkit-scrollbar-thumb {
            background: var(--primary-green);
            border-radius: 10px;
        }

        /* Loading animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .question-item {
            animation: fadeIn 0.5s ease;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-container {
                gap: 25px;
            }
            
            .sidebar {
                flex: 0 0 260px;
            }
            
            .question-title {
                font-size: 18px;
            }
        }

        @media (max-width: 992px) {
            .main-container {
                gap: 20px;
            }
            
            .sidebar {
                flex: 0 0 240px;
            }
            
            .questions-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-container {
                max-width: 100%;
            }
            #meta-dates-indicate{
                display: none;
            }
        }

        @media (max-width: 768px) {
            body {
                overflow: auto; /* Re-enable scrolling on mobile */
            }
            
            .main-container {
                flex-direction: column;
                padding: 15px;
                gap: 15px;
                height: auto; /* Auto height on mobile */
            }
            
            .sidebar {
                display: none;
                height: auto;
            }
            
            .questions-section {
                height: auto;
            }
            
            .questions-content {
                overflow: visible;
            }
            
            .mobile-filters-toggle {
                display: flex;
            }
            
            .questions-header h1 {
                font-size: 24px;
            }
            
            .question-item {
                padding: 20px;
            }
            
            .question-title {
                font-size: 17px;
            }
            
            .question-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .question-date {
                margin-left: 0;
            }
           
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 10px;
            }
            
            .questions-header h1 {
                font-size: 22px;
            }
            
            .question-item {
                padding: 18px;
            }
            
            .question-title {
                font-size: 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .question-title i {
                font-size: 20px;
            }
            
            .no-results {
                padding: 40px 20px;
            }
            
            .no-results i {
                font-size: 48px;
            }
            
            .no-results h3 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="empty"></div>

    <div class="main-container">
        <!-- Desktop Sidebar for Filters -->
        <div class="sidebar">
            <div class="sidebar-content">
                <!-- Categories Filter -->
                <div class="filter-section">
                    <div class="filter-header" onclick="toggleFilter('categories')">
                        <h2><i class="fas fa-folder"></i> Categories</h2>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul id="categories-list" class="filter-list">
                        <li class="filter-item">
                            <a href="questions.php" class="filter-link <?php echo !$selected_category ? 'active-filter' : ''; ?>">
                                <i class="fas fa-list"></i> All Categories
                            </a>
                        </li>
                        <?php 
                        $categories->data_seek(0);
                        while ($row = $categories->fetch_assoc()) { 
                            $is_active = $selected_category == $row['id'];
                        ?>
                            <li class="filter-item">
                                <a href="questions.php?category=<?php echo $row['id']; ?><?php echo $selected_university ? '&university=' . $selected_university : ''; ?>" 
                                   class="filter-link <?php echo $is_active ? 'active-filter' : ''; ?>">
                                    <i class="fas fa-folder-open"></i> 
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>

                <!-- Universities Filter -->
                <div class="filter-section">
                    <div class="filter-header" onclick="toggleFilter('universities')">
                        <h2><i class="fas fa-university"></i> Universities</h2>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul id="universities-list" class="filter-list">
                        <li class="filter-item">
                            <a href="questions.php" class="filter-link <?php echo !$selected_university ? 'active-filter' : ''; ?>">
                                <i class="fas fa-globe"></i> All Universities
                            </a>
                        </li>
                        <?php 
                        $universities->data_seek(0);
                        while ($row = $universities->fetch_assoc()) { 
                            $is_active = $selected_university == $row['id'];
                        ?>
                            <li class="filter-item">
                                <a href="questions.php?university=<?php echo $row['id']; ?><?php echo $selected_category ? '&category=' . $selected_category : ''; ?>" 
                                   class="filter-link <?php echo $is_active ? 'active-filter' : ''; ?>">
                                    <i class="fas fa-school"></i> 
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="questions-section">
            <!-- Mobile Filters Toggle -->
            <div class="mobile-filters-toggle" onclick="toggleMobileFilters()">
                <span><i class="fas fa-filter"></i> Filters</span>
                <i class="fas fa-chevron-down"></i>
            </div>

            <!-- Mobile Filters (hidden by default) -->
            <div class="mobile-filters" id="mobileFilters">
                <!-- Categories Filter -->
                <div class="filter-section">
                    <div class="filter-header" onclick="toggleMobileFilter('mobile-categories')">
                        <h2><i class="fas fa-folder"></i> Categories</h2>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul id="mobile-categories" class="filter-list">
                        <li class="filter-item">
                            <a href="questions.php" class="filter-link <?php echo !$selected_category ? 'active-filter' : ''; ?>">
                                <i class="fas fa-list"></i> All Categories
                            </a>
                        </li>
                        <?php 
                        $categories->data_seek(0);
                        while ($row = $categories->fetch_assoc()) { 
                            $is_active = $selected_category == $row['id'];
                        ?>
                            <li class="filter-item">
                                <a href="questions.php?category=<?php echo $row['id']; ?><?php echo $selected_university ? '&university=' . $selected_university : ''; ?>" 
                                   class="filter-link <?php echo $is_active ? 'active-filter' : ''; ?>">
                                    <i class="fas fa-folder-open"></i> 
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>

                <!-- Universities Filter -->
                <div class="filter-section">
                    <div class="filter-header" onclick="toggleMobileFilter('mobile-universities')">
                        <h2><i class="fas fa-university"></i> Universities</h2>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul id="mobile-universities" class="filter-list">
                        <li class="filter-item">
                            <a href="questions.php" class="filter-link <?php echo !$selected_university ? 'active-filter' : ''; ?>">
                                <i class="fas fa-globe"></i> All Universities
                            </a>
                        </li>
                        <?php 
                        $universities->data_seek(0);
                        while ($row = $universities->fetch_assoc()) { 
                            $is_active = $selected_university == $row['id'];
                        ?>
                            <li class="filter-item">
                                <a href="questions.php?university=<?php echo $row['id']; ?><?php echo $selected_category ? '&category=' . $selected_category : ''; ?>" 
                                   class="filter-link <?php echo $is_active ? 'active-filter' : ''; ?>">
                                    <i class="fas fa-school"></i> 
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="questions-content">
                <div class="questions-header">
                    <h1><i class="fas fa-question-circle"></i> Past Questions</h1>
                    <div class="search-container">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search" placeholder="Search questions by title..." autocomplete="off">
                    </div>
                </div>

                <!-- Active Filters -->
                <?php if ($selected_category || $selected_university) : ?>
                <div class="active-filters">
                    <?php if ($selected_category) : 
                        $categories->data_seek(0);
                        $cat_name = "Unknown";
                        while ($cat = $categories->fetch_assoc()) {
                            if ($cat['id'] == $selected_category) {
                                $cat_name = $cat['name'];
                                break;
                            }
                        }
                    ?>
                    <div class="active-filter-tag">
                        <i class="fas fa-folder"></i>
                        <?php echo htmlspecialchars($cat_name); ?>
                        <button class="remove-filter" onclick="removeFilter('category')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($selected_university) : 
                        $universities->data_seek(0);
                        $uni_name = "Unknown";
                        while ($uni = $universities->fetch_assoc()) {
                            if ($uni['id'] == $selected_university) {
                                $uni_name = $uni['name'];
                                break;
                            }
                        }
                    ?>
                    <div class="active-filter-tag">
                        <i class="fas fa-university"></i>
                        <?php echo htmlspecialchars($uni_name); ?>
                        <button class="remove-filter" onclick="removeFilter('university')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div id="questions-list" class="questions-list">
                    <?php 
                    if ($questions->num_rows > 0) {
                        while ($row = $questions->fetch_assoc()) { 
                            // Get category name for this question
                            $category_name = "Unknown";
                            $categories->data_seek(0);
                            while ($cat = $categories->fetch_assoc()) {
                                if ($cat['id'] == $row['category_id']) {
                                    $category_name = $cat['name'];
                                    break;
                                }
                            }
                            
                            // Get university name for this question
                            $university_name = "Unknown";
                            $universities->data_seek(0);
                            while ($uni = $universities->fetch_assoc()) {
                                if ($uni['id'] == $row['university_id']) {
                                    $university_name = $uni['name'];
                                    break;
                                }
                            }
                    ?>
                        <div class="question-item">
                            <a href="view_questions.php?id=<?php echo $row['id']; ?>">
                                <h3 class="question-title"><i class="fas fa-file-pdf"></i> <?php echo htmlspecialchars($row['title']); ?></h3>
                                <div class="question-meta">
                                    <span id="meta-dates-indicate"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($category_name); ?></span>
                                    <span id="meta-dates-indicate"><i class="fas fa-university"></i> <?php echo htmlspecialchars($university_name); ?></span>
                                    <span id="meta-dates-indicate" class="question-date"><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($row['uploaded_at'])); ?></span>
                                </div>
                            </a>
                        </div>
                    <?php 
                        } 
                    } else { 
                    ?>
                        <div class="no-results">
                            <i class="fas fa-file-exclamation"></i>
                            <h3>No Questions Found</h3>
                            <p>There are no questions available with the current filters.</p>
                            <a href="questions.php" class="clear-filters">
                                <i class="fas fa-times"></i> Clear All Filters
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle desktop filters
        function toggleFilter(filterId) {
            const filterList = document.getElementById(filterId + '-list');
            const header = filterList.previousElementSibling;
            
            filterList.classList.toggle('active');
            header.classList.toggle('active');
        }

        // Toggle mobile filters
        function toggleMobileFilters() {
            const mobileFilters = document.getElementById('mobileFilters');
            const toggleButton = document.querySelector('.mobile-filters-toggle');
            
            mobileFilters.classList.toggle('active');
            toggleButton.classList.toggle('active');
        }

        // Toggle mobile filter sections
        function toggleMobileFilter(filterId) {
            const filterList = document.getElementById(filterId);
            const header = filterList.previousElementSibling;
            
            filterList.classList.toggle('active');
            header.classList.toggle('active');
        }

        // Remove filter
        function removeFilter(type) {
            const url = new URL(window.location.href);
            
            if (type === 'category') {
                url.searchParams.delete('category');
            } else if (type === 'university') {
                url.searchParams.delete('university');
            }
            
            window.location.href = url.toString();
        }

        // Search functionality
        document.addEventListener("DOMContentLoaded", function () {
            let searchInput = document.getElementById("search");
            let questionItems = document.querySelectorAll(".question-item");
            let questionsList = document.getElementById("questions-list");

            // Create "No questions found" message element
            let noResultsMessage = document.createElement("div");
            noResultsMessage.className = "no-results";
            noResultsMessage.innerHTML = `
                <i class="fas fa-search"></i>
                <h3>No Questions Found</h3>
                <p>No questions match your search criteria.</p>
            `;
            noResultsMessage.style.display = "none";
            questionsList.appendChild(noResultsMessage);

            searchInput.addEventListener("keyup", function () {
                let searchValue = searchInput.value.toLowerCase();
                let found = false;

                questionItems.forEach(item => {
                    let title = item.querySelector(".question-title").textContent.toLowerCase();
                    if (title.includes(searchValue)) {
                        item.style.display = "block";
                        found = true;
                    } else {
                        item.style.display = "none";
                    }
                });

                // Show "No questions found" message if no items are visible
                if (!found) {
                    noResultsMessage.style.display = "block";
                } else {
                    noResultsMessage.style.display = "none";
                }
            });
        });
    </script>
</body>
</html>