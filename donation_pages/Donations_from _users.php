<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Our Educational Platform - Dasaplus</title>
    <style>
        :root {
            --primary-green: #003300;
            --primary-green-hover: #004d00;
            --yellow: #FFD700;
            --yellow-hover: #e6c300;
            --white: #FFFFFF;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
            --text-dark: #222;
            --text-medium: #444;
            --text-light: #666;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 6px 16px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, var(--light-gray) 0%, #e8f5e8 100%);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: var(--white);
            padding: 40px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            text-align: center;
        }
        
        h2 {
            color: var(--primary-green);
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .subtitle {
            color: var(--text-medium);
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .impact-section {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: left;
        }

        .impact-title {
            color: var(--primary-green);
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .impact-list {
            list-style: none;
            padding: 0;
        }

        .impact-list li {
            padding: 8px 0;
            color: var(--text-medium);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .impact-list li:before {
            content: "✓";
            color: var(--primary-green);
            font-weight: bold;
        }

        .payment-label {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 20px;
            display: block;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .payment-btn {
            background: var(--white);
            border: 2px solid var(--border-color);
            padding: 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 120px;
        }

        .payment-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary-green);
        }

        .payment-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .payment-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 16px;
        }

        .payment-desc {
            color: var(--text-light);
            font-size: 13px;
        }

        .mpesa-btn { border-color: #34a853; }
        .mpesa-btn:hover { border-color: #2b8d42; background-color: rgba(52, 168, 83, 0.05); }
        .mpesa-icon { color: #34a853; }

        .paypal-btn { border-color: #ffb900; }
        .paypal-btn:hover { border-color: #e6a700; background-color: rgba(255, 185, 0, 0.05); }
        .paypal-icon { color: #ffb900; }

        .stripe-btn { border-color: #6772e5; }
        .stripe-btn:hover { border-color: #556cd6; background-color: rgba(103, 114, 229, 0.05); }
        .stripe-icon { color: #6772e5; }

        .crypto-btn { border-color: #f7931a; }
        .crypto-btn:hover { border-color: #d67c10; background-color: rgba(247, 147, 26, 0.05); }
        .crypto-icon { color: #f7931a; }

        .footer-text {
            color: var(--text-light);
            font-size: 14px;
            margin-top: 20px;
            line-height: 1.5;
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--light-gray);
            padding: 8px 16px;
            border-radius: 20px;
            color: var(--text-medium);
            font-size: 14px;
            margin-top: 15px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body{
                padding: 20px 0;
            }
            .container {
                padding: 30px;
                margin: 15px;
            }
            
            .payment-grid {
                grid-template-columns: 1fr;
            }
            
            h2 {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
            
            .logo {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            h2 {
                font-size: 22px;
            }
            
            .payment-btn {
                padding: 15px;
                min-height: 100px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    
    
    <h2>Support Our Educational Mission</h2>
    <p class="subtitle">Your donation helps us provide free educational resources, maintain our platform, and support students worldwide.</p>

    <div class="impact-section">
        <div class="impact-title">🌟 Your Impact</div>
        <ul class="impact-list">
            <li>Provide free learning materials to underserved communities</li>
            <li>Maintain and improve our educational platform</li>
            <li>Support development of new features and resources</li>
            <li>Help students access quality education worldwide</li>
        </ul>
    </div>

    <label class="payment-label">Choose Payment Method:</label>

    <div class="payment-grid">
        <div class="payment-btn mpesa-btn" onclick="redirectTo('mpesa/mpesa')">
            <div class="payment-icon mpesa-icon">📱</div>
            <div class="payment-name">M-Pesa</div>
            <div class="payment-desc">Mobile Money</div>
        </div>

        <div class="payment-btn paypal-btn" onclick="redirectTo('paypal/paypal')">
            <div class="payment-icon paypal-icon">💳</div>
            <div class="payment-name">PayPal</div>
            <div class="payment-desc">Cards & PayPal</div>
        </div>

        <div class="payment-btn stripe-btn" onclick="redirectTo('stripe/stripe')">
            <div class="payment-icon stripe-icon">💳</div>
            <div class="payment-name">Credit/Debit Card</div>
            <div class="payment-desc">Secure payments</div>
        </div>

        <div class="payment-btn crypto-btn" onclick="redirectTo('crypto')">
            <div class="payment-icon crypto-icon">₿</div>
            <div class="payment-name">Cryptocurrency</div>
            <div class="payment-desc">Bitcoin & Crypto</div>
        </div>
    </div>

    <p class="footer-text">
        All donations are securely processed and tax-deductible. You will receive a receipt for your records.
    </p>
    
    <div class="secure-badge">
        🔒 Secure & Encrypted Payments
    </div>
</div>

<script>
    function redirectTo(method) {
        // Add animation before redirect
        document.body.style.opacity = '0.8';
        document.body.style.transition = 'opacity 0.3s ease';
        
        setTimeout(() => {
            window.location.href = method + '_donation.php';
        }, 300);
    }
</script>

</body>
</html>