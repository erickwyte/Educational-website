<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cryptocurrency Donations - Dasaplus</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #ffa600ff;
      --primary-dark: #002200;
      --secondary: #ffa600ff;
      --dark: #000000;
      --darker: #1A1A1A;
      --light: #FFFFFF;
      --gray: #B0B0B0;
      --success: #4CAF50;
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
      min-height: 100vh;
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
      background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.05"><circle cx="50" cy="50" r="40" fill="black" /></svg>');
      background-size: 80px;
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
      max-width: 900px;
      margin: 40px auto;
      background: var(--light);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    
    .intro {
      text-align: center;
      margin-bottom: 40px;
    }
    
    .intro h2 {
      font-size: 2rem;
      margin-bottom: 15px;
      color: var(--secondary);
    }
    
    .intro p {
      color: var(--gray);
      max-width: 700px;
      margin: 0 auto;
    }
    
    .crypto-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .crypto-card {
      background: var(--light);
      border-radius: 12px;
      padding: 25px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .crypto-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
      border-color: var(--secondary);
    }
    
    .crypto-header {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .crypto-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      font-size: 24px;
      background: var(--primary-dark);
      color: var(--light);
    }
    
    .crypto-name h3 {
      font-size: 1.4rem;
      margin-bottom: 5px;
    }
    
    .crypto-name span {
      color: var(--gray);
      font-size: 0.9rem;
    }
    
    .crypto-address {
      background: rgba(0, 0, 0, 0.05);
      padding: 15px;
      border-radius: 8px;
      font-family: monospace;
      word-break: break-all;
      margin-bottom: 20px;
      position: relative;
      border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .copy-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: var(--primary);
      color: var(--light);
      border: none;
      border-radius: 6px;
      padding: 5px 10px;
      cursor: pointer;
      font-size: 0.8rem;
      transition: background 0.2s;
    }
    
    .copy-btn:hover {
      background: var(--secondary);
    }
    
    .qr-code {
      text-align: center;
      padding: 10px;
      background: var(--light);
      border-radius: 8px;
      display: inline-block;
    }
    
    .qr-container {
      text-align: center;
      margin-top: 15px;
    }
    
    .support-note {
      text-align: center;
      padding: 20px;
      background: var(--light);
      border-radius: 12px;
      margin-top: 30px;
      border: 1px solid rgba(0, 0, 0, 0.1);
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
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      transform: translateY(-100px);
      opacity: 0;
      transition: transform 0.3s, opacity 0.3s;
      z-index: 1000;
    }
    
    .notification.show {
      transform: translateY(0);
      opacity: 1;
    }
    
    @media (max-width: 768px) {
      .header h1 {
        font-size: 2.2rem;
      }
      
      .container {
        padding: 25px;
        margin: 20px;
      }
      
      .crypto-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>Support Dasaplus with Crypto</h1>
    <p>Your cryptocurrency donations help us continue our mission and develop innovative solutions</p>
  </div>
  
  <div class="container">
    <div class="intro">
      <h2>Make a Difference with Crypto</h2>
      <p>Select your preferred cryptocurrency and scan the QR code or copy the address to send your donation. Thank you for your support!</p>
    </div>
    
    <div class="crypto-grid">
      <!-- Bitcoin -->
      <div class="crypto-card">
        <div class="crypto-header">
          <div class="crypto-icon">
            <i class="fab fa-bitcoin"></i>
          </div>
          <div class="crypto-name">
            <h3>Bitcoin</h3>
            <span>BTC</span>
          </div>
        </div>
        <div class="crypto-address">
          1ABCxyzYourBitcoinWalletAddressHere123
          <button class="copy-btn" data-address="1ABCxyzYourBitcoinWalletAddressHere123">
            <i class="fas fa-copy"></i> Copy
          </button>
        </div>
        <div class="qr-container">
          <div class="qr-code" id="qrcode-btc"></div>
        </div>
      </div>
      
      <!-- Ethereum -->
      <div class="crypto-card">
        <div class="crypto-header">
          <div class="crypto-icon">
            <i class="fab fa-ethereum"></i>
          </div>
          <div class="crypto-name">
            <h3>Ethereum</h3>
            <span>ETH</span>
          </div>
        </div>
        <div class="crypto-address">
          0xYourEthereumWalletAddressHere456
          <button class="copy-btn" data-address="0xYourEthereumWalletAddressHere456">
            <i class="fas fa-copy"></i> Copy
          </button>
        </div>
        <div class="qr-container">
          <div class="qr-code" id="qrcode-eth"></div>
        </div>
      </div>
      
      <!-- USDT -->
      <div class="crypto-card">
        <div class="crypto-header">
          <div class="crypto-icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="crypto-name">
            <h3>Tether</h3>
            <span>USDT</span>
          </div>
        </div>
        <div class="crypto-address">
          0xYourUSDTWalletAddressHere789
          <button class="copy-btn" data-address="0xYourUSDTWalletAddressHere789">
            <i class="fas fa-copy"></i> Copy
          </button>
        </div>
        <div class="qr-container">
          <div class="qr-code" id="qrcode-usdt"></div>
        </div>
      </div>
      
   
      
      <!-- Solana -->
      <div class="crypto-card">
        <div class="crypto-header">
          <div class="crypto-icon">
            <i class="fas fa-bolt"></i>
          </div>
          <div class="crypto-name">
            <h3>Solana</h3>
            <span>SOL</span>
          </div>
        </div>
        <div class="crypto-address">
          YourSolanaWalletAddressHere345
          <button class="copy-btn" data-address="YourSolanaWalletAddressHere345">
            <i class="fas fa-copy"></i> Copy
          </button>
        </div>
        <div class="qr-container">
          <div class="qr-code" id="qrcode-sol"></div>
        </div>
      </div>
    </div>
    
    <div class="support-note">
      <p>For donations in other cryptocurrencies or if you need assistance, please <a href="mailto:support@dasaplus.com" style="color: var(--secondary);">contact our support team</a>.</p>
    </div>
    
    <a href="Donations_from _users.php" class="back-link">
      <i class="fas fa-arrow-left"></i>&nbsp; Back to Support Page
    </a>
  </div>
  
  <div class="notification" id="copy-notification">
    Address copied to clipboard!
  </div>

  <script>
    // Initialize QR codes
    new QRCode(document.getElementById("qrcode-btc"), {
      text: "1ABCxyzYourBitcoinWalletAddressHere123",
      width: 120,
      height: 120
    });
    
    new QRCode(document.getElementById("qrcode-eth"), {
      text: "0xYourEthereumWalletAddressHere456",
      width: 120,
      height: 120
    });
    
    new QRCode(document.getElementById("qrcode-usdt"), {
      text: "0xYourUSDTWalletAddressHere789",
      width: 120,
      height: 120
    });
    
    new QRCode(document.getElementById("qrcode-ada"), {
      text: "addr1YourCardanoWalletAddressHere012",
      width: 120,
      height: 120
    });
    
    new QRCode(document.getElementById("qrcode-sol"), {
      text: "YourSolanaWalletAddressHere345",
      width: 120,
      height: 120
    });
    
    // Copy functionality
    document.querySelectorAll('.copy-btn').forEach(button => {
      button.addEventListener('click', function() {
        const address = this.getAttribute('data-address');
        navigator.clipboard.writeText(address).then(() => {
          const notification = document.getElementById('copy-notification');
          notification.textContent = `${this.parentElement.querySelector('h3').textContent} address copied!`;
          notification.classList.add('show');
          
          setTimeout(() => {
            notification.classList.remove('show');
          }, 3000);
        });
      });
    });
  </script>
</body>
</html>