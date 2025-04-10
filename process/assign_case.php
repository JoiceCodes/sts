<?php
session_start();
require_once "../config/database.php"; // Ensures $connection is available
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Adjust path as needed

// --- Helper function to get email settings from the database (mysqli version) ---
function get_email_settings($connection) {
    $settings = [];
    // Fetch settings specifically prefixed with smtp_
    $query = "SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'";
    $result = $connection->query($query); // Use query() for simplicity if not using external input
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
    // --- Input Validation ---
    if (!isset($_POST["case_id"]) || !filter_var($_POST["case_id"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        $_SESSION['error_message'] = "Invalid or missing Case ID.";
        header("Location: ../error_page.php?error=invalid_case_id"); // Adjust redirect as needed
        exit;
    }
    if (!isset($_POST["engineer"]) || !filter_var($_POST["engineer"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        $_SESSION['error_message'] = "Invalid or missing Engineer ID.";
        header("Location: ../error_page.php?error=invalid_engineer_id"); // Adjust redirect as needed
        exit;
    }

    // --- Initialize Variables ---
    $caseId = (int)$_POST["case_id"];
    $engineerId = (int)$_POST["engineer"];
    $caseStatus = "Waiting in Progress"; // Status when assigned
    $caseNumber = null;
    $userName = null;
    $userEmail = null;
    $engineerName = null;
    $errorMessage = null; // Store potential non-fatal errors (like email failure)
    $emailSent = false; // Track email status

    // --- Get the case number (optional but used in email subject) ---
    $getCaseNumber = mysqli_prepare($connection, "SELECT case_number FROM cases WHERE id = ? LIMIT 1");
    if ($getCaseNumber) {
        mysqli_stmt_bind_param($getCaseNumber, "i", $caseId);
        mysqli_stmt_execute($getCaseNumber);
        $getCaseNumberResult = mysqli_stmt_get_result($getCaseNumber);
        if ($row = mysqli_fetch_assoc($getCaseNumberResult)) {
            $caseNumber = $row["case_number"];
        } else {
            error_log("Could not find case number for case ID $caseId.");
        }
        mysqli_free_result($getCaseNumberResult);
        mysqli_stmt_close($getCaseNumber);
    } else {
        error_log("Prepare Error (getCaseNumber): " . mysqli_error($connection));
    }
    $caseNumber = $caseNumber ?? $caseId; // Use Case ID as fallback


    // --- Update case assignment in database ---
    $setCase = mysqli_prepare($connection, "UPDATE cases SET user_id = ?, case_status = ?, last_modified = NOW() WHERE id = ?");
    if (!$setCase) {
        error_log("Prepare Error (setCase): " . mysqli_error($connection));
        $_SESSION['error_message'] = "Database error preparing case assignment.";
        header("Location: ../error_page.php?error=db_prepare");
        exit;
    }
    mysqli_stmt_bind_param($setCase, "isi", $engineerId, $caseStatus, $caseId);
    if (!mysqli_stmt_execute($setCase)) {
        error_log("Execute Error (setCase): " . mysqli_stmt_error($setCase));
        $_SESSION['error_message'] = "Database error assigning case.";
        mysqli_stmt_close($setCase);
        header("Location: ../error_page.php?error=db_execute");
        exit;
    }
    $affectedRows = mysqli_stmt_affected_rows($setCase);
    mysqli_stmt_close($setCase);

    if ($affectedRows === 0) {
        error_log("Case assignment failed (affectedRows=0) for case ID $caseId to engineer ID $engineerId.");
        $_SESSION['error_message'] = "Case not found or already assigned.";
        $folder = ($_SESSION["user_role"] ?? '') === "Technical Head" ? "technical_head" : (($_SESSION["user_role"] ?? '') === "Administrator" ? "administrator" : "dashboard");
        header("Location: ../$folder/new_cases.php?error=notfound");
        exit;
    }

    // --- Fetch Case Owner (User) Details ---
    $queryUser = mysqli_prepare($connection, "SELECT u.full_name, u.email FROM cases c JOIN users u ON c.case_owner = u.id WHERE c.id = ?");
    if($queryUser){
        mysqli_stmt_bind_param($queryUser, "i", $caseId);
        mysqli_stmt_execute($queryUser);
        $resultUser = mysqli_stmt_get_result($queryUser);
        if($rowUser = mysqli_fetch_assoc($resultUser)){
            $userName = $rowUser['full_name'];
            $userEmail = $rowUser['email'];
        } else {
            error_log("Could not fetch case owner details for case ID $caseId.");
        }
        mysqli_free_result($resultUser);
        mysqli_stmt_close($queryUser);
    } else {
        error_log("Prepare Error (queryUser): " . mysqli_error($connection));
    }


    // --- Fetch Assigned Engineer Details ---
    $queryEngineer = mysqli_prepare($connection, "SELECT full_name FROM users WHERE id = ?");
    if($queryEngineer){
        mysqli_stmt_bind_param($queryEngineer, "i", $engineerId);
        mysqli_stmt_execute($queryEngineer);
        $resultEngineer = mysqli_stmt_get_result($queryEngineer);
        if($rowEngineer = mysqli_fetch_assoc($resultEngineer)){
            $engineerName = $rowEngineer['full_name'];
        } else {
            error_log("Could not fetch engineer name for engineer ID $engineerId.");
            $engineerName = "Assigned Engineer"; // Fallback name
        }
        mysqli_free_result($resultEngineer);
        mysqli_stmt_close($queryEngineer);
    } else {
        error_log("Prepare Error (queryEngineer): " . mysqli_error($connection));
        $engineerName = "Assigned Engineer"; // Fallback name
    }


    // --- Send Email Notification ---
    if (!empty($userEmail)) {
        // *** FETCH EMAIL SETTINGS FROM DATABASE ***
        $email_settings = get_email_settings($connection);
        if (empty($email_settings)) {
            error_log("Critical Error: Failed to load email settings from database for case assignment $caseId.");
            $errorMessage = "Case assigned, but email settings failed to load. Notification not sent.";
        } else {
            $mail = new PHPMailer(true);
            try {
                // *** CONFIGURE PHPMailer USING DATABASE SETTINGS ***
                $mail->isSMTP();
                $mail->Host = $email_settings['smtp_host'] ?? 'smtp.example.com';
                $mail->SMTPAuth = true;
                $mail->Username = $email_settings['smtp_username'] ?? '';
                $mail->Password = $email_settings['smtp_password'] ?? '';

                $secure_type = strtolower($email_settings['smtp_secure'] ?? 'tls');
                if ($secure_type === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } elseif ($secure_type === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = false;
                }
                $mail->Port = (int)($email_settings['smtp_port'] ?? 587);
                $mail->setFrom(
                    $email_settings['smtp_from_email'] ?? 'noreply@example.com',
                    $email_settings['smtp_from_name'] ?? 'Technical Support'
                );
                $mail->addAddress($userEmail, $userName ?? 'Valued Customer');

                // --- Email Content ---
                $emailSubject = "Support Case Assigned - Case #" . htmlspecialchars($caseNumber);
                $mail->Subject = $emailSubject;
                $mail->isHTML(true);

                // *** APPLYING THE PREVIOUS EMAIL STYLE ***
                $mail->Body = "
                    <!DOCTYPE html>
                    <html lang='en'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>" . htmlspecialchars($emailSubject) . "</title>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 20px; }
                            .container { max-width: 600px; margin: 0 auto; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                            h1 { color: #2a6496; border-bottom: 2px solid #eef; padding-bottom: 10px; margin-top: 0; font-size: 1.4em; }
                            p { margin-bottom: 15px; }
                            .highlight { font-weight: bold; color: #2a6496; }
                            .footer { margin-top: 25px; padding-top: 15px; border-top: 1px solid #eee; font-size: 0.85em; color: #888; text-align: center; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h1>" . htmlspecialchars($emailSubject) . "</h1>
                            <p>Hello <span class='highlight'>" . htmlspecialchars($userName ?? 'Valued Customer') . "</span>,</p>
                            <p>Your support case <span class='highlight'>#" . htmlspecialchars($caseNumber) . "</span> has been assigned to our engineer: <span class='highlight'>" . htmlspecialchars($engineerName) . "</span>.</p>
                            <p>They will review your case and contact you during regular support hours for further assistance.</p>
                            <p>To help resolve the issue more quickly, please ensure any relevant screenshots or error messages have been provided via the support portal.</p>
                            <p>Thank you for contacting support.</p>
                            <div class='footer'>
                                Best regards,<br>
                                <b>Technical Support Team</b><br>
                                <i>This is an automated message. Please use the support portal for inquiries.</i>
                            </div>
                        </div>
                    </body>
                    </html>";

                // Create matching AltBody
                $mail->AltBody = "Hello " . htmlspecialchars($userName ?? 'Valued Customer') . ",\n\n"
                    . "Your support case #" . htmlspecialchars($caseNumber) . " has been assigned to our engineer: " . htmlspecialchars($engineerName) . ".\n\n"
                    . "They will review your case and contact you during regular support hours for further assistance.\n\n"
                    . "To help resolve the issue more quickly, please ensure any relevant screenshots or error messages have been provided via the support portal.\n\n"
                    . "Thank you for contacting support.\n\n"
                    . "Best regards,\nTechnical Support Team\n"
                    . "This is an automated message. Please use the support portal for inquiries.";

                // Send the email
                $mail->send();
                $emailSent = true;
                error_log("Assignment notification sent for case $caseNumber to $userEmail.");

            } catch (Exception $e) {
                $emailSent = false;
                error_log("Mailer Error (Assign Case $caseNumber) to $userEmail using Host: "
                    . ($mail->Host ?? 'N/A') . ", Port: " . ($mail->Port ?? 'N/A')
                    . ", Secure: " . ($mail->SMTPSecure === false ? 'None' : ($mail->SMTPSecure ?? 'N/A'))
                    . ", Username: " . ($mail->Username ?? 'N/A')
                    . ". PHPMailer Error: {$mail->ErrorInfo}");
                $errorMessage = "Case assigned, but the notification email could not be sent. Mailer Error: " . htmlspecialchars($mail->ErrorInfo);
            }
        }
    } else {
        error_log("Case assigned (ID $caseId), but user email ($userEmail) is missing. Notification not sent.");
        $errorMessage = "Case assigned, but notification could not be sent (missing user email).";
    }

    // --- Prepare for Redirection ---
    $folder = 'dashboard';
    switch($_SESSION["user_role"] ?? '') {
        case "Technical Head": $folder = "technical_head"; break;
        case "Administrator": $folder = "administrator"; break;
    }

    // Set feedback message
    if ($errorMessage) {
        $_SESSION['warning_message'] = $errorMessage;
    } else {
        $_SESSION['success_message'] = "Case #" . htmlspecialchars($caseNumber) . " assigned successfully to " . htmlspecialchars($engineerName) . ".";
    }

    // Close DB connection
    if (isset($connection) && $connection instanceof mysqli) {
        mysqli_close($connection);
    }

    // Redirect
    header("Location: ../$folder/new_cases.php?status=assigned");
    exit;

} else {
    // Redirect if not POST
    header("Location: ../index.php");
    exit;
}

// Final check to close DB connection
if (isset($connection) && $connection instanceof mysqli) {
    mysqli_close($connection);
}
?>