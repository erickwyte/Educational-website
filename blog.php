<?php
session_start();
include 'config.php'; // Database connection

// Fetch categories
$categoryQuery = "SELECT DISTINCT category_name FROM blog_categories";
$categoryStmt = $conn->prepare($categoryQuery);
if (!$categoryStmt) {
    die("Category query preparation failed: " . $conn->error);
}
$categoryStmt->execute();
$categoryResult = $categoryStmt->get_result();

// Fetch most viewed posts (Popular Articles)
$popularQuery = "SELECT id, title FROM blog_posts ORDER BY views DESC LIMIT 5";
$popularStmt = $conn->prepare($popularQuery);
if (!$popularStmt) {
    die("Popular posts query preparation failed: " . $conn->error);
}
$popularStmt->execute();
$popularPosts = $popularStmt->get_result();

// Fetch latest posts
$query = "SELECT id, title FROM blog_posts ORDER BY created_at DESC LIMIT 5";
$latestStmt = $conn->prepare($query);
if (!$latestStmt) {
    die("Latest posts query preparation failed: " . $conn->error);
}
$latestStmt->execute();
$latestPosts = $latestStmt->get_result();

// Check if a category is selected
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// TRACK BLOG VIEW ACTIVITY - ADDED THIS
if (isset($_SESSION['user_id'])) {
    $activity_details = "Viewed blog page";
    if (!empty($selectedCategory)) {
        $activity_details .= " - Category: " . $selectedCategory;
    }
    track_activity($_SESSION['user_id'], ACTIVITY_VIEW, $activity_details);
} else {
    $activity_details = "Guest viewed blog page";
    if (!empty($selectedCategory)) {
        $activity_details .= " - Category: " . $selectedCategory;
    }
    track_guest_activity(ACTIVITY_VIEW, $activity_details);
}

if (!empty($selectedCategory)) {
    // Fetch blog posts based on category using a JOIN
    $query = "SELECT bp.* FROM blog_posts bp 
              JOIN blog_categories bc ON bp.category_id = bc.id 
              WHERE bc.category_name = ? ORDER BY bp.created_at DESC";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Category posts query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("s", $selectedCategory);
} else {
    // Fetch all blog posts
    $query = "SELECT * FROM blog_posts ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("All posts query preparation failed: " . $conn->error);
    }
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Dasaplus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/blog.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="empty"></div>

    <!-- Category Header for Small Screens -->
    <div class="category-header">
        <div class="category-header-inner">
            <a href="blog.php" class="<?php echo empty($selectedCategory) ? 'active' : ''; ?>">
                All Categories
            </a>
            <?php 
            $categoryResult->data_seek(0);
            while ($category = $categoryResult->fetch_assoc()) { ?>
                <a href="blog.php?category=<?php echo urlencode($category['category_name']); ?>"
                   class="<?php echo ($selectedCategory == $category['category_name']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category['category_name']); ?>
                </a>
            <?php } ?>
        </div>
    </div>

    <div class="container">
        <!-- Main Content -->
        <main class="main-content">
            <?php while ($post = $result->fetch_assoc()) { 
                $blog_id = $post['id'];
                
                // Fetch images for this blog post
                $mediaQuery = "SELECT media_url FROM blog_media WHERE blog_id = ? AND media_type = 'image'";
                $mediaStmt = $conn->prepare($mediaQuery);
                if (!$mediaStmt) {
                    die("Media query preparation failed: " . $conn->error);
                }
                $mediaStmt->bind_param("i", $blog_id);
                $mediaStmt->execute();
                $mediaResult = $mediaStmt->get_result();

                $images = [];
                while ($row = $mediaResult->fetch_assoc()) {
                    $images[] = "Uploads/blog_images/" . $row['media_url'];
                }
                $mediaStmt->close();

                $defaultImage = "images&icons/default-blog.png"; 
                $image_path = !empty($images) ? $images[0] : $defaultImage;
            ?>
                <a href="blog_article.php?id=<?php echo $post['id']; ?>" class="article-link">
                    <article class="article">
                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <div class="article-content">
                            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                            <div class="article-meta">
                                <span>By <?php echo htmlspecialchars($post['author']); ?></span>
                                <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                                <span class="views">
                                    <i class="fas fa-eye"></i> <?php echo $post['views']; ?> Views
                                </span>
                            </div>
                            <p>
                                <?php 
                                    $maxLength = 200;
                                    $content = strip_tags($post['content']);
                                    $content = preg_replace('/\s+/', ' ', $content);
                                    $content = trim($content);
                                    echo strlen($content) > $maxLength ? substr($content, 0, $maxLength) . '...' : $content;
                                ?>
                            </p>
                        </div>
                    </article>
                </a>
            <?php } ?>
        </main>

        <!-- Sidebar (Hidden on small screens) -->
        <aside class="sidebar">
            <!-- Categories Section -->
            <div class="sidebar-widget categories">
                <h2>Categories</h2>
                <ul>
                    <li>
                        <a href="blog.php" class="<?php echo empty($selectedCategory) ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> All Categories
                        </a>
                    </li>
                    <?php 
                    $categoryResult->data_seek(0);
                    while ($category = $categoryResult->fetch_assoc()) { ?>
                        <li>
                            <a href="blog.php?category=<?php echo urlencode($category['category_name']); ?>"
                               class="<?php echo ($selectedCategory == $category['category_name']) ? 'active' : ''; ?>">
                                <i class="fas fa-folder"></i> <?php echo htmlspecialchars($category['category_name']); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <!-- Popular Articles -->
            <div class="sidebar-widget popular-articles">
                <h2>Popular Articles</h2>
                <ul>
                    <?php 
                    $popularPosts->data_seek(0);
                    while ($popular = $popularPosts->fetch_assoc()) { ?>
                        <li>
                            <a href="blog_article.php?id=<?php echo $popular['id']; ?>">
                                <i class="fas fa-fire"></i> <?php echo htmlspecialchars($popular['title']); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <!-- Latest Posts -->
            <div class="sidebar-widget latest-posts">
                <h2>Latest Posts</h2>
                <ul>
                    <?php 
                    $latestPosts->data_seek(0);
                    while ($latest = $latestPosts->fetch_assoc()) { ?>
                        <li>
                            <a href="blog_article.php?id=<?php echo $latest['id']; ?>">
                                <i class="fas fa-clock"></i> <?php echo htmlspecialchars($latest['title']); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </aside>
    </div>

    <button onclick="scrollToTop()" id="backToTopBtn" title="Go to top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script>
        // Show button when scrolling down
        window.onscroll = function() { scrollFunction(); };

        function scrollFunction() {
            var button = document.getElementById("backToTopBtn");
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                button.style.display = "flex";
            } else {
                button.style.display = "none";
            }
        }

        // Smooth scroll to top
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: "smooth" });
        }

        // Initialize scroll function on page load
        scrollFunction();
    </script>
</body>
</html>