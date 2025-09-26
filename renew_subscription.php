<?php
require 'config.php';
include 'includes/session_check.php';

$user_id = $_SESSION['user_id'];

// Fetch user subscription details
$sql = "SELECT subscription_end FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$expiry_date = strtotime($user['subscription_end']);
$current_date = time();
$days_left = ($expiry_date - $current_date) / 86400; // Convert to days
?>

<link rel="stylesheet" href="css/style.css">
<?php include 'includes/header.php'; ?>

<div class="subscription-container">
    <h2>🔔 Subscription Renewal</h2>
    
    <p>Your subscription expires on: <strong><?php echo htmlspecialchars($user['subscription_end']); ?></strong></p>

    <?php if ($days_left <= 0): ?>
        <p style="color: red;">⚠️ Your subscription has expired! Renew now to regain access.</p>
    <?php elseif ($days_left <= 5): ?>
        <p style="color: orange;">⚠️ Your subscription is expiring soon! Renew now to avoid interruption.</p>
    <?php else: ?>
        <p>Your subscription is still active, but you can extend it in advance.</p>
    <?php endif; ?>

    <!-- Payment Options -->
    <h3>Select Payment Method</h3>
    <div class="payment-options">
        <a href="mpesa_payment.php" class="payment-btn mpesa">Pay with M-Pesa</a>
        <a href="paypal_payment.php" class="payment-btn paypal">Pay with PayPal</a>
    </div>
</div>

<style>
.subscription-container {
    max-width: 600px;
    margin: 50px auto;
    text-align: center;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    color: #007bff;
}

.payment-options {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 15px;
}

.payment-btn {
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: bold;
}

.mpesa {
    background-color: green;
    color: white;
}

.paypal {
    background-color: #0070ba;
    color: white;
}

.payment-btn:hover {
    opacity: 0.8;
}
</style>
