<?php
// socia_media_sharing.php  
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Share Dasaplus - Educational Platform</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #003300;
      --primary-dark: #002200;
      --primary-light: #e6ffe6;
      --secondary: #34a853;
      --accent: #FFC107;
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
      padding: 3rem 1rem;
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
      font-size: 2.5rem;
      margin-bottom: 1rem;
      font-weight: 700;
    }
    
    header p {
      font-size: 1.1rem;
      max-width: 600px;
      margin: 0 auto;
      opacity: 0.9;
    }
    
    .container {
      max-width: 1000px;
      margin: 2rem auto;
      padding: 1rem;
    }
    
    .share-section {
      background: white;
      padding: 2.5rem;
      border-radius: 16px;
      box-shadow: var(--card-shadow);
      margin-bottom: 2rem;
      text-align: center;
    }
    
    .share-section h2 {
      color: var(--primary);
      margin-bottom: 1.5rem;
      font-size: 2rem;
      position: relative;
      padding-bottom: 0.5rem;
    }
    
    .share-section h2::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 4px;
      background: var(--accent);
      border-radius: 2px;
    }
    
    .share-section p {
      margin-bottom: 1.5rem;
      color: var(--text);
      font-size: 1.1rem;
      max-width: 700px;
      margin-left: auto;
      margin-right: auto;
    }
    
    .social-platforms {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin: 2.5rem 0;
    }
    
    .social-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: var(--card-shadow);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      text-align: center;
      border: 1px solid #e0e0e0;
    }
    
    .social-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--hover-shadow);
    }
    
    .social-icon {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      display: inline-block;
      width: 70px;
      height: 70px;
      line-height: 70px;
      border-radius: 50%;
      color: white;
    }
    
    .facebook { background: #3b5998; }
    .twitter { background: #1da1f2; }
    .whatsapp { background: #25d366; }
    .linkedin { background: #0077b5; }
    .telegram { background: #0088cc; }
    .email { background: var(--primary); }
    
    .social-card h3 {
      color: var(--primary-dark);
      margin-bottom: 0.5rem;
    }
    
    .social-card p {
      color: var(--text-light);
      font-size: 0.95rem;
      margin-bottom: 1rem;
    }
    
    .share-btn {
      display: inline-block;
      background: var(--primary);
      color: white;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
    }
    
    .share-btn:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: var(--hover-shadow);
    }
    
    .referral-section {
      background: var(--primary-light);
      padding: 2rem;
      border-radius: 12px;
      margin: 2rem 0;
      text-align: center;
    }
    
    .referral-section h3 {
      color: var(--primary);
      margin-bottom: 1rem;
    }
    
    .referral-input {
      display: flex;
      max-width: 600px;
      margin: 0 auto;
    }
    
    .referral-input input {
      flex: 1;
      padding: 12px 15px;
      border: 2px solid #ddd;
      border-radius: 8px 0 0 8px;
      font-size: 1rem;
    }
    
    .referral-input button {
      background: var(--primary);
      color: white;
      border: none;
      padding: 0 1.5rem;
      border-radius: 0 8px 8px 0;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.3s;
    }
    
    .referral-input button:hover {
      background: var(--primary-dark);
    }
    
    .stats {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin: 2rem 0;
      flex-wrap: wrap;
    }
    
    .stat-item {
      text-align: center;
      padding: 1rem;
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 0.5rem;
    }
    
    .stat-label {
      color: var(--text-light);
      font-size: 1rem;
    }
    
    footer {
      text-align: center;
      padding: 2rem 1rem;
      margin-top: 3rem;
      background: var(--dark);
      color: white;
    }
    
    footer p {
      margin: 0;
      opacity: 0.8;
    }
    
    @media (max-width: 768px) {
      header h1 {
        font-size: 2rem;
      }
      
      .share-section {
        padding: 1.5rem;
      }
      
      .social-platforms {
        grid-template-columns: 1fr;
      }
      
      .referral-input {
        flex-direction: column;
      }
      
      .referral-input input {
        border-radius: 8px;
        margin-bottom: 0.5rem;
      }
      
      .referral-input button {
        border-radius: 8px;
        padding: 12px;
      }
      
      .stats {
        flex-direction: column;
        gap: 1rem;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1>Share Dasaplus with Others</h1>
    <p>Help spread knowledge by sharing our educational platform with your friends and community</p>
  </header>

  <div class="container">
    <section class="share-section">
      <h2>Spread the Word</h2>
      <p>Your sharing helps students across universities access quality educational resources. Choose your preferred platform to share Dasaplus with your network.</p>
      
      <div class="stats">
        <div class="stat-item">
          <div class="stat-number" id="shareCount">1,243</div>
          <div class="stat-label">Shares This Week</div>
        </div>
        <div class="stat-item">
          <div class="stat-number" id="userCount">15,897</div>
          <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-item">
          <div class="stat-number" id="resourceCount">2,541</div>
          <div class="stat-label">Learning Resources</div>
        </div>
      </div>
      
      <div class="social-platforms">
        <div class="social-card">
          <div class="social-icon facebook">
            <i class="fab fa-facebook-f"></i>
          </div>
          <h3>Facebook</h3>
          <p>Share with your friends on Facebook</p>
          <button class="share-btn" onclick="shareOnFacebook()">Share on Facebook</button>
        </div>
        
        <div class="social-card">
          <div class="social-icon twitter">
            <i class="fab fa-twitter"></i>
          </div>
          <h3>Twitter</h3>
          <p>Tweet about Dasaplus to your followers</p>
          <button class="share-btn" onclick="shareOnTwitter()">Share on Twitter</button>
        </div>
        
        <div class="social-card">
          <div class="social-icon whatsapp">
            <i class="fab fa-whatsapp"></i>
          </div>
          <h3>WhatsApp</h3>
          <p>Share directly with friends on WhatsApp</p>
          <button class="share-btn" onclick="shareOnWhatsApp()">Share on WhatsApp</button>
        </div>
        
        <div class="social-card">
          <div class="social-icon linkedin">
            <i class="fab fa-linkedin-in"></i>
          </div>
          <h3>LinkedIn</h3>
          <p>Share with your professional network</p>
          <button class="share-btn" onclick="shareOnLinkedIn()">Share on LinkedIn</button>
        </div>
        
        <div class="social-card">
          <div class="social-icon telegram">
            <i class="fab fa-telegram-plane"></i>
          </div>
          <h3>Telegram</h3>
          <p>Share with your Telegram groups and contacts</p>
          <button class="share-btn" onclick="shareOnTelegram()">Share on Telegram</button>
        </div>
        
        <div class="social-card">
          <div class="social-icon email">
            <i class="fas fa-envelope"></i>
          </div>
          <h3>Email</h3>
          <p>Send an email invitation to your contacts</p>
          <button class="share-btn" onclick="shareViaEmail()">Share via Email</button>
        </div>
      </div>
      
      <div class="referral-section">
        <h3>Share via Direct Link</h3>
        <p>Copy and share this link directly with anyone</p>
        <div class="referral-input">
          <input type="text" id="referralLink" value="" readonly>
          <button onclick="copyReferralLink()">Copy</button>
        </div>
        <p id="copyMessage" style="color: var(--primary); margin-top: 10px; display: none;">Link copied to clipboard!</p>
      </div>
    </section>
  </div>

  <footer>
    <p>&copy; 2025 Dasaplus | Empowering Education Through Innovation and Accessibility</p>
  </footer>

  <script>
    // Simulate user authentication status (replace with actual backend logic)
    const isLoggedIn = true; // Set to false for guest users
    const userId = isLoggedIn ? 'user123' : null; // Replace with actual user ID from your auth system

    // Dynamically get the current website URL
    const baseUrl = window.location.origin;
    const shareText = 'Check out Dasaplus - an amazing educational platform with free resources for university students!';
    
    // Determine the share URL based on login status
    const shareUrl = isLoggedIn ? `${baseUrl}?ref=${userId}` : baseUrl;
    
    // Set the referral link input value dynamically
    document.getElementById('referralLink').value = shareUrl;
    
    // Social sharing functions
    function shareOnFacebook() {
      const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
      window.open(url, '_blank', 'width=600,height=400');
      trackShare('facebook');
    }
    
    function shareOnTwitter() {
      const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(shareUrl)}`;
      window.open(url, '_blank', 'width=600,height=400');
      trackShare('twitter');
    }
    
    function shareOnWhatsApp() {
      const url = `https://wa.me/?text=${encodeURIComponent(shareText + ' ' + shareUrl)}`;
      window.open(url, '_blank', 'width=600,height=400');
      trackShare('whatsapp');
    }
    
    function shareOnLinkedIn() {
      const url = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`;
      window.open(url, '_blank', 'width=600,height=400');
      trackShare('linkedin');
    }
    
    function shareOnTelegram() {
      const url = `https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}`;
      window.open(url, '_blank', 'width=600,height=400');
      trackShare('telegram');
    }
    
    function shareViaEmail() {
      const subject = 'Check out Dasaplus - Educational Platform';
      const body = `${shareText}\n\n${shareUrl}`;
      const url = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
      window.location.href = url;
      trackShare('email');
    }
    
    function copyReferralLink() {
      const referralInput = document.getElementById('referralLink');
      referralInput.select();
      referralInput.setSelectionRange(0, 99999); // For mobile devices
      
      navigator.clipboard.writeText(referralInput.value)
        .then(() => {
          const message = document.getElementById('copyMessage');
          message.style.display = 'block';
          setTimeout(() => {
            message.style.display = 'none';
          }, 3000);
          trackShare('link');
        })
        .catch(err => {
          console.error('Failed to copy: ', err);
        });
    }
    
    // Function to track shares (would integrate with analytics in a real application)
    function trackShare(platform) {
      console.log(`Shared on ${platform}`);
      // In a real application, you would send this to your analytics service
      
      // Simulate increasing share count
      const shareCount = document.getElementById('shareCount');
      let count = parseInt(shareCount.textContent.replace(/,/g, ''));
      count += 1;
      shareCount.textContent = count.toLocaleString();
    }
    
    // Animate stats counting on page load
    function animateValue(id, start, end, duration) {
      const obj = document.getElementById(id);
      let startTimestamp = null;
      const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        obj.textContent = value.toLocaleString();
        if (progress < 1) {
          window.requestAnimationFrame(step);
        }
      };
      window.requestAnimationFrame(step);
    }
    
    // Initialize on page load
    window.onload = function() {
      // Animate the stats counting
      animateValue('shareCount', 0, 1243, 2000);
      animateValue('userCount', 0, 15897, 2000);
      animateValue('resourceCount', 0, 2541, 2000);
    };
  </script>
</body>
</html>