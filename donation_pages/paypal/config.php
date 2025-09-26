<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = 'localhost';
$db = 'edu_website';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    error_log("Database Connection Error: " . mysqli_connect_error(), 3, "error.log");
    die("An error occurred. Please try again later.");
}

// Now you can access PayPal credentials securely
$paypal_client_id = $_ENV['PAYPAL_CLIENT_ID'];
$paypal_secret = $_ENV['PAYPAL_SECRET'];
?>