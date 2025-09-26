<?php
session_start();
require 'config.php'; // Database connection

// Generate CSRF Token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// Email Subscription
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subscribe'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "❌ CSRF token validation failed.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit();
    }

    if (isset($_SESSION['last_subscribe']) && time() - $_SESSION['last_subscribe'] < 30) {
        $_SESSION['message'] = "❌ You're submitting too fast. Please wait before subscribing again.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit();
    }
    $_SESSION['last_subscribe'] = time();

    if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "❌ Invalid email address.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit();
    }

    $email = $conn->real_escape_string(trim($_POST['email']));

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM email_subscribers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['message'] = "❌ This email is already subscribed!";
        $_SESSION['message_type'] = "error";
        $stmt->close();
        header("Location: index.php");
        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO email_subscribers (email) VALUES (?)");
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Subscription successful! 🎉";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "❌ Error subscribing. Please try again.";
        $_SESSION['message_type'] = "error";
    }

    $stmt->close();
    header("Location: index.php");
    exit();
}
?>

<?php
// Fetch approved testimonials with user information
$query = "SELECT t.message, t.name, t.university, u.profile_photo, u.username 
          FROM testimonials t 
          LEFT JOIN users u ON t.user_id = u.id 
          WHERE t.status = 'approved'";
$result = mysqli_query($conn, $query);

$testimonials = [];
while ($row = mysqli_fetch_assoc($result)) {
    $testimonials[] = $row;
}

mysqli_close($conn);
?>

<!-- Message Modal -->
<?php if (isset($_SESSION['message'])): ?>
    <div id="messageModal" class="modal">
        <div class="modal-content <?php echo $_SESSION['message_type']; ?>">
            <span class="close" onclick="closeModal()">&times;</span>
            <p><?php echo $_SESSION['message']; ?></p>
        </div>
    </div>
    <script>
        document.getElementById("messageModal").style.display = "block";
        function closeModal() {
            document.getElementById("messageModal").style.display = "none";
        }
        setTimeout(closeModal, 5000);
    </script>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasaplus - Your Ultimate Learning Hub</title> 
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="empty">

    </div>

    <section class="hero"> 
        <div class="container home-section"> 
            <div class="home-text">
          <h1>Empower Your Learning Journey with Dasaplus</h1>
          <p class="lead">Access top-tier study materials, including curated PDFs, video tutorials, and exam prep resources — all tailored for university students like you.</p>
          <p>Join the Dasaplus community to discover, upload, and discuss academic content across a wide range of courses. Whether you're reviewing class notes, diving into past papers, or collaborating through our discussion forum, Dasaplus is here to help you succeed — anytime, anywhere.</p>
          <p><strong>Start learning smarter — not harder. Your academic success begins here.</strong></p>


                <button><a href="signup.php">Get Started</a></button> <button><a href="contact.php">Contact Us</a></button>
            </div>
            <div class="home-image">
                <img src="images&icons/freepik__upload__83844.png" alt="Students collaborating on learning"> </div>
        </div>
    </section>

    <section class="about-dasaplus">
    <div class="section-container">
        <h2>About Dasaplus</h2>
        <p>Dasaplus is more than just a study tool; it's a thriving community of dedicated learners, passionate educators, and driven students across Kenya, all committed to the principles of open-access education and collaborative knowledge sharing.</p>
        <p>Our platform is built on the belief that education should be accessible and engaging for everyone. We strive to provide a supportive environment where you can connect with peers, access valuable resources, and contribute to a collective pool of knowledge.</p>
        
        <!-- Link to About Page -->
        <a href="about_us.php" class="learn-more-link">Learn more about our mission & vision →</a>
    </div>
</section>

      <section class="upload-section">
        <div class="section-container">
            <h2>Share Your Knowledge, Empower Others! Upload Your Study Materials on Dasaplus</h2> <p>Do you have well-crafted notes, insightful guides, or valuable past papers that could benefit fellow students? Don't let them gather dust – share them on Dasaplus and make a real difference in someone's learning journey!</p> <p>Your contributions can be the key to another student's success, whether it's acing an exam or finally grasping a complex concept.</p>
            <p>At Dasaplus, we believe in the power of students helping students. If you have resources that could be useful, we invite you to share them and become an integral part of our supportive community.</p>
            <p><strong>Ready to contribute?</strong> Click the upload button below to add your PDFs! We carefully review all submissions to maintain the quality of our resources. Let's build a stronger learning community together! 🚀</p>
            <a href="user_pdfs_uploads.php" class="upload-button">Upload Your Study Materials Now</a> <p class="call-to-action">Join us in creating a smarter, more connected future through shared knowledge – start sharing today!</p> </div>
    </section>

    


<section class="testimonials">
    <div class="testimonials-section">
        <h2>What Students Say</h2>
        <div class="testimonial-slider" id="testimonialSlider">
            <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-card">
                    <!-- Profile Photo or Placeholder -->
                    <div class="testimonial-avatar">
                        <?php if (!empty($t['profile_photo'])): ?>
                            <img src="<?= htmlspecialchars($t['profile_photo']) ?>" 
                                 alt="Student profile" 
                                 class="testimonial-avatar-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="testimonial-avatar-placeholder" style="display: none;">
                                <?= !empty($t['username']) ? strtoupper(substr($t['username'], 0, 1)) : 'S' ?>
                            </div>
                        <?php else: ?>
                            <div class="testimonial-avatar-placeholder">
                                <?= !empty($t['username']) ? strtoupper(substr($t['username'], 0, 1)) : 'S' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="testimonial-message"><?= htmlspecialchars($t['message']) ?></div>
                    
                    <div class="testimonial-info">
                        <?php if (!empty($t['username'])): ?>
                            <div class="testimonial-username">@<?= htmlspecialchars($t['username']) ?></div>
                        <?php endif; ?>
                        <div class="testimonial-university"><?= htmlspecialchars($t['university']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
 

    <?php $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
    <section class="subscription-section">
        <div class="container">
            <h2>Stay Informed! Subscribe to Our Newsletter</h2> <p>Receive the latest updates on new courses, valuable content additions, and exclusive learning resources directly to your inbox.</p> <form class="subscription-form" action="index.php" method="POST" onsubmit="return validateEmail();">
                <input type="email" id="email" name="email" placeholder="Enter your email address" required> <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" name="subscribe">Subscribe</button> </form>
        </div>
    </section>

    <script>
        function validateEmail() {
            var email = document.getElementById("email").value;
            var pattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
            if (!email.match(pattern)) {
                alert("Please enter a valid email address.");
                return false;
            }
            return true;
        }
    </script>
    <script>
// Simple slider functionality
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('testimonialSlider');
    const cards = slider.querySelectorAll('.testimonial-card');
    let currentIndex = 0;
    
    // Auto-advance slides every 5 seconds
    setInterval(() => {
        currentIndex = (currentIndex + 1) % cards.length;
        scrollToTestimonial(currentIndex);
    }, 5000);
    
    function scrollToTestimonial(index) {
        const card = cards[index];
        slider.scrollTo({
            left: card.offsetLeft - slider.offsetLeft - (slider.offsetWidth - card.offsetWidth) / 2,
            behavior: 'smooth'
        });
    }
});
</script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>