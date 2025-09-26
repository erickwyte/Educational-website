<?php
include 'includes/session_check.php';
?>

<link rel="stylesheet" href="css/style.css">
<?php include 'includes/header.php'; ?>

<div class="success-container">
    <h2>✅ Payment Successful</h2>
    <p>Your subscription has been renewed for another 3 months.</p>
    <a href="profile.php" class="btn">Go to Profile</a>
</div>

<style>
.success-container {
    max-width: 600px;
    margin: 50px auto;
    text-align: center;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    color: green;
}

.btn {
    background-color: #007bff;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    font-weight: bold;
    border-radius: 5px;
    display: inline-block;
    margin-top: 10px;
}

.btn:hover {
    background-color: #0056b3;
}
</style>
