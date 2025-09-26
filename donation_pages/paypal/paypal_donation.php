<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Donate with PayPal - Dasaplus</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #1a73e8;
      --primary-dark: #002200;
      --secondary: #FFC107;
      --dark: #000000;
      --light: #FFFFFF;
      --gray: #B0B0B0;
      --success: #4CAF50;
      --error: #f44336;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--light);
      color: var(--dark);
      line-height: 1.6;
    }
    
    .header {
      background: var(--primary);
      color: var(--light);
      text-align: center;
      padding: 60px 20px;
      position: relative;
      overflow: hidden;
    }
    
    .header::before {
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
    
    .header h1 {
      font-size: 2.8rem;
      margin-bottom: 15px;
      font-weight: 700;
    }
    
    .header p {
      font-size: 1.2rem;
      max-width: 600px;
      margin: 0 auto;
      opacity: 0.9;
    }
    
    .container {
      max-width: 1000px;
      margin: 40px auto;
      background: var(--light);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .intro {
      text-align: center;
      margin-bottom: 40px;
    }
    
    .intro h2 {
      font-size: 2rem;
      margin-bottom: 15px;
      color: var(--primary);
    }
    
    .intro p {
      color: var(--gray);
      max-width: 700px;
      margin: 0 auto;
    }
    
    .donation-options {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    
    .donation-option {
      background: rgba(0, 0, 0, 0.05);
      border-radius: 12px;
      padding: 25px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }
    
    .donation-option:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      border-color: var(--secondary);
    }
    
    .donation-option.selected {
      border-color: var(--primary);
      background: rgba(0, 51, 0, 0.05);
    }
    
    .donation-option i {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 15px;
    }
    
    .donation-option h3 {
      font-size: 1.4rem;
      margin-bottom: 10px;
    }
    
    .donation-option p {
      color: var(--gray);
      font-size: 0.9rem;
    }
    
    .donation-amount {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
      margin: 10px 0;
    }
    
    .custom-amount {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 20px;
    }
    
    .currency-symbol {
      font-size: 1.5rem;
      font-weight: 600;
      margin-right: 10px;
      color: var(--dark);
    }
    
    #customAmount {
      width: 150px;
      padding: 12px 15px;
      border: 2px solid var(--gray);
      border-radius: 8px;
      font-size: 1.2rem;
      text-align: center;
    }
    
    #customAmount:focus {
      outline: none;
      border-color: var(--secondary);
    }
    
    .payment-form {
      background: rgba(0, 0, 0, 0.05);
      border-radius: 12px;
      padding: 30px;
      margin-top: 30px;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--dark);
    }
    
    .form-group input {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid var(--gray);
      border-radius: 8px;
      font-size: 1rem;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: var(--secondary);
    }
    
    .form-group input.error {
      border-color: var(--error);
    }
    
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    
    #paypal-button-container {
      display: block;
      width: 100%;
      margin-top: 20px;
    }
    
    .secure-notice {
      text-align: center;
      margin-top: 20px;
      color: var(--gray);
      font-size: 0.9rem;
    }
    
    .secure-notice i {
      color: var(--success);
      margin-right: 5px;
    }
    
    .back-link {
      display: inline-flex;
      align-items: center;
      color: var(--secondary);
      text-decoration: none;
      font-weight: 600;
      margin-top: 30px;
      transition: color 0.2s;
    }
    
    .back-link:hover {
      color: var(--primary);
    }
    
    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 25px;
      background: var(--success);
      color: var(--light);
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transform: translateY(-100px);
      opacity: 0;
      transition: transform 0.3s, opacity 0.3s;
      z-index: 1000;
    }
    
    .notification.error {
      background: var(--error);
    }
    
    .notification.show {
      transform: translateY(0);
      opacity: 1;
    }
    
    .loading {
      display: none;
      text-align: center;
      margin: 20px 0;
    }
    
    .loading-spinner {
      border: 4px solid rgba(0, 0, 0, 0.1);
      border-radius: 50%;
      border-top: 4px solid var(--primary);
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 0 auto;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    @media (max-width: 768px) {
      .header h1 {
        font-size: 2.2rem;
      }
      
      .container {
        padding: 25px;
        margin: 20px;
      }
      
      .form-row {
        grid-template-columns: 1fr;
      }
      
      .donation-options {
        grid-template-columns: 1fr;
      }
    }
  </style>
  <script src="https://www.paypal.com/sdk/js?client-id=AXqLsO56NJNkVwgapO7Zi0bjuC5utKpRDocOi83KbHXVjOvy8execGp89NO3p3F-gsATA8klpjnxNLxs&currency=USD"></script>
</head>
<body>
  <div class="header">
    <h1>Support Dasaplus with PayPal</h1>
    <p>Secure global payments with PayPal</p>
  </div>
  
  <div class="container">
    <div class="intro">
      <h2>Support Our Educational Mission</h2>
      <p>Your donation helps us provide free educational resources, tutorials, and learning opportunities for students worldwide.</p>
    </div>
    
    <div class="donation-options">
      <div class="donation-option" data-amount="10">
        <i class="fas fa-coffee"></i>
        <h3>Buy us a coffee</h3>
        <div class="donation-amount">$10</div>
        <p>Quick support for our team</p>
      </div>
      
      <div class="donation-option" data-amount="25">
        <i class="fas fa-book"></i>
        <h3>Learning materials</h3>
        <div class="donation-amount">$25</div>
        <p>Help us create content</p>
      </div>
      
      <div class="donation-option" data-amount="50">
        <i class="fas fa-server"></i>
        <h3>Server support</h3>
        <div class="donation-amount">$50</div>
        <p>Keep our platform running</p>
      </div>
      
      <div class="donation-option selected" data-amount="100">
        <i class="fas fa-heart"></i>
        <h3>Premium support</h3>
        <div class="donation-amount">$100</div>
        <p>Make a significant impact</p>
      </div>
    </div>
    
    <div class="custom-amount">
      <span class="currency-symbol">$</span>
      <input type="number" id="customAmount" placeholder="Enter custom amount" min="0.01" step="0.01">
    </div>
    
    <div class="payment-form">
      <h3>Donor Information</h3>
      
      <div class="form-row">
        <div class="form-group">
          <label for="firstName">First Name</label>
          <input type="text" id="firstName" placeholder="Your first name" required>
        </div>
        
        <div class="form-group">
          <label for="lastName">Last Name</label>
          <input type="text" id="lastName" placeholder="Your last name" required>
        </div>
      </div>
      
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" placeholder="Your email address" required>
      </div>
      
      <div class="form-group">
        <label for="message">Message (Optional)</label>
        <input type="text" id="message" placeholder="Add a personal message">
      </div>
      
      <input type="hidden" id="csrf_token" name="csrf_token" value="<?php session_start(); echo $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>">
      
      <div class="loading" id="loadingIndicator">
        <div class="loading-spinner"></div>
        <p>Processing your donation...</p>
      </div>
      
      <div id="paypal-button-container"></div>
      
      <div class="secure-notice">
        <i class="fas fa-lock"></i> This is a secure PayPal payment
      </div>
    </div>
    
    <a href="../Donations_from _users.php" class="back-link">
      <i class="fas fa-arrow-left"></i>&nbsp; Back to Support Page
    </a>
  </div>
  
  <div class="notification" id="donation-notification">
    Processing your donation...
  </div>

  <script>
    // Set up donation amount selection
    const donationOptions = document.querySelectorAll('.donation-option');
    const customAmountInput = document.getElementById('customAmount');
    const loadingIndicator = document.getElementById('loadingIndicator');
    let selectedAmount = 100; // Default amount

    donationOptions.forEach(option => {
      option.addEventListener('click', function() {
        donationOptions.forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        selectedAmount = parseFloat(this.getAttribute('data-amount'));
        customAmountInput.value = '';
        customAmountInput.classList.remove('error');
      });
    });

    customAmountInput.addEventListener('input', function() {
      if (this.value) {
        donationOptions.forEach(opt => opt.classList.remove('selected'));
        selectedAmount = parseFloat(this.value);
        this.classList.remove('error');
      }
    });

    // Validate form inputs
    function validateForm() {
      const firstName = document.getElementById('firstName').value.trim();
      const lastName = document.getElementById('lastName').value.trim();
      const email = document.getElementById('email').value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      let isValid = true;

      if (!firstName) {
        document.getElementById('firstName').classList.add('error');
        showNotification('Please enter your first name.', 'error');
        isValid = false;
      } else {
        document.getElementById('firstName').classList.remove('error');
      }

      if (!lastName) {
        document.getElementById('lastName').classList.add('error');
        showNotification('Please enter your last name.', 'error');
        isValid = false;
      } else {
        document.getElementById('lastName').classList.remove('error');
      }

      if (!emailRegex.test(email)) {
        document.getElementById('email').classList.add('error');
        showNotification('Please enter a valid email address.', 'error');
        isValid = false;
      } else {
        document.getElementById('email').classList.remove('error');
      }

      if (!selectedAmount || selectedAmount <= 0) {
        customAmountInput.classList.add('error');
        showNotification('Please select or enter a valid donation amount.', 'error');
        isValid = false;
      } else {
        customAmountInput.classList.remove('error');
      }

      return isValid;
    }

    // Show notification
    function showNotification(message, type = 'success') {
      const notification = document.getElementById('donation-notification');
      notification.textContent = message;
      notification.className = 'notification';
      if (type === 'error') {
        notification.classList.add('error');
      }
      notification.classList.add('show');
      setTimeout(() => {
        notification.classList.remove('show');
      }, 5000);
    }

    // Initialize PayPal Button
    paypal.Buttons({
      style: {
        layout: 'vertical',
        color: 'blue',
        shape: 'rect',
        label: 'donate'
      },
      createOrder: async function(data, actions) {
        if (!validateForm()) {
          return false;
        }

        loadingIndicator.style.display = 'block';

        const formData = {
          name: `${document.getElementById('firstName').value.trim()} ${document.getElementById('lastName').value.trim()}`,
          email: document.getElementById('email').value.trim(),
          amount: selectedAmount.toFixed(2),
          message: document.getElementById('message').value.trim(),
          csrf_token: document.getElementById('csrf_token').value
        };

        try {
          const response = await fetch('paypal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
          });
          const result = await response.json();

          loadingIndicator.style.display = 'none';

          if (result.error) {
            showNotification(result.error, 'error');
            return false;
          }

          return result.order_id;
        } catch (error) {
          loadingIndicator.style.display = 'none';
          showNotification('Failed to connect to the server. Please try again.', 'error');
          return false;
        }
      },
      onApprove: async function(data, actions) {
        loadingIndicator.style.display = 'block';
        try {
          const details = await actions.order.capture();
          loadingIndicator.style.display = 'none';
          showNotification(`Thank you, ${details.payer.name.given_name}, for your $${selectedAmount} donation!`, 'success');
          // Optionally redirect to a thank-you page
           window.location.href = 'https://yourwebsite.com/thankyou.html';
        } catch (error) {
          loadingIndicator.style.display = 'none';
          showNotification('An error occurred during payment capture. Please try again.', 'error');
        }
      },
      onError: function(err) {
        loadingIndicator.style.display = 'none';
        console.error('PayPal error:', err);
        showNotification('An error occurred during payment. Please try again.', 'error');
      },
      onCancel: function(data) {
        loadingIndicator.style.display = 'none';
        showNotification('Donation was cancelled.', 'error');
      }
    }).render('#paypal-button-container');

    // Form validation on input change
    const formInputs = document.querySelectorAll('input[required]');
    formInputs.forEach(input => {
      input.addEventListener('blur', function() {
        if (this.value.trim() === '') {
          this.classList.add('error');
        } else {
          this.classList.remove('error');
        }
      });
      input.addEventListener('input', function() {
        this.classList.remove('error');
      });
    });

    customAmountInput.addEventListener('input', function() {
      this.classList.remove('error');
    });
  </script>
</body>
</html>