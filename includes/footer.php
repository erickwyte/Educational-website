<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Dasaplus</title>
  <style>
    .bottom-footer {
      background-color: #003300;
      color: #e8f5e8;
      padding: 2.5rem 0 0;
      margin-top: 2.5rem;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1.2rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 2rem;
    }

    .footer-section h3 {
      color: #ffffff;
      font-size: 1.2rem;
      margin-bottom: 1rem;
      font-weight: 600;
      position: relative;
    }

    .footer-section h3::after {
      content: '';
      position: absolute;
      bottom: -6px;
      left: 0;
      width: 40px;
      height: 2px;
      background-color: #FFD700;
      border-radius: 4px;
    }

    .footer-section p {
      line-height: 1.6;
      margin: 0.8rem 0;
      font-size: 0.95rem;
      color: #d6e5d6;
    }

    .footer-section ul {
      list-style: none;
      padding: 0;
    }

    .footer-section li {
      margin-bottom: 0.6rem;
    }

    .footer-section a {
      color: #e8f5e8;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.95rem;
    }

    .footer-section a:hover,
    .footer-section a:focus {
      color: #FFD700;
      transform: translateX(6px);
    }

    .footer-logo img {
      width: 160px;
      margin-bottom: 0.8rem;
      transition: transform 0.3s ease;
    }

    .footer-logo img:hover {
      transform: scale(1.05);
    }

    .social-icons {
      display: flex;
      gap: 0.8rem;
      margin-top: 1rem;
    }

    .social-icons a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background-color: rgba(255, 255, 255, 0.12);
      border-radius: 50%;
      transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .social-icons a:hover,
    .social-icons a:focus {
      background-color: #FFD700;
      transform: translateY(-3px);
    }

    .social-icons img {
      width: 20px;
      height: 20px;
    }

    .footer-bottom {
      background-color: rgba(0, 0, 0, 0.25);
      text-align: center;
      padding: 1rem;
      margin-top: 2rem;
      font-size: 0.9rem;
      color: #d6e5d6;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    @media (max-width: 768px) {
      .footer-container {
        grid-template-columns: 1fr;
        /* Removed text-align: center */
        gap: 2rem;
      }

      /* Keep the underline aligned to the left on small screens */
      .footer-section h3::after {
        /* Removed left: 50% and transform: translateX(-50%) */
        left: 0;
        transform: none;
      }

      /* Keep social icons aligned to the left */
      .social-icons {
        /* Removed justify-content: center */
        justify-content: flex-start;
      }

      /* Keep logo aligned to the left */
      .footer-logo img {
        /* Removed margin: 0 auto 1rem */
        margin: 0 0 1rem 0;
      }
    }

    @media (max-width: 480px) {
      .footer-container {
        padding: 0 1rem;
      }

      .footer-logo img {
        width: 140px;
      }
    }
  </style>
</head>
<body>
  <footer class="bottom-footer" role="contentinfo">
    <div class="footer-container">
      <!-- About Section -->
      <div class="footer-section about" aria-labelledby="about-heading">
        <a href="index.php" class="footer-logo" aria-label="Dasaplus Home">
          <img src="images&icons/LOGO.png" alt="Dasaplus Logo">
        </a>
        <p>
          Dasaplus empowers university students with accessible educational resources 
          and an interactive academic community. Learn more, share more, grow together.
        </p>
      </div>

      <!-- Quick Links -->
      <div class="footer-section quick-links" aria-labelledby="quick-links-heading">
        <h3 id="quick-links-heading">Quick Links</h3>
        <ul>
          <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
          <li><a href="notes.php"><i class="fas fa-book"></i> Resources</a></li>
          <li><a href="questions.php"><i class="fas fa-question-circle"></i> Questions</a></li>
          <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
          <li><a href="signup.php"><i class="fas fa-user-plus"></i> Signup</a></li>
          <li><a href="blog.php"><i class="fas fa-blog"></i> Blog</a></li>
          <li><a href="discussion_forum.php"><i class="fas fa-comments"></i> Forum</a></li>
          <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a></li>
        </ul>
      </div>

      <!-- More Resources -->
      <div class="footer-section resources" aria-labelledby="resources-heading">
        <h3 id="resources-heading">More</h3>
        <ul>
          <li><a href="about_us.php"><i class="fas fa-info-circle"></i> About Us</a></li>
          <li><a href="FAQ.php"><i class="fas fa-question"></i> FAQ</a></li>
          <li><a href="privacy_policy.php"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
          <li><a href="terms&conditions.php"><i class="fas fa-file-contract"></i> Terms & Conditions</a></li>
          <li><a href="donation_pages/Donations_from _users.php"><i class="fas fa-coffee"></i> Buy Us Coffee</a></li>
        </ul>
      </div>

      <!-- Contact Section -->
      <div class="footer-section contact" aria-labelledby="contact-heading">
        <h3 id="contact-heading">Contact</h3>
        <ul>
          <li><a href="mailto:dasaplus01@gmail.com"><i class="fas fa-envelope"></i> dasaplus01@gmail.com</a></li>
          <li><a href="support.php"><i class="fas fa-heart"></i> Support Us</a></li>
        </ul>
        <div class="social-icons">
          <a href="https://facebook.com" target="_blank" aria-label="Facebook">
            <img src="images&icons/facebook-icon.png" alt="Facebook">
          </a>
          <a href="https://wa.me/2547xxxxxxx" target="_blank" aria-label="WhatsApp">
            <img src="images&icons/whatsapp-icon.png" alt="WhatsApp">
          </a>
          <a href="https://twitter.com" target="_blank" aria-label="Twitter">
            <img src="images&icons/twitter (2).png" alt="Twitter">
          </a>
          <a href="https://instagram.com" target="_blank" aria-label="Instagram">
            <img src="images&icons/instagram (1).png" alt="Instagram">
          </a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      &copy; 2023 Dasaplus. All rights reserved.
    </div>
  </footer>
</body>
</html>
