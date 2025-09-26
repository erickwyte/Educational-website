<?php
// Safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config.php'; // Database connection

// Set security headers (must be before any HTML)
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Privacy Policy - Dasaplus</title>
 <style nonce="<?php echo $nonce; ?>">
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

    /* Responsive Design */
    @media (max-width: 768px) {
      .main-content {
    
    margin: 0;
    padding: 0;
}
      .container {
        padding: 25px;
        margin: 0;
        border-radius:0;
        box-shadow: none;
        
      }
      
      h1 {
        font-size: 28px;
      }
      
      h2 {
        font-size: 22px;
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
      
      p {
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
      <h1>Privacy Policy</h1>
      <p class="last-updated"><em>Last updated: <?php echo $lastUpdated; ?></em></p>
      
      <h2><span class="section-number">1</span>Introduction</h2>
      <p>Welcome to Dasaplus Education Platform. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services. We are committed to protecting your privacy and ensuring transparency about our data practices.</p>
      
      <h2><span class="section-number">2</span>Information We Collect</h2>
      <p>We may collect the following types of personal information when you interact with our platform:</p>
      <p>• Account Information: Name, email address, phone number, and course details when you register<br>
         • Academic Data: Educational materials you upload, questions you post, and academic preferences<br>
         • Usage Data: Information about how you use our website, including IP address, browser type, and pages visited<br>
         • Communication Data: Messages you send through our platform and customer support inquiries</p>
      
      <h2><span class="section-number">3</span>How We Use Your Information</h2>
      <p>We use your information to provide and improve our services, including:</p>
      <p>• Creating and managing your user account<br>
         • Providing educational resources and materials<br>
         • Sending important notifications and updates about our services<br>
         • Responding to your inquiries and providing customer support<br>
         • Analyzing usage patterns to improve our platform's functionality<br>
         • Ensuring the security and integrity of our services</p>
      
      <h2><span class="section-number">4</span>Data Sharing and Disclosure</h2>
      <p>We respect your privacy and do not sell your personal data to third parties. We may share your information in the following limited circumstances:</p>
      <p>• With your explicit consent for specific purposes<br>
         • With service providers who assist us in operating our platform (under strict confidentiality agreements)<br>
         • When required by law, legal process, or governmental request<br>
         • To protect our rights, privacy, safety, or property, or that of our users<br>
         • In connection with a business transfer, such as a merger or acquisition</p>
      
      <h2><span class="section-number">5</span>Data Security</h2>
      <p>We implement comprehensive security measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction. These measures include:</p>
      <p>• Encryption of sensitive data in transit and at rest<br>
         • Regular security assessments and vulnerability testing<br>
         • Access controls and authentication mechanisms<br>
         • Secure server infrastructure with regular monitoring<br>
         • Employee training on data protection best practices</p>
      
      <h2><span class="section-number">6</span>Your Rights and Choices</h2>
      <p>You have the following rights regarding your personal information:</p>
      <p>• <strong>Access:</strong> Request a copy of the personal data we hold about you<br>
         • <strong>Correction:</strong> Update or correct inaccurate information in your account<br>
         • <strong>Deletion:</strong> Request deletion of your personal data under certain circumstances<br>
         • <strong>Objection:</strong> Object to certain processing activities<br>
         • <strong>Portability:</strong> Request transfer of your data to another service provider<br>
         • <strong>Withdrawal:</strong> Withdraw consent where processing is based on consent</p>
      <p>To exercise these rights, please contact us using the information provided below.</p>
      
      <h2><span class="section-number">7</span>Data Retention</h2>
      <p>We retain your personal information only for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. When we no longer need to use your information, we will securely delete or anonymize it.</p>
      
      <h2><span class="section-number">8</span>Cookies and Tracking Technologies</h2>
      <p>We use cookies and similar tracking technologies to enhance your experience on our website. These technologies help us:</p>
      <p>• Remember your preferences and settings<br>
         • Analyze how our services are used<br>
         • Deliver personalized content and advertisements<br>
         • Measure the effectiveness of our marketing campaigns</p>
      <p>You can control cookies through your browser settings and other tools. However, disabling cookies may affect your ability to use certain features of our platform.</p>
      
      <h2><span class="section-number">9</span>Children's Privacy</h2>
      <p>Our services are not directed to children under the age of 13. We do not knowingly collect personal information from children. If we become aware that we have collected personal information from a child without verification of parental consent, we will take steps to remove that information from our servers.</p>
      
      <h2><span class="section-number">10</span>Changes to This Policy</h2>
      <p>We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements. We will notify you of any material changes by posting the updated policy on this page with a new effective date. We encourage you to review this policy periodically to stay informed about our information practices.</p>
      
      <h2><span class="section-number">11</span>Contact Us</h2>
      <p>If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us at:</p>
      <p>Email: <a href="mailto:dasaplus01@gmail.com" class="contact-email">dasaplus01@gmail.com</a><br>
         We typically respond to inquiries within 2-3 business days.</p>
      
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