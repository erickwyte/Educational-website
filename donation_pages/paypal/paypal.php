<?php
require 'config.php'; // Database connection and .env loading
require 'vendor/autoload.php'; // PHPMailer & Dotenv

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json");

// Start session for CSRF protection
session_start();

// Validate CSRF token
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['csrf_token']) || $data['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(["error" => "Invalid CSRF token"]);
    exit;
}

// Validate input fields
if (!isset($data["name"], $data["email"], $data["amount"])) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$name = htmlspecialchars(strip_tags($data["name"]));
$email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
$amount = floatval($data["amount"]);
$message = isset($data["message"]) ? htmlspecialchars(strip_tags($data["message"])) : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email format"]);
    exit;
}
if ($amount <= 0) {
    echo json_encode(["error" => "Invalid amount entered"]);
    exit;
}

// PayPal Authentication
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v1/oauth2/token");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json", "Accept-Language: en_US"]);
curl_setopt($ch, CURLOPT_USERPWD, "$paypal_client_id:$paypal_secret");
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = json_decode(curl_exec($ch), true);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !isset($response["access_token"])) {
    error_log("PayPal Authentication Error: " . json_encode($response), 3, "error.log");
    echo json_encode(["error" => "Failed to obtain PayPal access token"]);
    exit;
}

$access_token = $response["access_token"];

// Create PayPal Order
$order_data = [
    "intent" => "CAPTURE",
    "purchase_units" => [
        [
            "amount" => [
                "currency_code" => "USD",
                "value" => number_format($amount, 2, '.', '')
            ],
            "description" => "Donation to Dasaplus" . ($message ? ": $message" : "")
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v2/checkout/orders");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $access_token"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$order_response = json_decode(curl_exec($ch), true);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 201 || !isset($order_response["id"])) {
    error_log("PayPal Order Creation Error: " . json_encode($order_response), 3, "error.log");
    echo json_encode(["error" => "Failed to create PayPal order"]);
    exit;
}

$transaction_id = $order_response["id"];
$status = $order_response["status"];

// Store in Database
try {
    $stmt = $conn->prepare("INSERT INTO paypal_donations (name, email, amount, transaction_id, status, message) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsss", $name, $email, $amount, $transaction_id, $status, $message);
    $stmt->execute();
    $stmt->close();
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage(), 3, "error.log");
    echo json_encode(["error" => "Database error. Please try again later."]);
    exit;
}
/*
// Send Appreciation Email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['SMTP_PORT'];

    $mail->setFrom($_ENV['SMTP_FROM'], 'Dasaplus');
    $mail->addAddress($email, $name);
    $mail->Subject = "Thank You for Your Donation!";
    $mail->Body = "Dear $name,\n\nThank you for your generous donation of $$amount! " . 
                  ($message ? "Your message: $message\n\n" : "") . 
                  "Your support helps us continue our mission.\n\nBest Regards,\nDasaplus Team";

    $mail->send();
} catch (Exception $e) {
    error_log("Email Error: " . $mail->ErrorInfo, 3, "error.log");
    // Continue execution even if email fails
}
*/
echo json_encode(["success" => true, "order_id" => $transaction_id]);
?>