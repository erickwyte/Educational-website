<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Payment Method</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #ff4b2b, #ff416c);
            color: #fff;
            text-align: center;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .payment-container {
            background: #ffffff;
            color: #333;
            max-width: 400px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        h2 {
            color: #ff416c;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #555;
        }

        .payment-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: 0.3s ease-in-out;
        }

        .payment-option:hover {
            transform: translateY(-3px);
            box-shadow: 3px 3px 15px rgba(0, 0, 0, 0.2);
        }

        .payment-option img {
            width: 50px;
            height: auto;
        }

        .payment-option span {
            flex: 1;
            font-size: 18px;
            font-weight: bold;
            text-align: left;
            margin-left: 15px;
            color: #444;
        }

        /* Responsive Design */
        @media (max-width: 500px) {
            .payment-container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Choose a Payment Method</h2>
        <p>Complete your payment to access full features.</p>

        <form action="m-pesa/mpesa_payment.php" method="POST">
            <button type="submit" class="payment-option">
                <img src="images&icons/m-pesa-icon.png" alt="M-Pesa">
                <span>Pay with M-Pesa</span>
            </button>
        </form>

        <form action="paypal_payment.php" method="POST">
            <button type="submit" class="payment-option">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal">
                <span>Pay with PayPal</span>
            </button>
        </form>

        <form action="stripe_payment.php" method="POST">
            <button type="submit" class="payment-option">
                <img src="https://upload.wikimedia.org/wikipedia/commons/6/6f/Stripe_Logo%2C_revised_2016.svg" alt="Stripe">
                <span>Pay with Stripe</span>
            </button>
        </form>
    </div>
</body>
</html>
