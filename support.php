
<?php
session_start();
require 'config.php'; // Database connection

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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Support Our Educational Platform - Dasaplus</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #1a73e8;
      --primary-dark: #0d47a1;
      --secondary: #34a853;
      --accent: #fbbc04;
      --light-bg: #f8f9fa;
      --dark: #202124;
      --text: #3c4043;
      --text-light: #5f6368;
      --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      --hover-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--light-bg);
      color: var(--text);
      line-height: 1.6;
    }
    
    header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      text-align: center;
      padding: 4rem 1rem;
      position: relative;
      overflow: hidden;
    }
    
    header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.05"><path d="M30,30 L70,30 L70,70 L30,70 Z" stroke="white" fill="none" /></svg>');
      background-size: 100px;
      opacity: 0.1;
    }
    
    header h1 {
      font-size: 2.8rem;
      margin-bottom: 1rem;
      font-weight: 700;
    }
    
    header p {
      font-size: 1.2rem;
      max-width: 600px;
      margin: 0 auto;
      opacity: 0.9;
    }
    
    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 1rem;
    }
    
    .support-section {
      background: white;
      padding: 3rem;
      border-radius: 16px;
      box-shadow: var(--card-shadow);
      margin-bottom: 2rem;
    }
    
    .support-section h2 {
      color: var(--primary);
      margin-bottom: 1.5rem;
      font-size: 2rem;
      position: relative;
      padding-bottom: 0.5rem;
    }
    
    .support-section h2::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 60px;
      height: 4px;
      background: var(--accent);
      border-radius: 2px;
    }
    
    .support-section p {
      margin-bottom: 1.5rem;
      color: var(--text);
      font-size: 1.1rem;
    }
    
    .highlight {
      background: linear-gradient(to right, rgba(26, 115, 232, 0.05) 0%, rgba(251, 188, 4, 0.05) 100%);
      padding: 2rem;
      border-left: 6px solid var(--accent);
      border-radius: 12px;
      margin: 2rem 0;
    }
    
    .highlight p {
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--primary-dark);
    }
    
    .highlight ul {
      list-style-type: none;
      padding-left: 0;
    }
    
    .highlight li {
      margin-bottom: 0.8rem;
      padding-left: 2rem;
      position: relative;
    }
    
    .highlight li::before {
      content: '✓';
      position: absolute;
      left: 0;
      color: var(--secondary);
      font-weight: bold;
    }
    
    .impact-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin: 2.5rem 0;
    }
    
    .impact-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: var(--card-shadow);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      text-align: center;
    }
    
    .impact-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--hover-shadow);
    }
    
    .impact-card i {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }
    
    .impact-card h3 {
      color: var(--primary-dark);
      margin-bottom: 0.5rem;
    }
    
    .impact-card p {
      color: var(--text-light);
      font-size: 1rem;
    }
    
    .cta-buttons {
      display: flex;
      gap: 1.5rem;
      flex-wrap: wrap;
      margin-top: 2.5rem;
      justify-content: center;
    }
    
    .cta-button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      background: var(--primary);
      color: white;
      padding: 1rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
      min-width: 200px;
    }
    
    .cta-button:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: var(--hover-shadow);
    }
    
    .cta-button.secondary {
      background: white;
      color: var(--primary);
      border: 2px solid var(--primary);
    }
    
    .cta-button.secondary:hover {
      background: var(--primary);
      color: white;
    }
    
    .cta-button i {
      margin-right: 0.5rem;
    }
    
    footer {
      text-align: center;
      padding: 2.5rem 1rem;
      margin-top: 3rem;
      background: var(--dark);
      color: white;
    }
    
    footer p {
      margin: 0;
      opacity: 0.8;
    }
    
    /* Testimonials Section */
.testimonials-section {
    background-color: #f8f9fa;
    text-align: center;
    padding: 3rem 1rem;
    overflow: hidden;
}

.testimonials-section h2 {
    color: #1a73e8;
    font-size: 2.2rem;
    font-weight: 600;
    margin-bottom: 2.5rem;
    text-transform: none;
    letter-spacing: 0;
}

.testimonial-slider {
    display: flex;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 2rem;
    margin: 0 auto;
    max-width: 100%;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    -ms-overflow-style: none;
    gap: 2rem;
}

.testimonial-slider::-webkit-scrollbar {
    display: none;
}

.testimonial-card {
    background-color: #fff;
    border-radius: 16px;
    padding: 3rem 2rem 2rem;
    box-shadow: 0 8px 24px rgba(0, 51, 0, 0.1);
    border: 1px solid #e0e0e0;
    text-align: center;
    flex: 0 0 auto;
    width: 85%;
    max-width: 380px;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 280px;
    scroll-snap-align: center;
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0, 51, 0, 0.15);
}

.testimonial-avatar {
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
}

.testimonial-avatar-image {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    object-fit: cover;
}

.testimonial-avatar-placeholder {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #003300, #006400);
    border: 4px solid #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    font-weight: 600;
}

.testimonial-message {
    font-size: 1.1rem;
    font-style: italic;
    color: #555;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    padding: 0 1rem;
    position: relative;
    flex-grow: 1;
}

.testimonial-message::before {
    content: '“';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    top: -20px;
    font-size: 3rem;
    color: rgba(0, 51, 0, 0.2);
    font-family: Georgia, serif;
    line-height: 1;
}

.testimonial-info {
    margin-top: auto;
    width: 100%;
}

.testimonial-name {
    font-weight: 700;
    color: #003300;
    margin-bottom: 0.25rem;
    font-size: 1.1rem;
}

.testimonial-university {
    font-size: 1rem;
    color: #666;
    margin-top:0.5rem;
    opacity: 0.9;
    font-style: normal;
}

.testimonial-username {
    font-size: 0.9rem;
    color: #006400;
    font-weight: 500;
    opacity: 0.8;
}

/* Navigation arrows */
.testimonial-nav {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.testimonial-nav-button {
    background: linear-gradient(90deg, #003300, #006400);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s, box-shadow 0.3s;
}

.testimonial-nav-button:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 51, 0, 0.2);
}

/* Responsive design */
@media (max-width: 768px) {
    .testimonials-section {
        padding: 2rem 0.5rem;
    }
    
    .testimonials-section h2 {
        font-size: 1.8rem;
        margin-bottom: 2rem;
    }
    
    .testimonial-slider {
        padding: 1.5rem;
        gap: 1.5rem;
    }
    
    .testimonial-card {
        width: 90%;
        max-width: 320px;
        padding: 2.5rem 1.5rem 1.5rem;
        min-height: 250px;
    }
    
    .testimonial-avatar-image,
    .testimonial-avatar-placeholder {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .testimonial-message {
        font-size: 1rem;
        padding: 0 0.5rem;
    }
    
    .testimonial-message::before {
        font-size: 2.5rem;
        top: -15px;
    }
}

@media (max-width: 480px) {
    .testimonial-card {
        width: 95%;
        max-width: 280px;
        padding: 2rem 1rem 1rem;
    }
    
    .testimonial-avatar {
        top: -25px;
    }
    
    .testimonial-avatar-image,
    .testimonial-avatar-placeholder {
        width: 50px;
        height: 50px;
        font-size: 1.3rem;
    }
    
    .testimonial-message {
        font-size: 0.95rem;
    }
}

    @media (max-width: 768px) {
      header h1 {
        font-size: 2.2rem;
      }
      
      .support-section {
        padding: 2rem 1.5rem;
      }
      
      .cta-buttons {
        flex-direction: column;
        align-items: center;
      }
      
      .cta-button {
        width: 100%;
        max-width: 300px;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1>Support Our Educational Mission</h1>
    <p>Empower the next generation of learners with your contribution</p>
  </header>

  <div class="container">
    <section class="support-section">
      <h2>Why Your Support Matters</h2>
      <p>At Dasaplus, we're committed to breaking down barriers to education by providing high-quality learning resources to university students everywhere. We believe that knowledge should be accessible to all, regardless of background or financial circumstances.</p>

      <div class="highlight">
        <p><strong>Your contribution will directly impact students by:</strong></p>
        <ul>
          <li>Expanding our library of study materials, tutorials, and research papers</li>
          <li>Developing interactive platforms for collaborative learning</li>
          <li>Providing free resources to students from underserved communities</li>
          <li>Enhancing our technology for better video learning and resource accessibility</li>
          <li>Supporting mentorship programs connecting students with industry professionals</li>
        </ul>
      </div>

      <div class="impact-cards">
        <div class="impact-card">
          <i class="fas fa-graduation-cap"></i>
          <h3>10,000+ Students Helped</h3>
          <p>Your support has already impacted thousands of learners across multiple universities</p>
        </div>
        
        <div class="impact-card">
          <i class="fas fa-book-open"></i>
          <h3>500+ Resources</h3>
          <p>We've developed extensive learning materials covering various disciplines</p>
        </div>
        
        <div class="impact-card">
          <i class="fas fa-users"></i>
          <h3>Community Growth</h3>
          <p>Our platform fosters collaboration and knowledge sharing among students</p>
        </div>
      </div>

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

      <h2>How You Can Support</h2>
      <p>There are multiple ways to contribute to our mission. Whether through financial support, sharing expertise, or helping us reach more students, every action makes a difference.</p>

      <div class="cta-buttons">
        <a href="donation_pages/Donations_from _users.php" class="cta-button">
          <i class="fas fa-donate"></i> Make a Donation
        </a>
        <a href="partner.html" class="cta-button secondary">
          <i class="fas fa-handshake"></i> Partner With Us
        </a>
        <a href="share.html" class="cta-button secondary">
          <i class="fas fa-share-alt"></i> Share Our Mission
        </a>
      </div>
    </section>
  </div>

  <footer>
    <p>&copy; 2025 Dasaplus | Empowering Education Through Innovation and Accessibility</p>
  </footer>

</body>
</html>