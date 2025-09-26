<?php
require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json");

// Log raw callback data
$callback_data = file_get_contents("php://input");
error_log("M-Pesa Callback Data: $callback_data", 3, "error.log");

$callback = json_decode($callback_data, true);
if (!isset($callback["Body"]["stkCallback"]["CheckoutRequestID"])) {
    error_log("Invalid callback data", 3, "error.log");
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "Invalid callback data"]);
    exit;
}

$checkout_request_id = $callback["Body"]["stkCallback"]["CheckoutRequestID"];
$result_code = $callback["Body"]["stkCallback"]["ResultCode"];
$result_desc = $callback["Body"]["stkCallback"]["ResultDesc"];
$status = $result_code == 0 ? "Success" : "Failed";

// Update database
try {
    $stmt = $conn->prepare("UPDATE mpesa_donations SET status = ?, error_message = ? WHERE checkout_request_id = ?");
    $stmt->bind_param("sss", $status, $result_desc, $checkout_request_id);
    $stmt->execute();
    $stmt->close();
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage(), 3, "error.log");
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "Database error"]);
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

echo json_encode(["ResultCode" => 0, "ResultDesc" => "Callback processed"]);
?>