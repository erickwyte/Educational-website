<?php
require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["CheckoutRequestID"])) {
    echo json_encode(["error" => "Missing CheckoutRequestID"]);
    exit;
}

$checkout_request_id = htmlspecialchars(strip_tags($data["CheckoutRequestID"]));

// Get M-Pesa access token
$consumer_key = $_ENV['MPESA_CONSUMER_KEY'];
$consumer_secret = $_ENV['MPESA_CONSUMER_SECRET'];
$credentials = base64_encode("$consumer_key:$consumer_secret");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

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

// Query transaction status
$shortcode = $_ENV['MPESA_SHORTCODE'];
$passkey = $_ENV['MPESA_PASSKEY'];
$timestamp = date('YmdHis');
$password = base64_encode($shortcode . $passkey . $timestamp);

$query_data = [
    "BusinessShortCode" => $shortcode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "CheckoutRequestID" => $checkout_request_id
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $access_token"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$query_response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

error_log("STK Query Request HTTP Code: $httpCode", 3, "error.log");
error_log("STK Query Request Response: $query_response", 3, "error.log");
if ($curlError) {
    error_log("cURL Error in STK Query: $curlError", 3, "error.log");
}

$query_response = json_decode($query_response, true);
if ($httpCode !== 200 || !isset($query_response["ResultCode"])) {
    error_log("M-Pesa STK Query Error: " . json_encode($query_response), 3, "error.log");
    echo json_encode(["error" => "Failed to query transaction status"]);
    exit;
}

$status = $query_response["ResultCode"] == "0" ? "Success" : "Failed";
$error_message = isset($query_response["ResultDesc"]) ? $query_response["ResultDesc"] : "Unknown error";

// Update database
try {
    $stmt = $conn->prepare("UPDATE mpesa_donations SET status = ?, error_message = ? WHERE checkout_request_id = ?");
    $stmt->bind_param("sss", $status, $error_message, $checkout_request_id);
    $stmt->execute();
    $stmt->close();
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage(), 3, "error.log");
    echo json_encode(["error" => "Database error. Please try again later."]);
    exit;
}

if ($status === "Success") {
    // Fetch donation details for email
    $stmt = $conn->prepare("SELECT name, email, amount, message FROM mpesa_donations WHERE checkout_request_id = ?");
    $stmt->bind_param("s", $checkout_request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $donation = $result->fetch_assoc();
    $stmt->close();

    // Send confirmation email
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
        $mail->addAddress($donation['email'], $donation['name']);
        $mail->Subject = "Thank You for Your Donation!";
        $mail->Body = "Dear {$donation['name']},\n\nThank you for your generous donation of KES {$donation['amount']}! " .
                      ($donation['message'] ? "Your message: {$donation['message']}\n\n" : "") .
                      "Your support helps us continue our mission.\n\nBest Regards,\nDasaplus Team";

        $mail->send();
        error_log("Email sent successfully to {$donation['email']}", 3, "error.log");
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo, 3, "error.log");
    }
}

echo json_encode(["status" => $status, "error" => $error_message]);
?>