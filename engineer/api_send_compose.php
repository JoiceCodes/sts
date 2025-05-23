<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once __DIR__ . "/../config/database.php"; // Ensure path is correct
require_once __DIR__ . '/../vendor/autoload.php'; // Ensure path is correct

header('Content-Type: application/json');
ini_set('display_errors', 0); // Keep 0 for production, 1 for debugging
error_reporting(E_ALL); // Log all errors
// Increase max execution time for potentially large uploads
set_time_limit(120); // 120 seconds = 2 minutes

// --- Request Method Check ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Only POST is allowed.']);
    exit();
}

// --- Authentication Check ---
if (!isset($_SESSION["user_id"], $_SESSION["user_email"], $_SESSION["user_full_name"])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'error' => 'User not authenticated. Please log in.']);
    exit();
}

// --- Get & Sanitize Input Data ---
$compose_to = filter_input(INPUT_POST, 'compose_to', FILTER_SANITIZE_EMAIL);
$compose_cc_string = filter_input(INPUT_POST, 'compose_cc', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES); // Sanitize CC string
$compose_subject = filter_input(INPUT_POST, 'compose_subject', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
// Sanitize body - allows basic tags if needed, but strips most harmful stuff. Adjust filter if full HTML needed.
$compose_body = filter_input(INPUT_POST, 'compose_body', FILTER_SANITIZE_SPECIAL_CHARS);


// --- Basic Validation ---
if (empty($compose_to) || empty($compose_subject) || empty($compose_body)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'Missing required fields: To, Subject, and Message are required.']);
    exit();
}
if (!filter_var($compose_to, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid primary recipient (To) email address format.']);
    exit();
}

$user_id = $_SESSION["user_id"];
$user_email = $_SESSION["user_email"];
$user_full_name = $_SESSION["user_full_name"];
$app_password = null;
$error_message = null;

// --- Fetch App Password (Error Handling Included) ---
if (isset($connection)) {
    $stmt_app_password = $connection->prepare("SELECT app_password FROM gmail_app_password WHERE user_id = ?");
    if ($stmt_app_password) {
        $stmt_app_password->bind_param("i", $user_id);
        if ($stmt_app_password->execute()) {
            $result_app_password = $stmt_app_password->get_result();
            if ($result_app_password->num_rows > 0) {
                $app_password = $result_app_password->fetch_assoc()['app_password'];
            } else {
                $error_message = "App Password not found for this user.";
            }
        } else {
             $error_message = "DB error executing password fetch: " . $stmt_app_password->error;
        }
        $stmt_app_password->close();
    } else {
        $error_message = "DB error preparing password fetch: " . $connection->error;
    }
} else {
    $error_message = "Database connection failed.";
}

// Check if App Password was retrieved
if (empty($app_password)) {
    http_response_code(500); // Internal Server Error (config issue)
    echo json_encode(['success' => false, 'error' => $error_message ?: 'Could not retrieve app password necessary for sending email.']);
    exit();
}

// --- PHPMailer Initialization and Configuration ---
$mail = new PHPMailer(true);

try {
    // Server settings
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable for detailed SMTP logs during debugging ONLY
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';        // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                    // Enable SMTP authentication
    $mail->Username   = $user_email;             // SMTP username (user's email)
    $mail->Password   = $app_password;           // SMTP password (fetched App Password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable explicit TLS encryption
    $mail->Port       = 587;                            // TCP port to connect to                  // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    $mail->CharSet    = PHPMailer::CHARSET_UTF8;   // Use UTF-8

    // Recipients
    $mail->setFrom($user_email, $user_full_name); // Sender's address and name
    $mail->addAddress($compose_to);               // Primary recipient
    $mail->addReplyTo($user_email, $user_full_name);// Set reply-to address

    // --- Add CC Recipients ---
    if (!empty($compose_cc_string)) {
        $cc_emails = explode(',', $compose_cc_string); // Split string by comma
        foreach ($cc_emails as $cc_email) {
            $cc_email_trimmed = trim($cc_email); // Remove leading/trailing whitespace
            if (!empty($cc_email_trimmed) && filter_var($cc_email_trimmed, FILTER_VALIDATE_EMAIL)) {
                try {
                     $mail->addCC($cc_email_trimmed); // Add valid CC address
                } catch (Exception $e) {
                     // Log error if adding CC fails (e.g., invalid format caught by PHPMailer)
                     error_log("PHPMailer Error adding CC '{$cc_email_trimmed}' for user {$user_email}: " . $e->getMessage());
                }
            } else {
                // Optional: Log invalid CC address attempts if not empty after trimming
                if (!empty($cc_email_trimmed)) {
                     error_log("Invalid CC address format skipped for user {$user_email}: '{$cc_email_trimmed}'");
                }
            }
        }
    }

    // --- Handle Attachments ---
    $attachment_errors = []; // Keep track of attachment issues
    if (isset($_FILES['compose_attachments'])) {
        // Check if it's structured as a multiple file upload
        if (is_array($_FILES['compose_attachments']['name'])) {
            $file_count = count($_FILES['compose_attachments']['name']);
            for ($i = 0; $i < $file_count; $i++) {
                $upload_error = $_FILES['compose_attachments']['error'][$i];
                $original_name = $_FILES['compose_attachments']['name'][$i];
                $tmp_name = $_FILES['compose_attachments']['tmp_name'][$i];

                // Check for actual upload errors
                if ($upload_error === UPLOAD_ERR_OK) {
                    $safe_name = basename($original_name); // Use basename for security
                    // Further sanitize name (optional but recommended)
                    $safe_name = preg_replace('/[\x00-\x1F\x7F]/u', '', $safe_name);
                    $safe_name = str_replace(['/', '\\', '..', ':' , '*', '?', '"', '<', '>', '|'], '_', $safe_name); // Replace more problematic chars

                    try {
                        // Add the attachment using its temporary path and safe name
                        $mail->addAttachment($tmp_name, $safe_name);
                    } catch (Exception $e) {
                        $attachment_errors[] = "Could not attach file '{$safe_name}': " . $e->getMessage();
                        error_log("PHPMailer Attachment Error for user {$user_email}: File '{$safe_name}' - " . $e->getMessage());
                    }
                } elseif ($upload_error !== UPLOAD_ERR_NO_FILE) {
                    // Handle other upload errors (file too large, partial upload, etc.)
                    $error_msg_detail = 'Unknown upload error.';
                     switch ($upload_error) {
                         case UPLOAD_ERR_INI_SIZE:
                         case UPLOAD_ERR_FORM_SIZE: $error_msg_detail = "File is too large."; break;
                         case UPLOAD_ERR_PARTIAL: $error_msg_detail = "File was only partially uploaded."; break;
                         case UPLOAD_ERR_NO_TMP_DIR: $error_msg_detail = "Server missing temporary folder."; break;
                         case UPLOAD_ERR_CANT_WRITE: $error_msg_detail = "Server failed to write file to disk."; break;
                         case UPLOAD_ERR_EXTENSION: $error_msg_detail = "A PHP extension stopped the file upload."; break;
                     }
                    $attachment_errors[] = "Error uploading file '{$original_name}': {$error_msg_detail}";
                    error_log("Attachment upload error for user {$user_email}: File '{$original_name}' - Error code {$upload_error} ({$error_msg_detail})");
                }
            }
        }
        // Note: Single file upload case is technically covered by the array check,
        // as PHP creates arrays even for single files when name ends with []
    }

    // Check if there were critical attachment errors before proceeding
    if (!empty($attachment_errors)) {
        // Decide how to handle: send without attachments, or fail the whole send?
        // For now, let's fail the send if attachments couldn't be processed correctly.
        throw new Exception("Could not process one or more attachments: " . implode("; ", $attachment_errors));
    }


    // --- Set Content ---
    // Determine if body is HTML or Plain Text based on tags (simple check)
    if (strip_tags($compose_body) !== $compose_body) {
        $mail->isHTML(true);                  // Set email format to HTML
        $mail->Body    = nl2br($compose_body); // Convert newlines for HTML view
        $mail->AltBody = strip_tags($compose_body); // Create plain text version
    } else {
        $mail->isHTML(false);                 // Set email format to plain text
        $mail->Body    = $compose_body;
    }
    $mail->Subject = $compose_subject;        // Email subject

    // --- Send the Email ---
    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Email sent successfully!']);

} catch (Exception $e) {
    // Log detailed error
    error_log("Mailer Error (Compose) for user {$user_email} to {$compose_to}: {$mail->ErrorInfo} || Exception: {$e->getMessage()}");

    // Provide a user-friendly error message
    $client_error_message = "Message could not be sent due to a server configuration or network error."; // Default
    if (stripos($mail->ErrorInfo, 'authentication failed') !== false || stripos($mail->ErrorInfo, 'Username and Password not accepted') !== false) {
        $client_error_message = "Authentication failed. Please check your App Password configuration.";
    } elseif (stripos($mail->ErrorInfo, 'invalid address') !== false || stripos($e->getMessage(), 'invalid address') !== false) {
        $client_error_message = "Message could not be sent. One or more recipient addresses (To, Cc) are invalid.";
    } elseif (stripos($mail->ErrorInfo, 'attachment') !== false || stripos($e->getMessage(), 'attachment') !== false) {
         $client_error_message = "Message could not be sent. There was an error processing an attachment.";
    } elseif (stripos($mail->ErrorInfo, 'connection failed') !== false || stripos($mail->ErrorInfo, 'could not connect') !== false) {
         $client_error_message = "Could not connect to the email server. Please try again later.";
    }

    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'error' => $client_error_message]);
}

exit();
?>