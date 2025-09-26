<?php
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Donate with M-Pesa - Dasaplus</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #34a853;
      --primary-dark: #2b8d42;
      --secondary: #FFC107;
      --dark: #000000;
      --light: #FFFFFF;
      --gray: #B0B0B0;
      --success: #4CAF50;
      --error: #f44336;
      --warning: #ff9800;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--light);
      color: var(--dark);
      line-height: 1.6;
    }
    .header {
      background: linear-gradient(to right, var(--primary-dark), var(--primary));
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
    .header h1 { font-size: 2.8rem; margin-bottom: 15px; font-weight: 700; }
    .header p { font-size: 1.2rem; max-width: 600px; margin: 0 auto; opacity: 0.9; }
    .container {
      max-width: 1000px;
      margin: 40px auto;
      background: var(--light);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    .intro { text-align: center; margin-bottom: 40px; }
    .intro h2 { font-size: 2rem; margin-bottom: 15px; color: var(--primary); }
    .intro p { color: var(--gray); max-width: 700px; margin: 0 auto; }
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
      background: rgba(52, 168, 83, 0.1);
      box-shadow: 0 5px 15px rgba(52, 168, 83, 0.2);
    }
    .donation-option i { font-size: 2.5rem; color: var(--primary); margin-bottom: 15px; }
    .donation-option h3 { font-size: 1.4rem; margin-bottom: 10px; }
    .donation-option p { color: var(--gray); font-size: 0.9rem; }
    .donation-amount { font-size: 1.8rem; font-weight: 700; color: var(--primary); margin: 10px 0; }
    .custom-amount { display: flex; align-items: center; justify-content: center; margin-top: 20px; }
    .currency-symbol { font-size: 1.5rem; font-weight: 600; margin-right: 10px; color: var(--dark); }
    #customAmount {
      width: 150px;
      padding: 12px 15px;
      border: 2px solid var(--gray);
      border-radius: 8px;
      font-size: 1.2rem;
      text-align: center;
    }
    #customAmount:focus { outline: none; border-color: var(--secondary); }
    .payment-form {
      background: rgba(0, 0, 0, 0.05);
      border-radius: 12px;
      padding: 30px;
      margin-top: 30px;
    }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark); }
    .form-group input {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid var(--gray);
      border-radius: 8px;
      font-size: 1rem;
    }
    .form-group input:focus { outline: none; border-color: var(--secondary); }
    .form-group input.error { border-color: var(--error); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    #mpesa-button { 
      display: block; 
      width: 100%; 
      padding: 16px; 
      background: var(--primary); 
      color: var(--light); 
      border: none; 
      border-radius: 8px; 
      font-size: 1.2rem; 
      font-weight: 600; 
      cursor: pointer; 
      transition: all 0.3s; 
    }
    #mpesa-button:hover { background: var(--primary-dark); transform: translateY(-2px); }
    #mpesa-button:disabled { 
      background: var(--gray); 
      cursor: not-allowed; 
      transform: none;
    }
    .secure-notice { text-align: center; margin-top: 20px; color: var(--gray); font-size: 0.9rem; }
    .secure-notice i { color: var(--success); margin-right: 5px; }
    .back-link {
      display: inline-flex;
      align-items: center;
      color: var(--secondary);
      text-decoration: none;
      font-weight: 600;
      margin-top: 30px;
      transition: color 0.2s;
    }
    .back-link:hover { color: var(--primary); }
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
      max-width: 400px;
    }
    .notification.error { background: var(--error); }
    .notification.warning { background: var(--warning); }
    .notification.show { transform: translateY(0); opacity: 1; }
    .loading { display: none; text-align: center; margin: 20px 0; }
    .loading-spinner {
      border: 4px solid rgba(0, 0, 0, 0.1);
      border-radius: 50%;
      border-top: 4px solid var(--primary);
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 0 auto;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .transaction-status {
      display: none;
      text-align: center;
      padding: 15px;
      border-radius: 8px;
      margin-top: 20px;
      background: rgba(0, 0, 0, 0.03);
    }
    .status-processing { color: var(--warning); }
    .status-success { color: var(--success); }
    .status-error { color: var(--error); }
    @media (max-width: 768px) {
      .header h1 { font-size: 2.2rem; }
      .container { padding: 25px; margin: 20px; }
      .form-row { grid-template-columns: 1fr; }
      .donation-options { grid-template-columns: 1fr; }
      .notification { max-width: 300px; right: 10px; left: 10px; }
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>Support Dasaplus with M-Pesa</h1>
    <p>Secure mobile money payments via M-Pesa</p>
  </div>

  <div class="container">
    <div class="intro">
      <h2>Support Our Educational Mission</h2>
      <p>Your donation helps us provide free educational resources, tutorials, and learning opportunities for students worldwide.</p>
    </div>

    <div class="donation-options">
      <div class="donation-option" data-amount="100">
        <i class="fas fa-coffee"></i>
        <h3>Small Support</h3>
        <div class="donation-amount">KES 100</div>
        <p>Quick support for our team</p>
      </div>
      <div class="donation-option" data-amount="250">
        <i class="fas fa-book"></i>
        <h3>Learning Materials</h3>
        <div class="donation-amount">KES 250</div>
        <p>Help us create content</p>
      </div>
      <div class="donation-option" data-amount="500">
        <i class="fas fa-server"></i>
        <h3>Server Support</h3>
        <div class="donation-amount">KES 500</div>
        <p>Keep our platform running</p>
      </div>
      <div class="donation-option selected" data-amount="1000">
        <i class="fas fa-heart"></i>
        <h3>Premium Support</h3>
        <div class="donation-amount">KES 1000</div>
        <p>Make a significant impact</p>
      </div>
    </div>

    <div class="custom-amount">
      <span class="currency-symbol">KES</span>
      <input type="number" id="customAmount" placeholder="Enter custom amount" min="1" step="1">
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
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" placeholder="e.g., 0712345678 or +254712345678" required>
        <small style="color: var(--gray); margin-top: 5px; display: block;">Must be an M-Pesa registered number</small>
      </div>
      <div class="form-group">
        <label for="message">Message (Optional)</label>
        <input type="text" id="message" placeholder="Add a personal message">
      </div>
      <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      
      <div class="loading" id="loadingIndicator">
        <div class="loading-spinner"></div>
        <p>Initiating your donation...</p>
      </div>
      
      <div class="transaction-status" id="transactionStatus">
        <i class="fas fa-sync-alt fa-spin status-processing" id="statusIcon"></i>
        <p id="statusText">Waiting for you to complete the transaction on your phone...</p>
      </div>
      
      <button id="mpesa-button">Donate with M-Pesa</button>
      
      <div class="secure-notice">
        <i class="fas fa-lock"></i> Secure M-Pesa Payment
      </div>
    </div>

    <a href="../Donations_from _users.php" class="back-link">
      <i class="fas fa-arrow-left"></i>&nbsp; Back to Support Page
    </a>
  </div>

  <div class="notification" id="donation-notification"></div>

  <script>
    // DOM Elements
    const donationOptions = document.querySelectorAll('.donation-option');
    const customAmountInput = document.getElementById('customAmount');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const mpesaButton = document.getElementById('mpesa-button');
    const transactionStatus = document.getElementById('transactionStatus');
    const statusIcon = document.getElementById('statusIcon');
    const statusText = document.getElementById('statusText');
    
    // State variables
    let selectedAmount = 1000;
    let checkoutRequestID = null;
    let pollingInterval = null;

    // Initialize donation option selection
    donationOptions.forEach(option => {
      option.addEventListener('click', function() {
        donationOptions.forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        selectedAmount = parseFloat(this.getAttribute('data-amount'));
        customAmountInput.value = '';
        customAmountInput.classList.remove('error');
        updateButtonState();
      });
    });

    // Handle custom amount input
    customAmountInput.addEventListener('input', function() {
      if (this.value) {
        donationOptions.forEach(opt => opt.classList.remove('selected'));
        selectedAmount = parseFloat(this.value);
        this.classList.remove('error');
        updateButtonState();
      }
    });

    // Update button state based on form validity
    function updateButtonState() {
      const firstName = document.getElementById('firstName').value.trim();
      const lastName = document.getElementById('lastName').value.trim();
      const email = document.getElementById('email').value.trim();
      const phone = document.getElementById('phone').value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const phoneRegex = /^(?:\+2547\d{8}|07\d{8})$/;
      
      const isFormValid = firstName && lastName && emailRegex.test(email) && 
                         phoneRegex.test(phone) && selectedAmount && selectedAmount >= 1;
      
      mpesaButton.disabled = !isFormValid;
    }

    // Add input listeners for real-time validation
    const formInputs = document.querySelectorAll('input[required]');
    formInputs.forEach(input => {
      input.addEventListener('blur', function() {
        validateField(this);
        updateButtonState();
      });
      input.addEventListener('input', function() {
        this.classList.remove('error');
        updateButtonState();
      });
    });

    customAmountInput.addEventListener('input', function() {
      this.classList.remove('error');
      updateButtonState();
    });

    // Validate individual field
    function validateField(field) {
      if (field.value.trim() === '') {
        field.classList.add('error');
        return false;
      }
      
      if (field.type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(field.value.trim())) {
          field.classList.add('error');
          return false;
        }
      }
      
      if (field.id === 'phone') {
        const phoneRegex = /^(?:\+2547\d{8}|07\d{8})$/;
        if (!phoneRegex.test(field.value.trim())) {
          field.classList.add('error');
          return false;
        }
      }
      
      field.classList.remove('error');
      return true;
    }

    // Normalize phone number to +254 format
    function normalizePhoneNumber(phone) {
      // Remove any non-digit characters except the leading +
      let cleaned = phone.replace(/[^0-9+]/g, '');
      
      // Convert 07XXXXXXXX to +2547XXXXXXXX
      if (cleaned.startsWith('07') && cleaned.length === 10) {
        cleaned = '+254' + cleaned.substring(1);
      }
      // Ensure +254 format is valid
      if (cleaned.startsWith('+2547') && cleaned.length === 13) {
        return cleaned;
      }
      return null; // Return null if format is invalid
    }

    // Validate entire form
    function validateForm() {
      let isValid = true;
      
      // Validate all required fields
      formInputs.forEach(input => {
        if (!validateField(input)) {
          isValid = false;
        }
      });
      
      // Validate amount
      if (!selectedAmount || selectedAmount < 1) {
        customAmountInput.classList.add('error');
        showNotification('Please select or enter a valid donation amount.', 'error');
        isValid = false;
      } else {
        customAmountInput.classList.remove('error');
      }
      
      // Validate phone format
      let phone = document.getElementById('phone').value.trim();
      phone = normalizePhoneNumber(phone);
      if (!phone) {
        document.getElementById('phone').classList.add('error');
        showNotification('Invalid phone number format. Use 0712345678 or +254712345678.', 'error');
        isValid = false;
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
      } else if (type === 'warning') {
        notification.classList.add('warning');
      }
      notification.classList.add('show');
      
      setTimeout(() => {
        notification.classList.remove('show');
      }, 5000);
    }

    // Update transaction status UI
    function updateTransactionStatus(status, message) {
      statusIcon.className = '';
      
      switch(status) {
        case 'processing':
          statusIcon.className = 'fas fa-sync-alt fa-spin status-processing';
          break;
        case 'success':
          statusIcon.className = 'fas fa-check-circle status-success';
          break;
        case 'error':
          statusIcon.className = 'fas fa-times-circle status-error';
          break;
      }
      
      statusText.textContent = message;
      transactionStatus.style.display = 'block';
    }

    // Stop polling for transaction status
    function stopPolling() {
      if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
      }
    }

    // Poll for transaction status
    async function pollTransactionStatus(checkoutRequestID) {
      const maxAttempts = 20; // Increased from 10 to 20 (approx 1 minute)
      let attempts = 0;

      pollingInterval = setInterval(async () => {
        attempts++;
        try {
          const response = await fetch('mpesa_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
              CheckoutRequestID: checkoutRequestID,
              csrf_token: document.getElementById('csrf_token').value
            })
          });
          
          const result = await response.json();

          if (result.status === 'Success') {
            stopPolling();
            updateTransactionStatus('success', 'Donation successful! Thank you for your support.');
            showNotification('Donation successful! Thank you for your support.', 'success');
            mpesaButton.disabled = false;
            loadingIndicator.style.display = 'none';
          } else if (result.status === 'Failed' || result.error) {
            stopPolling();
            updateTransactionStatus('error', result.error || 'Donation failed. Please try again.');
            showNotification(result.error || 'Donation failed. Please try again.', 'error');
            mpesaButton.disabled = false;
            loadingIndicator.style.display = 'none';
          } else if (attempts >= maxAttempts) {
            stopPolling();
            updateTransactionStatus('error', 'Transaction timed out. Please check your M-Pesa messages or try again.');
            showNotification('Transaction timed out. Please check your M-Pesa messages or try again.', 'error');
            mpesaButton.disabled = false;
            loadingIndicator.style.display = 'none';
          }
        } catch (error) {
          console.error('Polling error:', error);
          if (attempts >= maxAttempts) {
            stopPolling();
            updateTransactionStatus('error', 'Failed to verify transaction. Please try again.');
            showNotification('Failed to verify transaction. Please try again.', 'error');
            mpesaButton.disabled = false;
            loadingIndicator.style.display = 'none';
          }
        }
      }, 3000); // Poll every 3 seconds
    }

    // Handle M-Pesa donation button click
    mpesaButton.addEventListener('click', async function() {
      if (!validateForm()) {
        return;
      }

      let phone = document.getElementById('phone').value.trim();
      phone = normalizePhoneNumber(phone);
      
      if (!phone) {
        document.getElementById('phone').classList.add('error');
        showNotification('Invalid phone number format. Use 0712345678 or +254712345678.', 'error');
        return;
      }

      // Disable button to prevent multiple clicks
      mpesaButton.disabled = true;
      loadingIndicator.style.display = 'block';
      transactionStatus.style.display = 'none';

      const formData = {
        name: `${document.getElementById('firstName').value.trim()} ${document.getElementById('lastName').value.trim()}`,
        email: document.getElementById('email').value.trim(),
        phone: phone,
        amount: selectedAmount.toFixed(2),
        message: document.getElementById('message').value.trim(),
        csrf_token: document.getElementById('csrf_token').value
      };

      try {
        const response = await fetch('mpesa_stk.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(formData)
        });

        const result = await response.json();
        
        if (result.error) {
          console.error('Backend error:', result.error);
          showNotification(result.error, 'error');
          mpesaButton.disabled = false;
          loadingIndicator.style.display = 'none';
          return;
        }

        checkoutRequestID = result.CheckoutRequestID;
        showNotification('STK Push sent to your phone. Please check and confirm with your PIN.', 'success');
        
        // Show transaction status UI
        loadingIndicator.style.display = 'none';
        updateTransactionStatus('processing', 'Waiting for you to complete the transaction on your phone...');
        
        // Start polling for transaction status
        pollTransactionStatus(checkoutRequestID);
      } catch (error) {
        console.error('Fetch error:', error);
        showNotification('Failed to connect to the server. Please try again.', 'error');
        mpesaButton.disabled = false;
        loadingIndicator.style.display = 'none';
      }
    });

    // Initialize button state
    updateButtonState();
  </script>
</body>
</html>