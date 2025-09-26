<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') : 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#28a745">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Dasaplus University">
<meta name="application-name" content="Dasaplus University">
<meta name="mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
<title>Dasaplus University</title>
 <style>
:root {
  --primary-green: #003300;
  --primary-green-hover: #218838;
  --primary-red: #dc3545;
  --primary-red-hover: #b71c1c;
  --gold: #ffd700;
  --background: #f8f9fa;
  --card-bg: #fff;
  --border-color: #e0e0e0;
  --shadow: 0 4px 12px rgba(40,167,69,0.08);
  --shadow-hover: 0 6px 16px rgba(220,53,69,0.12);
  --text-dark: #222;
  --text-light: #666;
  --transition: all 0.3s ease;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', 'Roboto', sans-serif;
}

html, body {
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  background: var(--background);
  color: var(--text-dark);
  line-height: 1.6;
}
a{
  text-decoration: none;
  color: inherit;
}
.dpu-header {
  background: linear-gradient(90deg, var(--primary-green) 0%, var(--primary-green) 100%);
  color: #fff;
  position: sticky;
  top: 0;
  z-index: 1000;
  transition: transform 0.3s ease;
  border-bottom: 3px solid var(--gold);
  box-shadow: 0 2px 12px rgba(40,167,69,0.10);
  will-change: transform;
  width: 100%;
}

.dpu-header.dpu-hidden {
  transform: translateY(-100%);
}

.dpu-navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: clamp(0.6rem, 1.5vw, 0.8rem) clamp(1rem, 3vw, 1.5rem);
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
  gap: clamp(0.5rem, 1.5vw, 1rem);
}

.dpu-logo {
  display: flex;
  align-items: center;
  gap: clamp(5px, 1vw, 10px);
  flex-shrink: 0;
}

.dpu-logo-text {
  font-size: clamp(1.1rem, 2.5vw, 1.5rem);
  font-weight: 700;
  color: var(--gold);
  letter-spacing: 1px;
  text-shadow: 0 2px 8px rgba(40,167,69,0.10);
  white-space: nowrap;
}

.dpu-nav-links {
  display: flex;
  list-style: none;
  gap: clamp(0.5rem, 1.2vw, 0.8rem);
  margin: 0;
  padding: 0;
  align-items: center;
  flex-grow: 1;
  justify-content: center;
}

.dpu-nav-links a {
  color: #fff;
  text-decoration: none;
  font-weight: 600;
  font-size: clamp(0.85rem, 1.6vw, 1rem);
  padding: clamp(0.4rem, 1vw, 0.6rem) clamp(0.6rem, 1.2vw, 0.8rem);
  border-radius: 24px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(0,0,0,0.08);
  transition: var(--transition);
  white-space: nowrap;
  position: relative;
}

.dpu-nav-links a:hover {
  background: #fff;
  color: var(--primary-green);
  
}



.dpu-nav-links a:hover::after {
  width: 100%;
}

/* Profile */
.dpu-profile-section {
  position: relative;
  display: flex;
  align-items: center;
  gap: clamp(0.4rem, 1vw, 0.8rem);
  border-radius: 24px;
  padding: clamp(0.2rem, 0.6vw, 0.3rem) clamp(0.4rem, 1vw, 0.6rem);
  flex-shrink: 0;
}

.dpu-user-avatar {
  width: clamp(30px, 3.5vw, 36px);
  height: clamp(30px, 3.5vw, 36px);
  border-radius: 50%;
  background: var(--gold);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: var(--primary-green);
  border: 2px solid #fff;
  box-shadow: 0 2px 8px rgba(220,53,69,0.10);
  flex-shrink: 0;
  font-size: clamp(0.8rem, 1.8vw, 0.9rem);
}

.dpu-username {
  font-weight: 700;
  color: #fff;
  letter-spacing: 0.5px;
  font-size: clamp(0.8rem, 1.6vw, 0.9rem);
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dpu-profile-icon {
  background: none;
  border: none;
  color: var(--gold);
  font-size: clamp(1.3rem, 2.8vw, 1.6rem);
  cursor: pointer;
  padding: 0.3rem;
  border-radius: 50%;
  transition: var(--transition);
  flex-shrink: 0;
  min-width: 40px;
  min-height: 40px;
}


.dpu-profile-icon:focus {
  background: #fff;
  color: var(--primary-green);
  transform: scale(1.1);
}

.dpu-logout-btn {
  background: var(--primary-red);
  border: none;
  color: #fff;
  font-weight: 700;
  padding: clamp(0.3rem, 0.8vw, 0.5rem) clamp(0.6rem, 1.5vw, 0.9rem);
  border-radius: 50px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  box-shadow: 0 2px 8px rgba(220,53,69,0.10);
  transition: var(--transition);
  font-size: clamp(0.8rem, 1.6vw, 0.9rem);
  white-space: nowrap;
}

.dpu-logout-btn:hover {
  background: linear-gradient(90deg, var(--primary-green), var(--primary-red));
  transform: scale(1.04);
}

.dpu-logout-btn-in-li {
  display: none;
  background: var(--primary-red);
  border: none;
  color: #fff;
  font-weight: 600;
  padding: clamp(0.7rem, 1.8vw, 0.9rem) clamp(0.5rem, 1.2vw, 0.6rem);
  border-radius: 14px;
  cursor: pointer;
  width: 100%;
  text-align: center;
  transition: var(--transition);
}

.dpu-logout-btn-in-li:hover {
  background: var(--primary-red-hover);
  transform: scale(1.03);
}

.dpu-profile-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: var(--card-bg);
  border-radius: 12px;
  box-shadow: var(--shadow-hover);
  width: clamp(160px, 28vw, 200px);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: var(--transition);
  border: 1px solid var(--border-color);
  overflow: hidden;
  z-index: 1001;
}

.dpu-profile-section:hover .dpu-profile-dropdown,
.dpu-profile-section:focus-within .dpu-profile-dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateY(8px);
}

.dpu-profile-dropdown a {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.7rem 0.9rem;
  color: var(--primary-green);
  text-decoration: none;
  font-weight: 600;
  transition: var(--transition);
  white-space: nowrap;
}

.dpu-profile-dropdown a:hover {
  background: var(--primary-green);
  color: #fff;
  padding-left: 1.3rem;
}

/* Mobile and Tablet */
#dpu-open-sidebar-button,
#dpu-close-sidebar-button {
  display: none;
  background: none;
  border: none;
  color: var(--gold);
  font-size: clamp(1.4rem, 3vw, 1.5rem);
  cursor: pointer;
  padding: clamp(0.3rem, 0.8vw, 0.4rem);
  border-radius: 50%;
  transition: var(--transition);
  min-width: 40px;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

#dpu-open-sidebar-button:hover,
#dpu-close-sidebar-button:hover {
  background: rgba(255,255,255,0.1);
}

#dpu-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(40,167,69,0.12);
  z-index: 999;
  transition: var(--transition);
}

#dpu-overlay.dpu-active {
  display: block;
}

.dpu-auth-links {
  display: none;
}

/* Responsive Breakpoints */
@media (min-width: 1200px) {
  #dpu-open-sidebar-button,
  #dpu-close-sidebar-button {
    display: none !important;
  }
  .dpu-navbar {
    flex-wrap: nowrap;
  }
  .dpu-nav-links {
    flex-wrap: nowrap;
  }
}

@media (max-width: 1200px) {
  .dpu-nav-links {
    position: fixed;
    top: 0;
    right: 0;
    transform: translateX(100%);
    height: 100vh;
    width: min(90vw, 320px);
    max-width: 100vw;
    background: var(--card-bg);
    flex-direction: column;
    padding: clamp(3.5rem, 7vh, 4.5rem) clamp(0.8rem, 2vw, 1rem) clamp(0.8rem, 2vh, 1.5rem);
    gap: clamp(0.6rem, 1.5vw, 0.8rem);
    transition: transform 0.3s cubic-bezier(.77,0,.18,1);
    box-shadow: var(--shadow-hover);
    z-index: 1002;
    border-radius: 0 0 0 28px;
    overflow-y: auto;
    overflow-x: hidden;
  }
  .dpu-auth-links {
    display: block;
  }
  .dpu-logout-btn-in-li {
    display: block;
  }
  .dpu-nav-links.dpu-active {
    transform: translateX(0);
  }
  .dpu-nav-links li {
    width: 100%;
  }
  .dpu-nav-links a {
    color: var(--primary-green);
    background: rgba(40,167,69,0.08);
    padding: clamp(0.8rem, 1.8vw, 1rem) 0.9rem;
    border-radius: 12px;
    font-size: clamp(0.95rem, 2vw, 1rem);
    font-weight: 600;
    width: 100%;
    text-align: left;
    justify-content: flex-start;
  }
  .dpu-nav-links a:hover {
    background: var(--primary-green);
    color: #fff;
    transform: scale(1.03);
  }
  .dpu-nav-links a::after {
    display: none;
  }
  .dpu-profile-section {
    display: none;
  }
  .dpu-logout-btn {
    display: none;
  }
  #dpu-open-sidebar-button,
  #dpu-close-sidebar-button {
    display: flex;
    font-size: clamp(1.5rem, 3.5vw, 1.8rem);
    padding: clamp(0.4rem, 1vw, 0.5rem);
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(40,167,69,0.10);
  }
  #dpu-close-sidebar-button {
    position: absolute;
    top: clamp(0.6rem, 1.5vw, 0.8rem);
    right: clamp(0.6rem, 1.5vw, 0.8rem);
    color: var(--primary-red);
    background: #fff;
    box-shadow: 0 2px 8px rgba(220,53,69,0.10);
    z-index: 1003;
  }
  .dpu-navbar {
    flex-wrap: wrap;
  }
}

@media (max-width: 600px) {
  .dpu-navbar {
    padding: clamp(0.4rem, 1vw, 0.6rem) clamp(0.4rem, 1.2vw, 0.8rem);
    gap: clamp(0.3rem, 0.8vw, 0.5rem);
  }
  .dpu-logo {
    order: 1;
    flex: 1;
  }
  .dpu-logo-text {
    font-size: clamp(1rem, 2vw, 1.1rem);
  }
  #dpu-open-sidebar-button {
    order: 3;
    flex-shrink: 0;
  }
  .dpu-nav-links {
    width: 100vw;
    max-width: 100vw;
    padding: clamp(3rem, 8vh, 3.5rem) clamp(0.4rem, 1.5vw, 0.6rem) clamp(0.6rem, 2vh, 1rem);
    gap: clamp(0.5rem, 1.2vw, 0.7rem);
  }
  .dpu-nav-links a {
    font-size: clamp(0.9rem, 1.8vw, 0.95rem);
    padding: clamp(0.7rem, 1.6vw, 0.9rem) clamp(0.5rem, 1.2vw, 0.6rem);
    border-radius: 10px;
  }
  .dpu-logout-btn-in-li {
    font-size: clamp(0.85rem, 1.8vw, 0.95rem);
    padding: clamp(0.7rem, 1.6vw, 0.9rem) clamp(0.5rem, 1.2vw, 0.6rem);
    border-radius: 10px;
  }
  #dpu-open-sidebar-button,
  #dpu-close-sidebar-button {
    font-size: clamp(1.6rem, 4vw, 1.9rem);
    min-width: 44px;
    min-height: 44px;
    padding: clamp(0.5rem, 1.2vw, 0.6rem);
  }
}

@media (max-width: 480px) {
  .dpu-navbar {
    padding: clamp(0.3rem, 0.8vw, 0.5rem) clamp(0.3rem, 0.8vw, 0.5rem);
    gap: clamp(0.2rem, 0.6vw, 0.4rem);
  }
  .dpu-logo-text {
    font-size: clamp(0.95rem, 1.8vw, 1rem);
  }
  .dpu-nav-links {
    padding: clamp(2.5rem, 7vh, 3rem) clamp(0.3rem, 1vw, 0.4rem) clamp(0.5rem, 1.5vh, 0.8rem);
    gap: clamp(0.4rem, 1vw, 0.6rem);
  }
  .dpu-nav-links a {
    font-size: clamp(0.85rem, 1.6vw, 0.9rem);
    padding: clamp(0.6rem, 1.4vw, 0.8rem) clamp(0.4rem, 1vw, 0.5rem);
  }
  .dpu-logout-btn-in-li {
    padding: clamp(0.6rem, 1.4vw, 0.8rem) clamp(0.4rem, 1vw, 0.5rem);
  }
  #dpu-open-sidebar-button,
  #dpu-close-sidebar-button {
    font-size: clamp(1.4rem, 3.5vw, 1.7rem);
    min-width: 40px;
    min-height: 40px;
    padding: clamp(0.3rem, 0.8vw, 0.4rem);
  }
}

/* Fix for iOS Safari */
@supports (-webkit-touch-callout: none) {
  html, body {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
    position: relative;
  }
  .dpu-navbar {
    width: 100%;
    max-width: 100%;
  }
  .dpu-nav-links {
    width: 100%;
    max-width: 100%;
  }
}
</style>
</head>
<body>
<header class="dpu-header">
<nav class="dpu-navbar">
  <div class="dpu-logo">
    <a href="index.php"><div class="dpu-logo-text">Dasaplus</div></a>
  </div>
  <ul class="dpu-nav-links" id="dpu-nav-links">
    <button id="dpu-close-sidebar-button"><i class="fas fa-times"></i></button>
    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
    <li><a href="notes.php"><i class="fas fa-book"></i> Resources</a></li>
    <li><a href="questions.php"><i class="fas fa-question-circle"></i> Questions</a></li>
    <li><a href="blog.php"><i class="fas fa-blog"></i> Blog</a></li>
    <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
    <li><a href="discussion_forum.php"><i class="fas fa-comments"></i> Forum</a></li>
    <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
    <?php if(!$isLoggedIn): ?>
      <li class="dpu-auth-links"><a href="login.php"><i class="fas fa-sign-in-alt"></i> Log In</a></li>
      <li class="dpu-auth-links"><a href="signup.php"><i class="fas fa-user-plus"></i> Sign Up</a></li>
    <?php else: ?>
      <li>
        <form action="logout.php" method="POST" style="margin:0">
          <button type="submit" class="dpu-logout-btn-in-li"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
      </li>
    <?php endif; ?>
  </ul>
  <div class="dpu-profile-section">
    <?php if($isLoggedIn): ?>
      
      <form action="logout.php" method="POST" style="margin:0">
        <button type="submit" class="dpu-logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
      </form>
    <?php else: ?>
      <button class="dpu-profile-icon"><i class="fas fa-user-circle"></i></button>
      <div class="dpu-profile-dropdown">
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Log In</a>
        <a href="signup.php"><i class="fas fa-user-plus"></i> Sign Up</a>
        <a href="faq.php"><i class="fas fa-info-circle"></i> FAQ</a>
      </div>
    <?php endif; ?>
  </div>
  <button id="dpu-open-sidebar-button"><i class="fas fa-bars"></i></button>
</nav>
</header>
<div id="dpu-overlay"></div>

<script>
const nav = document.getElementById("dpu-nav-links");
const openBtn = document.getElementById("dpu-open-sidebar-button");
const closeBtn = document.getElementById("dpu-close-sidebar-button");
const overlay = document.getElementById("dpu-overlay");
const profileBtn = document.querySelector(".dpu-profile-icon");
const profileDropdown = document.querySelector(".dpu-profile-dropdown");
const header = document.querySelector(".dpu-header");

function openSidebar() {
  nav.classList.add("dpu-active");
  overlay.classList.add("dpu-active");
  document.body.style.overflow = "hidden";
  document.body.style.position = "fixed";
  document.body.style.width = "100%";
  document.body.style.top = "0";
  document.body.style.left = "0";
}

function closeSidebar() {
  nav.classList.remove("dpu-active");
  overlay.classList.remove("dpu-active");
  document.body.style.overflow = "";
  document.body.style.position = "";
  document.body.style.width = "";
  document.body.style.top = "";
  document.body.style.left = "";
  if (profileDropdown) {
    profileDropdown.classList.remove("dpu-active");
  }
}

openBtn.addEventListener("click", openSidebar);
closeBtn.addEventListener("click", closeSidebar);
overlay.addEventListener("click", closeSidebar);

window.addEventListener("resize", () => {
  if (window.innerWidth > 900) {
    closeSidebar();
  }
});

if (profileBtn) {
  profileBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    profileDropdown.classList.toggle("dpu-active");
  });

  document.addEventListener("click", (e) => {
    if (
      profileDropdown.classList.contains("dpu-active") &&
      !profileDropdown.contains(e.target) &&
      !profileBtn.contains(e.target)
    ) {
      profileDropdown.classList.remove("dpu-active");
    }
  });
}

/* Hide header on scroll down, show on scroll up */
let lastScroll = 0;
let ticking = false;
const scrollThreshold = window.matchMedia("(max-width: 600px)").matches ? 150 : 100;
const delay = window.matchMedia("(max-width: 600px)").matches ? 150 : 100;
let lastTime = 0;

window.addEventListener("scroll", () => {
  if (!ticking) {
    window.requestAnimationFrame(() => {
      const now = Date.now();
      if (now - lastTime >= delay) {
        let y = window.scrollY;
        if (Math.abs(y - lastScroll) > scrollThreshold) {
          if (y > lastScroll && y > header.offsetHeight + 20) {
            header.classList.add("dpu-hidden");
          } else if (y < lastScroll) {
            header.classList.remove("dpu-hidden");
          }
          lastScroll = y <= 0 ? 0 : y;
          lastTime = now;
        }
      }
      ticking = false;
    });
    ticking = true;
  }
});

// Prevent horizontal scrolling on touch devices
document.addEventListener('touchstart', function(event) {
  const touch = event.touches[0];
  const startX = touch.clientX;
  document.addEventListener('touchmove', function(e) {
    const currentTouch = e.touches[0];
    const diffX = Math.abs(currentTouch.clientX - startX);
    if (diffX > 10 && !nav.classList.contains('dpu-active')) {
      e.preventDefault();
    }
  }, { passive: false });
}, { passive: true });
</script>
</body>
</html>