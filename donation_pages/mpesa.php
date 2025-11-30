<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate via M-PESA</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            text-align: center;
            background-color: #003300;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .instructions {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        
        .qr-code img {
            max-width: 100%;
            height: auto;
            border: 2px solid #003300;
            border-radius: 10px;
            margin: 10px auto;
            display: block;
        }
        
        .amount-suggestion {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin: 15px 0;
        }
        
        button {
            background-color: #003300;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            flex: 1;
            min-width: 100px;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #002200;
        }
        
        .highlight {
            color: #003300;
            font-weight: bold;
        }
        
        footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            padding: 15px;
        }
        
        .phone-number {
            font-size: 20px;
            font-weight: bold;
            color: #003300;
            text-align: center;
            margin: 15px 0;
            padding: 12px;
            background-color: #e6ffe6;
            border-radius: 5px;
            word-break: break-all;
        }
        
        ol {
            padding-left: 20px;
            margin: 15px 0;
        }
        
        li {
            margin-bottom: 10px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header {
                padding: 15px;
                border-radius: 8px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .instructions {
                padding: 15px;
            }
            
            .phone-number {
                font-size: 18px;
                padding: 10px;
            }
            
            button {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .amount-suggestion {
                flex-direction: column;
                gap: 8px;
            }
            
            .amount-suggestion button {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .header {
                padding: 12px;
            }
            
            .header h1 {
                font-size: 1.3rem;
            }
            
            .header p {
                font-size: 0.9rem;
            }
            
            .instructions {
                padding: 12px;
            }
            
            .instructions h2 {
                font-size: 1.2rem;
            }
            
            .phone-number {
                font-size: 16px;
                padding: 8px;
            }
            
            button {
                padding: 8px 12px;
                font-size: 13px;
            }
            
            ol, ul {
                padding-left: 18px;
            }
            
            footer {
                font-size: 11px;
            }
        }
        
        /* Animation for QR code */
        .qr-code img {
            transition: transform 0.3s ease;
        }
        
        .qr-code img:hover {
            transform: scale(1.03);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Support Our Cause with M-PESA</h1>
        <p>Thank you for your generosity! Every contribution helps make a difference.</p>
    </div>

    <div class="instructions">
        <h2>How to Donate</h2>
        <p>Follow these simple steps on your phone:</p>
        <ol>
            <li>Open the <strong>M-PESA</strong> menu on your phone.</li>
            <li>Select <strong>Send Money</strong>.</li>
            <li>Enter the phone number below:</li>
            <div class="phone-number">+254746336294</div>
            <li>Enter the amount you wish to donate.</li>
            <li>Enter your M-PESA PIN and confirm.</li>
            <li>You'll receive an SMS confirmation. Thank you!</li>
        </ol>

        <div class="amount-suggestion">
            <button onclick="setAmount(100)">KSh 100</button>
            <button onclick="setAmount(500)">KSh 500</button>
            <button onclick="setAmount(1000)">KSh 1000</button>
        </div>
        <p><strong>Reference:</strong> Type a note like "Donation" for tracking.</p>
    </div>

    <div class="qr-code">
        <h3>Scan for Donation Details</h3>
        <p>Scan the image below to view the phone number (+254746336294). Then, manually enter it in the M-PESA app under "Send Money."</p>
        <!-- Replace 'placeholder.jpg' with your QR code or image file (e.g., 'qrcode.png') -->
        <img src="../images&icons/QR-code.jpg" alt="Donation QR Code or Image">
    </div>

    <footer>
        <p>All donations are secure via M-PESA.</p>
        <p>Powered by Safaricom M-PESA | Date: September 20, 2025</p>
    </footer>

    <script>
        // Suggestion buttons
        function setAmount(amount) {
            alert('Suggested amount: KSh ' + amount + '. Enter this in M-PESA when sending to +254746336294!');
        }
        
        // Make phone number selectable on mobile devices
        document.querySelector('.phone-number').addEventListener('click', function() {
            const range = document.createRange();
            range.selectNode(this);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
        });
    </script>
</body>
</html>