
<?php
session_start();
// Database connection
include 'config.php'; // or include 'includes/config.php'; depending on your file structure

// Fetch approved testimonials with user information
$query = "SELECT t.message, t.university, u.profile_photo, u.username 
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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="css/about_us.css" />
  <title>About Dasaplus</title>
  <style>

  </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- Content -->
<div class="main-container">

  <!-- About Us -->
  <div class="about-us">
    <div class="about-image">
      <img src="images&icons/about us.jpg" alt="About Us Image" />
    </div>
    <div class="about-text">
      <h2>About Us</h2>
      <p>
        Dasaplus is an innovative educational platform designed to support university students with essential academic resources. We offer a wide range of study materials, including lecture notes and research papers to enhance your learning experience.
      </p>
      <p>
        Our goal is to create a collaborative and engaging space where students can connect, share ideas, and grow academically. We are committed to making learning easier and more accessible, with a team that continuously works to provide high-quality content to help you succeed.
      </p>
    </div>
  </div>

  <!-- Mission and Vision -->
  <div class="mission-vision" id="mission">
    <h2>Our Mission</h2>
    <p>
      To empower university students with access to relevant academic materials, fostering a supportive learning community that encourages growth, innovation, and academic excellence.
    </p>

    <h2>Our Vision</h2>
    <p>
      To be the leading educational platform for students across Africa, providing a centralized hub for academic resources, discussions, and collaborative learning opportunities.
    </p>
  </div>

  <!-- Testimonials -->
  

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


  <!-- FAQ Section -->
  <div class="faq-container">
        <div class="faq-header">
            <h2><i class="fas fa-question-circle"></i> Frequently Asked Questions</h2>
            
        </div>

        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question">
                    <div class="question-text">
                        <span class="question-number">1</span>
                        What is Dasaplus?
                    </div>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Dasaplus is an educational platform that provides students with access to learning resources such as PDFs, video tutorials, and discussion forums to enhance their knowledge and academic experience.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <div class="question-text">
                        <span class="question-number">2</span>
                        How do I create an account on Dasaplus?
                    </div>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    To create an account, click on the "Sign Up" button on the login page, fill in the required details, and verify your email address. Once verified, you can log in and start accessing resources.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <div class="question-text">
                        <span class="question-number">3</span>
                        Is Dasaplus free to use?
                    </div>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Dasaplus offers both free and premium content. Some resources require a subscription, which can be purchased through M-Pesa or PayPal.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <div class="question-text">
                        <span class="question-number">4</span>
                        How do I subscribe to premium content?
                    </div>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    You can subscribe by navigating to the payment section in your profile and selecting your preferred payment method (M-Pesa or PayPal). Once the payment is confirmed, you will gain access to premium resources for three months.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <div class="question-text">
                        <span class="question-number">5</span>
                        Can I upload PDFs and videos to Dasaplus?
                    </div>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Yes, registered users can upload PDFs for review. If approved by the admin, the content will be made available to others. Only admins can upload videos.
                </div>
            </div>
        </div>

        <div class="faq-footer">
            <div class="faq-stats">Showing 5 of 15 questions</div>
            <a href="FAQ.php" class="view-all-link">
                See all FAQs
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
  <!-- Contact Us -->
  <div class="contact" id="contact">
    <h2>Contact Us</h2>
    <p><span>Email:</span> support@dasaplus.com</p>
    <p><span>Phone:</span> +254 712 345678</p>
    <p><span>Instagram:</span> <a href="https://instagram.com/rejentor" target="_blank">@rejentor</a></p>
    <p><span>Facebook:</span> <a href="https://facebook.com/yourpage" target="_blank">Dasaplus Facebook Page</a></p>
  </div>
</div>

<script>
        // FAQ accordion functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                const isActive = item.classList.contains('active');
                
                // Close all items
                document.querySelectorAll('.faq-item').forEach(faqItem => {
                    faqItem.classList.remove('active');
                });
                
                // Open clicked item if it wasn't already active
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });

        // Search functionality
        const searchInput = document.querySelector('.faq-search input');
        searchInput.addEventListener('keyup', () => {
            const searchTerm = searchInput.value.toLowerCase();
            let visibleCount = 0;
            
            document.querySelectorAll('.faq-item').forEach(item => {
                const question = item.querySelector('.question-text').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Update stats
            document.querySelector('.faq-stats').textContent = 
                `Showing ${visibleCount} of 15 questions`;
        });
    </script>

<!-- Footer -->
<footer>
  &copy; 2025 Dasaplus. All rights reserved.
</footer>

</body>
</html>