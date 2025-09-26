<?php
session_start();
require 'config.php'; // Database connection if needed

// Set security headers
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Terms & Conditions - Dasaplus</title>
  <style>
    :root {
      --primary-green: #003300;
      --primary-green-hover: #004d00;
      --yellow: #FFD700;
      --white: #FFFFFF;
      --light-gray: #f8f9fa;
      --border-color: #e0e0e0;
      --text-dark: #222;
      --text-medium: #444;
      --text-light: #666;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      background-color: var(--light-gray);
      color: var(--text-dark);
      line-height: 1.6;
    }

    .main-content {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
    }

    .empty {
      height: 20px;
    }

    .container {
      background: var(--white);
      border-radius: 12px;
      padding: 40px;
      box-shadow: var(--shadow);
      margin: 20px 0;
    }

    h1 {
      font-size: 36px;
      font-weight: 700;
      color: var(--primary-green);
      margin-bottom: 10px;
      text-align: center;
    }

    .last-updated {
      text-align: center;
      color: var(--text-light);
      margin-bottom: 30px;
      font-style: italic;
      font-size: 16px;
    }

    h2 {
      font-size: 24px;
      font-weight: 600;
      color: var(--primary-green);
      margin: 25px 0 15px 0;
      padding-bottom: 8px;
      border-bottom: 2px solid var(--light-gray);
    }

    p {
      margin: 0 0 15px 0;
      color: var(--text-medium);
      line-height: 1.7;
      font-size: 16px;
    }

   .restrictions {
      margin: 0 0 15px 0;
      padding-left: 30px;
      color: var(--text-medium);
    }

    li {
      margin-bottom: 8px;
      line-height: 1.6;
    }

    a {
      color: var(--primary-green);
      text-decoration: none;
      transition: color 0.3s ease;
    }

    a:hover {
      color: var(--primary-green-hover);
      text-decoration: underline;
    }

    .section-number {
      background-color: var(--primary-green);
      color: var(--white);
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-right: 10px;
      font-weight: 600;
    }

    .back-to-top {
      display: block;
      text-align: center;
      margin-top: 30px;
      padding: 10px;
      background-color: var(--light-gray);
      border-radius: 6px;
      color: var(--primary-green);
      font-weight: 500;
    }

    .contact-email {
      font-weight: 600;
      color: var(--primary-green);
    }

    .important-note {
      background-color: #fff3cd;
      border-left: 4px solid #ffc107;
      padding: 15px;
      margin: 20px 0;
      border-radius: 4px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .container {
        padding: 25px;
        margin: 0;
        box-shadow: none;
        border-radius:0;
      }
      .main-content {
    
    margin: 0 ;
    padding: 0;
}
      
      h1 {
        font-size: 28px;
      }
      
      h2 {
        font-size: 22px;
      }
      
     
      
      .restrictions {
        padding-left: 20px;
      }
    }

    @media (max-width: 480px) {
      .container {
        padding: 20px;
      }
      
      h1 {
        font-size: 24px;
      }
      
      h2 {
        font-size: 20px;
      }
      
      p, li {
        font-size: 15px;
      }
    }
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>
  <div class="empty"></div>

  <div class="main-content">
    <div class="container">
      <?php $lastUpdated = date("F j, Y"); ?>
      <h1>Terms and Conditions</h1>
      <p class="last-updated"><em>Last updated: <?php echo $lastUpdated; ?></em></p>
      
      <div class="important-note">
        <strong>Important:</strong> Please read these Terms and Conditions carefully before using our website. By accessing or using our services, you agree to be bound by these terms.
      </div>
      
      <h2><span class="section-number">1</span>Introduction</h2>
      <p>Welcome to Dasaplus Education Platform. These Terms and Conditions govern your use of our website and services. By accessing or using our platform, you accept these terms in full. If you disagree with any part of these terms, you must not use our website.</p>
      
      <h2><span class="section-number">2</span>License to Use Website</h2>
      <p>Unless otherwise stated, we own the intellectual property rights for all material on Dasaplus. Subject to the license below, all these intellectual property rights are reserved.</p>
      <p>You may view, download, and print pages from the website for your personal use, subject to the following restrictions:</p>
      <ul class="restrictions">
        <li>You must not republish material from this website without proper attribution</li>
        <li>You must not sell, rent, or sub-license material from the website</li>
        <li>You must not reproduce, duplicate, or copy material from this website for commercial purposes</li>
        <li>You must not redistribute content from this website without our express permission</li>
      </ul>
      
      <h2><span class="section-number">3</span>Acceptable Use</h2>
      <p>You must not use our website in any way that causes, or may cause, damage to the website or impairment of the availability or accessibility of the website. Specifically, you must not:</p>
      <ul class="restrictions">
        <li>Use our website in any way that is unlawful, illegal, fraudulent, or harmful</li>
        <li>Conduct any systematic or automated data collection activities without our consent</li>
        <li>Use this website to copy, store, host, transmit, or distribute any material containing viruses or malicious computer software</li>
        <li>Attempt to gain unauthorized access to our website, server, or any connected database</li>
        <li>Engage in any activity that interferes with the proper working of our website</li>
      </ul>
      
      <h2><span class="section-number">4</span>User Accounts</h2>
      <p>To access certain features of our website, you may be required to create an account. When creating an account, you agree to:</p>
      <ul class="restrictions">
        <li>Provide accurate, current, and complete information</li>
        <li>Maintain the security of your password and accept all risks of unauthorized access</li>
        <li>Notify us immediately if you discover or suspect any security breaches related to our website</li>
        <li>Take responsibility for all activities that occur under your account</li>
      </ul>
      
      <h2><span class="section-number">5</span>User Content</h2>
      <p>In these Terms and Conditions, "your user content" means material (including without limitation text, images, audio material, video material, and audio-visual material) that you submit to our website, for whatever purpose.</p>
      <p>You grant to us a worldwide, irrevocable, non-exclusive, royalty-free license to use, reproduce, adapt, publish, translate, and distribute your user content in any existing or future media.</p>
      
      <h2><span class="section-number">6</span>Intellectual Property Rights</h2>
      <p>All content included on our website, such as text, graphics, logos, images, audio clips, digital downloads, and software, is the property of Dasaplus or its content suppliers and protected by international copyright laws.</p>
      <p>The compilation of all content on this site is the exclusive property of Dasaplus and protected by international copyright laws.</p>
      
      <h2><span class="section-number">7</span>Limitations of Liability</h2>
      <p>We will not be liable to you in relation to the contents of, or use of, or otherwise in connection with, this website:</p>
      <ul class="restrictions">
        <li>For any indirect, special, or consequential loss</li>
        <li>For any business losses, loss of revenue, income, profits, or anticipated savings</li>
        <li>For any loss of or corruption of data or database</li>
        <li>For any matters beyond our reasonable control</li>
      </ul>
      
      <h2><span class="section-number">8</span>Indemnification</h2>
      <p>You agree to indemnify, defend, and hold harmless Dasaplus, its officers, directors, employees, agents, and third parties for any losses, costs, liabilities, and expenses relating to or arising out of your use of or inability to use the website, any user postings made by you, or your violation of these Terms and Conditions.</p>
      
      <h2><span class="section-number">9</span>Termination</h2>
      <p>We may terminate or suspend your account and bar access to the website immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever, including without limitation if you breach the Terms and Conditions.</p>
      
      <h2><span class="section-number">10</span>Governing Law</h2>
      <p>These Terms and Conditions shall be governed by and construed in accordance with the laws of the jurisdiction in which our company is registered, without regard to its conflict of law provisions.</p>
      
      <h2><span class="section-number">11</span>Changes to Terms and Conditions</h2>
      <p>We reserve the right, at our sole discretion, to modify or replace these Terms and Conditions at any time. If a revision is material, we will provide at least 30 days' notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.</p>
      
      <h2><span class="section-number">12</span>Contact Information</h2>
      <p>If you have any questions about these Terms and Conditions, please contact us at:</p>
      <p>Email: <a href="mailto:dasaplus01@gmail.com" class="contact-email">dasaplus01@gmail.com</a><br>
         We typically respond to inquiries within 2-3 business days.</p>
      
      <p>Thank you for choosing Dasaplus Education Platform.</p>
      
      <a href="#" class="back-to-top">↑ Back to Top</a>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>

  <script>
    // Smooth scrolling for back to top link
    document.querySelector('.back-to-top').addEventListener('click', function(e) {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  </script>
</body>
</html>