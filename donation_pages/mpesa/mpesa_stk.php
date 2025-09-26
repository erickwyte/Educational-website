<?php
require 'config.php'; // Database connection and .env loading
require 'vendor/autoload.php'; // PHPMailer & Dotenv

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Start session for CSRF protection
session_start();

// Validate CSRF token
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['csrf_token']) || $data['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    error_log("CSRF validation failed", 3, "error.log");
    echo json_encode(["error" => "Invalid CSRF token"]);
    exit;
}

// Validate input fields
if (!isset($data["name"], $data["email"], $data["phone"], $data["amount"])) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$name = htmlspecialchars(strip_tags($data["name"]));
$email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
$phone = preg_replace("/[^0-9+]/", "", $data["phone"]); // Clean phone number
$amount = floatval($data["amount"]);
$message = isset($data["message"]) ? htmlspecialchars(strip_tags($data["message"])) : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email format"]);
    exit;
}
if (!preg_match("/^\+2547\d{8}$/", $phone)) {
    echo json_encode(["error" => "Invalid phone number format. Use +2547XXXXXXXX"]);
    exit;
}
if ($amount < 1) {
    echo json_encode(["error" => "Amount must be at least KES 1"]);
    exit;
}

// Get M-Pesa access token
$consumer_key = $_ENV['MPESA_CONSUMER_KEY'];
$consumer_secret = $_ENV['MPESA_CONSUMER_SECRET'];
$credentials = base64_encode("$consumer_key:$consumer_secret");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable for testing
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable for testing

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

error_log("M-Pesa Token Request HTTP Code: $httpCode", 3, "error.log");
error_log("M-Pesa Token Request Response: $response", 3, "error.log");
if ($curlError) {
    error_log("cURL Error in Token Request: $curlError", 3, "error.log");
}

$tokenResponse = json_decode($response, true);
if ($httpCode !== 200 || !isset($tokenResponse["access_token"])) {
    error_log("M-Pesa Authentication Error: " . json_encode($tokenResponse), 3, "error.log");
    echo json_encode(["error" => "Failed to obtain M-Pesa access token"]);
    exit;
}

$access_token = $tokenResponse["access_token"];

// Initiate STK Push
$shortcode = $_ENV['MPESA_SHORTCODE'];
$passkey = $_ENV['MPESA_PASSKEY'];
$callback_url = $_ENV['MPESA_CALLBACK_URL'];
$timestamp = date('YmdHis');
$password = base64_encode($shortcode . $passkey . $timestamp);

$stk_data = [
    "BusinessShortCode" => $shortcode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => (int)$amount,
    "PartyA" => $phone,
    "PartyB" => $shortcode,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callback_url,
    "AccountReference" => "DASAPLUS",
    "TransactionDesc" => "Donation to Dasaplus" . ($message ? ": $message" : "")
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $access_token"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stk_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$stk_response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

error_log("STK Push Request HTTP Code: $httpCode", 3, "error.log");
error_log("STK Push Request Response: $stk_response", 3, "error.log");
if ($curlError) {
    error_log("cURL Error in STK Push: $curlError", 3, "error.log");
}

$stk_response = json_decode($stk_response, true);
if ($httpCode !== 200 || !isset($stk_response["CheckoutRequestID"])) {
    error_log("M-Pesa STK Push Error: " . json_encode($stk_response), 3, "error.log");
    echo json_encode(["error" => "Failed to initiate STK Push. Check error.log for details."]);
    exit;
}

$checkout_request_id = $stk_response["CheckoutRequestID"];
$status = "Pending";

// Store in Database
try {
    $stmt = $conn->prepare("INSERT INTO mpesa_donations (name, email, phone, amount, checkout_request_id, status, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdsss", $name, $email, $phone, $amount, $checkout_request_id, $status, $message);
    $stmt->execute();
    $stmt->close();
    error_log("Database insert successful for CheckoutRequestID: $checkout_request_id", 3, "error.log");
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage(), 3, "error.log");
    echo json_encode(["error" => "Database error. Please try again later."]);
    exit;
}

echo json_encode(["success" => true, "CheckoutRequestID" => $checkout_request_id]);
?>