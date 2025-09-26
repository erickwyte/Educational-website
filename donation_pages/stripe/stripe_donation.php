<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Donate with Stripe - Dasaplus</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #6772e5;
      --primary-dark: #5469d4;
      --secondary: #7b68ee;
      --accent: #ffc439;
      --light-bg: #f8f9fa;
      --dark: #212529;
      --text: #2d2d2d;
      --text-light: #6c757d;
      --success: #28a745;
      --border: #dee2e6;
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
    
    .header {
      background: linear-gradient(to right, var(--primary-dark), var(--primary));
      color: white;
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
      background: white;
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
      color: var(--text-light);
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
      border-color: var(--accent);
    }
    
    .donation-option.selected {
      border-color: var(--primary);
      background: rgba(103, 114, 229, 0.05);
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
      color: var(--text-light);
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
      border: 2px solid var(--border);
      border-radius: 8px;
      font-size: 1.2rem;
      text-align: center;
    }
    
    #customAmount:focus {
      outline: none;
      border-color: var(--primary);
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
      border: 2px solid var(--border);
      border-radius: 8px;
      font-size: 1rem;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
    }
    
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    
    .stripe-btn {
      display: block;
      width: 100%;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 16px;
      font-size: 1.2rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
      margin-top: 20px;
      text-align: center;
      text-decoration: none;
    }
    
    .stripe-btn:hover {
      background: var(--primary-dark);
    }
    
    .secure-notice {
      text-align: center;
      margin-top: 20px;
      color: var(--text-light);
      font-size: 0.9rem;
    }
    
    .secure-notice i {
      color: var(--success);
      margin-right: 5px;
    }
    
    .payment-methods {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 15px;
    }
    
    .payment-method {
      font-size: 2rem;
      color: var(--text-light);
    }
    
    .back-link {
      display: inline-flex;
      align-items: center;
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      margin-top: 30px;
      transition: color 0.2s;
    }
    
    .back-link:hover {
      color: var(--secondary);
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
</head>
<body>
  <div class="header">
    <h1>Support Dasaplus with Stripe</h1>
    <p>Secure credit and debit card payments</p>
  </div>
  
  <div class="container">
    <div class="intro">
      <h2>Make a Secure Donation</h2>
      <p>Your contribution helps us provide free educational resources and learning opportunities for students worldwide.</p>
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
      <input type="number" id="customAmount" placeholder="Enter custom amount" min="1" step="0.01">
    </div>
    
    <div class="payment-form">
      <h3>Donor Information</h3>
      
      <div class="form-row">
        <div class="form-group">
          <label for="firstName">First Name</label>
          <input type="text" id="firstName" placeholder="Your first name">
        </div>
        
        <div class="form-group">
          <label for="lastName">Last Name</label>
          <input type="text" id="lastName" placeholder="Your last name">
        </div>
      </div>
      
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" placeholder="Your email address">
      </div>
      
      <div class="form-group">
        <label for="message">Message (Optional)</label>
        <input type="text" id="message" placeholder="Add a personal message">
      </div>
      
      <!-- Replace with your actual Stripe payment link -->
      <a href="https://buy.stripe.com/test_ABC123" class="stripe-btn">
        <i class="fab fa-stripe"></i> Donate Now
      </a>
      
      <div class="secure-notice">
        <i class="fas fa-lock"></i> Secure Stripe payment
      </div>
      
      <div class="payment-methods">
        <i class="fab fa-cc-visa payment-method"></i>
        <i class="fab fa-cc-mastercard payment-method"></i>
        <i class="fab fa-cc-amex payment-method"></i>
        <i class="fab fa-cc-discover payment-method"></i>
      </div>
    </div>
    
    <a href="../Donations_from _users.php" class="back-link">
      <i class="fas fa-arrow-left"></i>&nbsp; Back to Support Page
    </a>
  </div>

  <script>
    // Set up donation amount selection
    const donationOptions = document.querySelectorAll('.donation-option');
    const customAmountInput = document.getElementById('customAmount');
    let selectedAmount = 100; // Default amount

    donationOptions.forEach(option => {
      option.addEventListener('click', function() {
        // Remove selected class from all options
        donationOptions.forEach(opt => opt.classList.remove('selected'));
        
        // Add selected class to clicked option
        this.classList.add('selected');
        
        // Update selected amount
        selectedAmount = this.getAttribute('data-amount');
        customAmountInput.value = '';
        
        updateStripeLink(selectedAmount);
      });
    });
    
    // Handle custom amount input
    customAmountInput.addEventListener('input', function() {
      if (this.value) {
        // Remove selected class from all options
        donationOptions.forEach(opt => opt.classList.remove('selected'));
        
        // Update selected amount
        selectedAmount = this.value;
        updateStripeLink(selectedAmount);
      }
    });
    
    // In a real implementation, this would update the Stripe link with the selected amount
    function updateStripeLink(amount) {
      // This is a simplified example - in reality you would need to use
      // Stripe's API or payment links with predefined amounts
      console.log(`Selected amount: $${amount}`);
      
      // For a real implementation, you might do something like:
      // const stripeLink = document.querySelector('.stripe-btn');
      // stripeLink.href = `https://buy.stripe.com/...?prefilled_amount=${amount}00`;
    }
    
    // Form validation (simplified)
    document.querySelector('.stripe-btn').addEventListener('click', function(e) {
      const email = document.getElementById('email').value;
      const firstName = document.getElementById('firstName').value;
      
      if (!firstName) {
        e.preventDefault();
        alert('Please enter your first name');
        return;
      }
      
      if (!email || !email.includes('@')) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return;
      }
      
      // In a real implementation, you would submit to Stripe here
      // For this demo, we'll just show a confirmation
      console.log(`Proceeding to Stripe payment of $${selectedAmount}`);
    });
  </script>
</body>
</html>