<?php
session_start();
include 'config.php';

// Ensure database connection exists
if (!isset($conn)) {
    die("Database connection failed.");
}

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
$username = null;

// Fetch logged-in user's username
if ($user_id) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $username = $user['username'] ?? null;
}

// Validate blog post ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 0;
if ($id <= 0) {
    die("Invalid blog post ID.");
}


// Fetch blog post details including likes
$stmt = $conn->prepare("SELECT title, author, created_at, content, views, likes FROM blog_posts WHERE id = ?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    die("Blog post not found.");
}

// Get the correct like count from the database
$like_count = $post['likes'] ?? 0;

// Update the post view count
$updateViews = $conn->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
$updateViews->bind_param("i", $id);
$updateViews->execute();




//likes count/////////////////////////////////////////////
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id'])) {
    $post_id = intval($_POST['post_id']);

    // Increment likes in the database
    $stmt = $conn->prepare("UPDATE blog_posts SET likes = likes + 1 WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();

    // Fetch updated like count
    $stmt = $conn->prepare("SELECT likes FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode(['likes' => $row['likes']]); // Return updated likes
    exit();
}


// Fetch blog post details, ensure 'id' is selected
$stmt = $conn->prepare("SELECT id, title, author, created_at, content, views, likes FROM blog_posts WHERE id = ?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    die("Error: Blog post not found.");
}






// Fetch the blog post details
$blog_id = $post['id'] ?? null;
if (!$blog_id) {
    die("Error: Blog ID is missing or invalid.");
}

// Fetch associated images
$mediaQuery = "SELECT media_url FROM blog_media WHERE blog_id = ? AND media_type = 'image'";
$mediaStmt = $conn->prepare($mediaQuery);
$mediaStmt->bind_param("i", $blog_id);
$mediaStmt->execute();
$mediaResult = $mediaStmt->get_result();

$images = [];
while ($row = $mediaResult->fetch_assoc()) {
    $imageFile = "uploads/blog_images/" . $row['media_url'];
    if (file_exists($imageFile)) {
        $images[] = $imageFile;
    }
}
$mediaStmt->close();

// Default image
$defaultImage = "images&icons/default-blog.png"; 

// If images exist, use the first one
$imagePath = !empty($images) ? $images[0] : $defaultImage;
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($post['title'] ?? 'Blog Article') ?> | Dasaplus</title>

    <?php
    $description = htmlspecialchars(substr(strip_tags($post['content']), 0, 160));
    $keywords = htmlspecialchars(implode(", ", explode(" ", $post['title'])));
    $site_name = "Dasaplus";
    $canonicalUrl = "https://yourwebsite.com/blog_article.php?id=" . $id;
    ?>

    <meta name="description" content="<?= $description ?>">
    <meta name="keywords" content="<?= $keywords ?>">
    <meta name="author" content="Dasaplus">
    <link rel="canonical" href="<?= $canonicalUrl ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>" />
    <meta property="og:description" content="<?= $description ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:image" content="https://yourwebsite.com/<?= $imagePath ?>" />
    <meta property="og:url" content="<?= $canonicalUrl ?>" />
    <meta property="og:site_name" content="<?= $site_name ?>" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= htmlspecialchars($post['title']) ?>" />
    <meta name="twitter:description" content="<?= $description ?>" />
    <meta name="twitter:image" content="https://yourwebsite.com/<?= $imagePath ?>" />
    <meta name="twitter:site" content="@yourTwitterHandle" />

    <style>
        /* Reset */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Segoe UI', sans-serif;
  background-color: #f8f9fa;
  color: #333;
  line-height: 1.7;
}

/* Layout Container */
.container {
  display: flex;
  flex-wrap: nowrap;
  flex-direction: row;
  justify-content: space-between;
  max-width: 1200px;
  margin: 40px auto;
  padding: 0 20px;
  gap: 30px;
}

/* Article Section */
.article-content {
  flex: 2;
  background: #fff;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
}

.article-content h3 {
  font-size: 32px;
  margin-bottom: 10px;
  color: #003300;
}

.post-meta {
  font-size: 14px;
  color: #777;
  margin-bottom: 20px;
}

.blog-image {
  width: 100%;
  border-radius: 6px;
  margin: 20px 0;
}

.post-content p {
  margin-bottom: 20px;
}

.button-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
}

.back-button img,
.like-button img {
  height: 24px;
  vertical-align: middle;
}

.like-button {
  background: transparent;
  border: none;
  cursor: pointer;
  font-size: 16px;
  color: #444;
  display: flex;
  align-items: center;
  gap: 5px;
}

/* Share Section */
.share-article {
  margin-top: 30px;
}

.share-article h3 {
  font-size: 20px;
  margin-bottom: 10px;
  color: #003300;
}

.share-btn {
  display: inline-block;
  margin-right: 10px;
}

.share-btn img {
  height: 32px;
  width: 32px;
  transition: transform 0.2s ease;
}

.share-btn:hover img {
  transform: scale(1.1);
}


/* Sidebar Styles */
/* Sidebar Styles */
.sidebar {
    width: 300px;
    max-height: 400px; /* Maximum height */
    padding: 20px;
    background-color: #f4f4f4;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 80px;
    
}


/* Widget Header */
.sidebar .widget h3 {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
}

/* Ads Section */
.sidebar .widget.ads {
    margin-bottom: 30px;
}

/* Individual Ad Styling */
.sidebar .widget .ad {
    margin-bottom: 20px;
    padding: 10px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-align: center;
}

.sidebar .widget .ad img {
    max-width: 100%;
    height: auto;
    border-radius: 6px;
    margin-bottom: 10px;
}

.sidebar .widget .ad p {
    font-size: 14px;
    color: #555;
}

/* Optional: Hover Effect for Ads */
.sidebar .widget .ad:hover {
    background-color: #f1f1f1;
    cursor: pointer;
}
/* Suggested/Related Articles Section */
.related-articles {
    margin-bottom: 30px;
    background-color:rgb(255, 255, 255);  /* Light background for the section */

    border-radius: 8px;
}

.related-articles h2 {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    margin: 25px  0  15px;
    border-bottom: 2px solid rgb(0, 0, 0); /* Underline the heading */
    padding-bottom: 5px;
}

.related-articles ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.related-articles ul li {
    margin-bottom: 5px;
    border-bottom: 1px solid #ccc;
}
.related-articles ul li h3{
    margin-bottom: 5px;
    font-size: 18px;
}

.related-articles ul li a {
    text-decoration: none;
    color: #003300;  /* Link color */
    font-size: 17px;
    transition: color 0.3s ease; /* Smooth hover transition */
    display: block;
}

.related-articles ul li a:hover {
    color: red; /* Darker blue on hover */
    text-decoration: underline; /* Underline on hover */

}




/* Responsive */
@media (max-width: 999px) {
  .container {
    flex-direction: column;
    padding: 0 10px;
    margin:auto;
  }

  .sidebar {
    position: static;
    width: 100%;
    margin-top: 20px;
  }

  .article-content h3 {
    font-size: 24px;
  }

  .share-btn img {
    height: 28px;
    width: 28px;
  }
}

    </style>
</head>

<body>
<?php include 'includes/header.php'; ?>
<div class="empty"></div>

<div class="container">
<main class="article-content">
    <h3><?= htmlspecialchars($post['title']) ?></h3>
    <p class="post-meta">
        <strong>By <?= htmlspecialchars($post['author']) ?></strong> |
        <?= date('F j, Y', strtotime($post['created_at'])) ?>
    </p>

    <img class="blog-image" src="<?= $imagePath ?>" alt="Post Image">

    <div class="post-content">
        <p><?= nl2br(trim(preg_replace('/\s+/', ' ', $post['content']))) ?></p>
    </div>

    <div class="button-container">
        <a href="blog.php" class="back-button">
            <img src="images&icons/left-arrow.png" alt="Back">
        </a>
        <button class="like-button" id="likeBtn">
            <img src="images&icons/thumb-up.png" alt="">
            (<span id="likeCount"><?= $like_count ?></span>)
        </button>
    </div>

    <div class="share-article">
        <h3>Share:</h3>
        <?php
        $shareUrl = urlencode("https://yourwebsite.com/blog_article.php?id=" . $id);
        $shareText = urlencode($post['title'] . " - Read it here: https://yourwebsite.com/blog_article.php?id=" . $id);
        $titleOnly = urlencode($post['title']);
        ?>

        <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>" target="_blank" class="share-btn whatsapp">
            <img src="images&icons/whatsapp-icon.png" alt="WhatsApp">
        </a>

        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="share-btn facebook">
            <img src="images&icons/facebook-icon.png" alt="Facebook">
        </a>

        <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $titleOnly ?>" target="_blank" class="share-btn twitter">
            <img src="images&icons/twitter (2).png" alt="Twitter">
        </a>
    </div>

    <!-- Suggested/Related Articles Section -->
    <div class="related-articles">
        <h2>Related Articles</h2>
        <ul>
            <?php
            // Fetch related articles from the database
            $relatedStmt = $conn->prepare("SELECT id, title FROM blog_posts WHERE id != ? ORDER BY created_at DESC LIMIT 5");
            $relatedStmt->bind_param("i", $id);
            $relatedStmt->execute();
            $relatedResult = $relatedStmt->get_result();
            while ($relatedPost = $relatedResult->fetch_assoc()) {
                echo '<li><h3><a href="blog_article.php?id=' . $relatedPost['id'] . '">' . htmlspecialchars($relatedPost['title']) . '</a></h3></li>';
            }
            ?>
        </ul>
    </div>
</main>


    <aside class="sidebar">
    <div class="widget ads">
        <h3>Advertisements</h3>
        <div class="ad">
            <img src="ad1.jpg" alt="Ad 1">
            <p>Advertise here!</p>
        </div>
        <div class="ad">
            <img src="ad2.jpg" alt="Ad 2">
            <p>Advertise here!</p>
        </div>
        <div class="ad">
            <img src="ad3.jpg" alt="Ad 3">
            <p>Advertise here!</p>
        </div>
    </div>

    
</aside>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const likeBtn = document.getElementById("likeBtn");
    const likeCountSpan = document.getElementById("likeCount");

    likeBtn.addEventListener("click", function() {
        const postId = <?= $id ?>;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "like_post.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        alert(response.error);
                    } else {
                        likeCountSpan.innerText = response.likes;
                        likeBtn.disabled = true;
                    }
                } catch (e) {
                    console.error("Error parsing JSON:", e);
                }
            }
        };

        xhr.send("post_id=" + postId);
    });
});
</script>

</body>
</html>
