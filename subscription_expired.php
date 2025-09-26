<?php
session_start();
require 'config.php';
?>

<link rel="stylesheet" href="css/style.css">
<?php include 'includes/header.php'; ?>

<div class="container">
    <h2>⚠️ Subscription Expired</h2>
    <p>Oops! Your subscription has expired. Please renew to continue accessing premium content.</p>
    <a href="renew_subscription.php" class="renew-btn">Renew Now</a>
</div>

<style>
.container {
    max-width: 600px;
    margin: 50px auto;
    text-align: center;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    color: #ff4d4d;
}

.renew-btn {
    background-color: #007bff;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    font-weight: bold;
    border-radius: 5px;
    display: inline-block;
    margin-top: 10px;
}

.renew-btn:hover {
    background-color: #0056b3;
}
</style>
