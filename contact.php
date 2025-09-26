
<?php
// Database connection and message handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Database connection parameters
    $servername = "localhost";
    $username = "root"; // Change if needed
    $password = ""; // Change if needed
    $dbname = "edu_website";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get form data and sanitize
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = isset($_POST['subject']) ? $conn->real_escape_string($_POST['subject']) : '';
    $message = $conn->real_escape_string($_POST['message']);
    
    // Check if user exists in database to get user_id
    $user_id = null;
    $user_query = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
    $user_result = $conn->query($user_query);
    
    if ($user_result && $user_result->num_rows > 0) {
        $user_row = $user_result->fetch_assoc();
        $user_id = $user_row['id'];
    }
    
    // Insert message into database
    $sql = "INSERT INTO messages (user_id, name, email, subject, message, status) 
            VALUES ('$user_id', '$name', '$email', '$subject', '$message', 'pending')";
    
    if ($conn->query($sql)) {
        // Message inserted successfully
        echo "<script>console.log('Message saved to database successfully');</script>";
    } else {
        // Error handling
        echo "<script>console.error('Error: " . $conn->error . "');</script>";
    }
    
    // Close connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Dasaplus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/contact.css">
</head>
<body>

<?php include 'includes/header.php'; ?>
    
  

  <p class="contact-message">
      Thank you for reaching out to us! We are here to provide you with any assistance you may need. Whether you have a question, feedback, or any other inquiries, our team is ready to help. Please take a moment to fill out the form below with your contact details and message, and we will make sure to get back to you as soon as possible. Your concerns are important to us, and we are committed to providing the best service and support we can offer. We look forward to hearing from you!
  </p>

  <section class="social-channels">
      <h2>Connect With Us</h2>
      <div class="channels-container">
          <div class="channel-card">
              <div class="channel-icon telegram">
                  <i class="fab fa-telegram"></i>
              </div>
              <h3>Telegram Channel</h3>
              <p>Join our Telegram channel for instant updates, announcements, and discussions.</p>
              <a href="https://t.me/yourchannel" class="channel-btn" target="_blank">Join Now</a>
          </div>
          
          <div class="channel-card">
              <div class="channel-icon whatsapp">
                  <i class="fab fa-whatsapp"></i>
              </div>
              <h3>WhatsApp Group</h3>
              <p>Connect with our community on WhatsApp for quick queries and support.</p>
              <a href="https://wa.me/yournumber" class="channel-btn" target="_blank">Join Group</a>
          </div>
          
          <div class="channel-card">
              <div class="channel-icon youtube">
                  <i class="fab fa-youtube"></i>
              </div>
              <h3>YouTube Channel</h3>
              <p>Subscribe to our YouTube channel for tutorials, updates, and informative content.</p>
              <a href="https://youtube.com/yourchannel" class="channel-btn" target="_blank">Subscribe</a>
          </div>
      </div>
  </section>

  <div class="container">
      <form id="contact-form" action="" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo bin2hex(random_bytes(32)); ?>">

          <label for="name">Name</label>
          <input type="text" name="name" placeholder="Your Full Name" required>

          <label for="email">Email</label>
          <input type="email" name="email" placeholder="Your Email Address" required>

          <label for="subject">Subject</label>
          <input type="text" name="subject" placeholder="Optional">

          <label for="message">Message</label>
          <textarea name="message" rows="6" placeholder="Your message here..." required></textarea>

          <button type="submit" name="submit">Send Message</button>
      </form>
  </div>

  <div id="popupMessage" class="popup-message" style="display: none;"></div>

  <footer>
      <p>&copy; <?php echo date("Y"); ?> Dasaplus. All rights reserved.</p>
  </footer>

  <script>
      document.getElementById('contact-form').addEventListener('submit', function(e) {
          e.preventDefault();
          
          // Get form data
          const formData = new FormData(this);
          formData.append('submit', 'true');
          
          // Send form data via AJAX
          fetch('', {
              method: 'POST',
              body: formData
          })
          .then(response => response.text())
          .then(data => {
              // Show success message
              const popup = document.getElementById('popupMessage');
              popup.textContent = 'Your message has been sent successfully!';
              popup.className = 'popup-message popup-success';
              popup.style.display = 'block';
              
              // Reset form
              document.getElementById('contact-form').reset();
              
              // Hide message after 5 seconds
              setTimeout(() => {
                  popup.style.display = 'none';
              }, 5000);
          })
          .catch(error => {
              // Show error message
              const popup = document.getElementById('popupMessage');
              popup.textContent = 'There was an error sending your message. Please try again.';
              popup.className = 'popup-message popup-error';
              popup.style.display = 'block';
              
              // Hide message after 5 seconds
              setTimeout(() => {
                  popup.style.display = 'none';
              }, 5000);
          });
      });
  </script>
</body>
</html>
