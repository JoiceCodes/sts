<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit();
}
if (!isset($_SESSION["user_id"], $_SESSION["user_email"], $_SESSION["user_full_name"])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit();
}

$reply_to = filter_input(INPUT_POST, 'reply_to', FILTER_SANITIZE_EMAIL);
$reply_subject = filter_input(INPUT_POST, 'reply_subject', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
$reply_body = filter_input(INPUT_POST, 'reply_body', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
$original_message_id = filter_input(INPUT_POST, 'original_message_id', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

if (!$reply_to || !$reply_subject || !$reply_body) {
    echo json_encode(['success' => false, 'error' => 'Missing required reply fields.']);
    exit();
}
if (!filter_var($reply_to, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid recipient email address.']);
    exit();
}

$user_id = $_SESSION["user_id"];
$user_email = $_SESSION["user_email"];
$user_full_name = $_SESSION["user_full_name"];
$app_password = null;
$error_message = null;

if (isset($connection)) {
    $stmt_app_password = $connection->prepare("SELECT app_password FROM gmail_app_password WHERE user_id = ?");
    if ($stmt_app_password) {
        $stmt_app_password->bind_param("i", $user_id);
        $stmt_app_password->execute();
        $result_app_password = $stmt_app_password->get_result();
        if ($result_app_password->num_rows > 0) {
            $app_password = $result_app_password->fetch_assoc()['app_password'];
        } else {
            $error_message = "App Password not configured.";
        }
        $stmt_app_password->close();
    } else {
        $error_message = "DB error preparing config fetch: " . $connection->error;
    }
} else {
    $error_message = "Database connection failed.";
}

if (!$app_password || $error_message) {
    echo json_encode(['success' => false, 'error' => $error_message ?: 'Could not retrieve app password.']);
    exit();
}

$mail = new PHPMailer(true);

try {
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $user_email;
    $mail->Password   = $app_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($user_email, $user_full_name);
    $mail->addAddress($reply_to);
    $mail->addReplyTo($user_email, $user_full_name);

    $mail->isHTML(false);
    $mail->Subject = $reply_subject;
    $mail->Body    = $reply_body;
    $mail->AltBody = $reply_body;

    if ($original_message_id) {
        $mail->addCustomHeader('In-Reply-To', $original_message_id);
        $mail->addCustomHeader('References', $original_message_id);
    }

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Mailer Error for user $user_email: {$mail->ErrorInfo}");
    echo json_encode(['success' => false, 'error' => "Message could not be sent. Please check configuration or App Password."]);
}

exit();
?>