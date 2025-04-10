<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once "../config/database.php"; // Ensures $connection is available

// --- Helper function to get settings (optional, but cleans up the main logic) ---
function get_email_settings($connection) {
    $settings = [];
    // Fetch settings specifically prefixed with smtp_
    $query = "SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'";
    $result = $connection->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $result->free();
    } else {
        error_log("Failed to fetch email settings from database: " . $connection->error);
        // Return empty or default array, depending on how critical these are
    }
    return $settings;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --- Generate Case Number ---
    $query = "SELECT case_number FROM cases ORDER BY id DESC LIMIT 1";
    $result = $connection->query($query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_case_number = intval($row["case_number"]);
        $case_number = str_pad($last_case_number + 1, 8, "0", STR_PAD_LEFT);
    } else {
        $case_number = "00000001";
    }
    if ($result) $result->free(); // Free result set

    // --- Get POST Data ---
    $type = mysqli_real_escape_string($connection, trim($_POST["type"]));
    $subject = mysqli_real_escape_string($connection, trim($_POST["subject"]));
    $severity = mysqli_real_escape_string($connection, trim($_POST["severity"]));
    $product_group = mysqli_real_escape_string($connection, trim($_POST["product_group"]));
    $product = mysqli_real_escape_string($connection, trim($_POST["product_name"]));
    $case_owner = $_SESSION["user_id"]; // Assumes user_id is set in session
    $company = mysqli_real_escape_string($connection, trim($_POST["company"]));
    $product_version = mysqli_real_escape_string($connection, trim($_POST["product_version"]));

    // --- File Upload Handling ---
    $upload_dir = "../uploads/";
    $allowed_types = ["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "mp4", "avi", "mov"];
    $max_file_size = 10 * 1024 * 1024; // 10 MB
    $attachment_name = ""; // Initialize as empty string
    $upload_path = ""; // Initialize upload path

    if (!empty($_FILES["attachment"]["name"]) && $_FILES["attachment"]["error"] == UPLOAD_ERR_OK) {
        $file_name = $_FILES["attachment"]["name"];
        $file_tmp = $_FILES["attachment"]["tmp_name"];
        $file_size = $_FILES["attachment"]["size"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            error_log("Invalid file type uploaded: " . $file_ext . " for case attempt by user ID " . $case_owner);
            $_SESSION['case_error'] = "Error: Invalid file type. Allowed types: " . implode(", ", $allowed_types);
            header("Location: ../user/create_case_form.php");
            exit();
        }
        if ($file_size > $max_file_size) {
            error_log("File size exceeds limit: " . $file_size . " for case attempt by user ID " . $case_owner);
             $_SESSION['case_error'] = "Error: File size exceeds 10MB limit.";
            header("Location: ../user/create_case_form.php");
            exit();
        }

        // Create a unique file name to prevent overwrites
        $attachment_name = time() . "_" . uniqid('', true) . "." . $file_ext; // Use uniqueid with entropy
        $upload_path = $upload_dir . $attachment_name;

        if (!move_uploaded_file($file_tmp, $upload_path)) {
            error_log("Failed to move uploaded file to: " . $upload_path . " for case attempt by user ID " . $case_owner);
             $_SESSION['case_error'] = "Error: Failed to upload file. Please try again.";
            header("Location: ../user/create_case_form.php");
            exit();
        }
    } elseif (!empty($_FILES["attachment"]["name"])) {
        // Handle other upload errors
        error_log("File upload error code: " . $_FILES["attachment"]["error"] . " for case attempt by user ID " . $case_owner);
        $_SESSION['case_error'] = "Error during file upload (Code: " . $_FILES["attachment"]["error"] . "). Please try again.";
        header("Location: ../user/create_case_form.php");
        exit();
    }


    // --- Insert Case into Database ---
    $query_insert_case = "INSERT INTO cases (case_number, type, subject, severity, product_group, product, company, product_version, case_owner, attachment)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt_insert_case = $connection->prepare($query_insert_case)) {
        // Note: case_owner should likely be an INT if it's a foreign key ID, adjust 's' if needed.
        // Assuming user_id in session is the correct type for the case_owner column.
        $stmt_insert_case->bind_param("ssssssssis", $case_number, $type, $subject, $severity, $product_group, $product, $company, $product_version, $case_owner, $attachment_name);

        if ($stmt_insert_case->execute()) {
            $case_id = $connection->insert_id; // Get the ID of the inserted case if needed elsewhere
            $stmt_insert_case->close();

            // --- Get Case Owner Details for Email ---
            $case_owner_name = "Valued Customer"; // Default name
            $case_owner_email = ""; // Initialize email

            $query_user = "SELECT full_name, email FROM users WHERE id = ?";
            if ($stmt_user = $connection->prepare($query_user)) {
                $stmt_user->bind_param("i", $case_owner); // Assuming user ID is integer
                if ($stmt_user->execute()) {
                    $result_user = $stmt_user->get_result();
                    if ($row_user = $result_user->fetch_assoc()) {
                        $case_owner_name = $row_user["full_name"];
                        $case_owner_email = $row_user["email"];
                    } else {
                        error_log("Could not find user details for user ID: " . $case_owner . " for case " . $case_number);
                    }
                    $result_user->free();
                } else {
                    error_log("Error executing user select statement for case " . $case_number . ": " . $stmt_user->error);
                }
                $stmt_user->close();
            } else {
                error_log("Error preparing user select statement for case " . $case_number . ": " . $connection->error);
            }

            // --- Send Email Notification to User ---
            if (!empty($case_owner_email)) {

                // *** FETCH EMAIL SETTINGS FROM DATABASE ***
                $email_settings = get_email_settings($connection);

                // --- Prepare Email Content ---
                $plainMessageUser = "Dear " . htmlspecialchars($case_owner_name) . ",\n\n"
                    . "Your issue has been successfully logged as case #" . htmlspecialchars($case_number) . ".\n\n"
                    . "Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\n"
                    . "Thank you,\nTechnical Support Team\n\n"
                    . "Please do not reply to this email. To update your case, please use the support portal.";

                $userEmailBody = "
                    <!DOCTYPE html>
                    <html lang='en'>
                    <head> <meta charset='UTF-8'> <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>Case #" . htmlspecialchars($case_number) . " Created</title>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
                            .container { max-width: 600px; margin: 20px auto; background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
                            h1 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; }
                            p { margin-bottom: 10px; } .highlight { font-weight: bold; color: #0056b3; }
                            .note { font-size: 0.9em; color: #777; font-style: italic; margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;}
                            .footer { font-size: 0.8em; color: #aaa; margin-top: 20px; text-align: center; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h1>Case #" . htmlspecialchars($case_number) . " Created</h1>
                            <p>Dear <span class='highlight'>" . htmlspecialchars($case_owner_name) . "</span>,</p>
                            <p>Your issue has been successfully logged with the following details:</p>
                            <ul>
                                <li><strong>Case Number:</strong> <span class='highlight'>" . htmlspecialchars($case_number) . "</span></li>
                                <li><strong>Subject:</strong> " . htmlspecialchars($subject) . "</li>
                                <li><strong>Type:</strong> " . htmlspecialchars($type) . "</li>
                                <li><strong>Severity:</strong> " . htmlspecialchars($severity) . "</li>
                            </ul>
                            <p>We will review your case and provide an update shortly.</p>
                            <p class='note'><b>Please Note:</b> Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.</p>
                            <p>Thank you,<br>Technical Support Team</p>
                            <p class='footer'><i>Please do not reply to this email. To update your case, please use the support portal.</i></p>
                        </div>
                    </body>
                    </html>";

                $mail = new PHPMailer(true);
                try {
                    // *** CONFIGURE PHPMailer USING DATABASE SETTINGS ***
                    $mail->isSMTP();
                    $mail->Host = $email_settings['smtp_host'] ?? 'smtp.gmail.com'; // Fallback if not set
                    $mail->SMTPAuth = true; // Assumed TRUE if username/password are set in DB
                    $mail->Username = $email_settings['smtp_username'] ?? '';
                    $mail->Password = $email_settings['smtp_password'] ?? '';

                    // Determine encryption based on setting value
                    $secure_type = strtolower($email_settings['smtp_secure'] ?? 'tls'); // Default to tls
                    if ($secure_type === 'tls') {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    } elseif ($secure_type === 'ssl') {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    } else {
                        $mail->SMTPSecure = false; // No encryption
                    }

                    $mail->Port = (int)($email_settings['smtp_port'] ?? 587); // Cast to int, fallback

                    $mail->setFrom(
                        $email_settings['smtp_from_email'] ?? 'noreply@example.com',
                        $email_settings['smtp_from_name'] ?? 'Support Team'
                     );
                    $mail->addAddress($case_owner_email, $case_owner_name);
                    $mail->Subject = "Support Case #" . htmlspecialchars($case_number) . " Created Successfully"; // Slightly more informative subject
                    $mail->isHTML(true);
                    $mail->Body = $userEmailBody;
                    $mail->AltBody = $plainMessageUser; // Add plain text alternative

                    $mail->send();
                    error_log("Confirmation email sent successfully to $case_owner_email for case $case_number.");

                } catch (Exception $e) {
                    // Log detailed error including the settings used (except password)
                     error_log("User Email Error for Case $case_number to $case_owner_email using Host: "
                        . ($mail->Host ?? 'N/A') . ", Port: " . ($mail->Port ?? 'N/A')
                        . ", Secure: " . ($mail->SMTPSecure === false ? 'None' : ($mail->SMTPSecure ?? 'N/A'))
                        . ", Username: " . ($mail->Username ?? 'N/A')
                        . ". PHPMailer Error: {$mail->ErrorInfo}");
                    // Don't stop the script here, still try to save notifications/chat
                }
            } else {
                 error_log("Skipping user email for case $case_number because owner email is empty (User ID: $case_owner).");
            }

            // --- Insert System Chat Message ---
            $system_name = "System"; // Define system sender name
            $chatMessage = "Case #" . htmlspecialchars($case_number) . " created successfully by " . htmlspecialchars($case_owner_name) . ".";
             // Use a concise message for chat, the full text is in notifications/email
             // $plainMessageUser could be used if you prefer the full email text in chat

            $sendMessage = $connection->prepare("INSERT INTO chats (case_number, sender, receiver, message) VALUES (?, ?, ?, ?)");
            if ($sendMessage) {
                // Send chat message from System to the case owner (or make receiver generic if needed)
                $sendMessage->bind_param("ssss", $case_number, $system_name, $case_owner_name, $chatMessage);
                if (!$sendMessage->execute()) {
                    error_log("Error storing system chat message for Case $case_number: " . $sendMessage->error);
                }
                $sendMessage->close();
            } else {
                 error_log("Error preparing system chat message insert for Case $case_number: " . $connection->error);
            }


            // --- Store User Notification in Database ---
            $notificationSubjectUser = "Case #" . htmlspecialchars($case_number) . " Created";
            $insertNotificationUser = $connection->prepare("INSERT INTO notifications (case_id, recipient_username, recipient_email, message_subject, message_body) VALUES (?, ?, ?, ?, ?)");
            if ($insertNotificationUser) {
                 // Use the plain text email body for the notification body
                 $insertNotificationUser->bind_param("sssss", $case_number, $case_owner_name, $case_owner_email, $notificationSubjectUser, $plainMessageUser);
                 if (!$insertNotificationUser->execute()) {
                     error_log("Error storing user notification for Case $case_number: " . $insertNotificationUser->error);
                 }
                 $insertNotificationUser->close();
            } else {
                 error_log("Error preparing user notification insert for Case $case_number: " . $connection->error);
            }


            // --- Prepare and Store Admin Notification in Database ---
             $adminNotificationSubject = "New Case #" . htmlspecialchars($case_number) . " Requires Attention from " . htmlspecialchars($company); // Add company for quick context

             $adminNotificationPlainTextBody = "New Support Case Logged\n"
                . "===================================\n"
                . "A new support case requires review/assignment:\n\n"
                . "Case Number: " . htmlspecialchars($case_number) . "\n"
                . "Submitted By: " . htmlspecialchars($case_owner_name) . " (" . htmlspecialchars($case_owner_email) . ")\n"
                . "Company: " . htmlspecialchars($company) . "\n\n"
                . "--- Case Details ---\n"
                . "Subject: " . htmlspecialchars($subject) . "\n"
                . "Type: " . htmlspecialchars($type) . "\n"
                . "Severity: " . htmlspecialchars($severity) . "\n"
                . "Product Group: " . htmlspecialchars($product_group) . "\n"
                . "Product Name: " . htmlspecialchars($product) . "\n"
                . "Product Version: " . htmlspecialchars($product_version) . "\n"
                . "Attachment: " . ($attachment_name ? htmlspecialchars($attachment_name) : 'None Uploaded') . "\n\n"
                . "Please access the admin dashboard or support portal to view the full case details and manage assignment.\n"
                . "===================================";

            // Insert into admin_notifications table
            $insertAdminNotification = $connection->prepare(
                 "INSERT INTO admin_notifications (case_number, notification_subject, notification_body) VALUES (?, ?, ?)" // Assuming column name is created_at
             );
            if ($insertAdminNotification) {
                $insertAdminNotification->bind_param("sss", $case_number, $adminNotificationSubject, $adminNotificationPlainTextBody);
                if (!$insertAdminNotification->execute()) {
                    error_log("Error storing admin notification for Case $case_number: " . $insertAdminNotification->error);
                }
                $insertAdminNotification->close();
            } else {
                error_log("Error preparing admin notification insert for Case $case_number: " . $connection->error);
            }

            // --- Redirect User on Success ---
            $_SESSION['case_success'] = "Case #" . htmlspecialchars($case_number) . " created successfully!";
            header("Location: ../user/my_cases.php"); // Redirect to my_cases page
            exit();

        } else { // --- Handle Case Insertion Failure ---
            error_log("Error inserting case for user ID " . $case_owner . ": " . $stmt_insert_case->error);
            // Clean up uploaded file if insertion failed
            if (!empty($upload_path) && file_exists($upload_path)) {
                unlink($upload_path);
                error_log("Cleaned up orphaned upload file: " . $upload_path);
            }
            $_SESSION['case_error'] = "Failed to create case due to a database error. Please try again.";
            header("Location: ../user/create_case_form.php");
            exit();
        }
    } else { // --- Handle Statement Preparation Failure ---
        error_log("Error preparing case insert statement: " . $connection->error);
        $_SESSION['case_error'] = "Failed to prepare case creation due to a system error. Please contact support.";
        header("Location: ../user/create_case_form.php");
        exit();
    }

} else { // --- Handle Invalid Request Method ---
    // Redirect if not a POST request
    header("Location: ../user/create_case_form.php");
    exit();
}

// Close connection if it's open (though script usually exits before this)
if (isset($connection) && $connection instanceof mysqli) {
    $connection->close();
}
?>