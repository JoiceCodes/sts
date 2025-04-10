<?php
session_start();
require_once "../config/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function get_email_settings($connection) {
    $settings = [];
    $query = "SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'";
    $result = $connection->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $result->free();
    } else {
        error_log("Failed to fetch email settings from database: " . $connection->error);
    }
    return $settings;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["case_number"])) {
        $_SESSION['error_message'] = "Error: Case number not provided.";
        header("Location: ../index.php?error=missing_data");
        exit;
    }
    $caseNumber = $_POST["case_number"];
    $caseStatus = "Solved";

    $folder = 'dashboard';
    switch ($_SESSION["user_role"] ?? '') {
        case "Engineer":
            $folder = "engineer";
            break;
        case "Technical Head":
            $folder = "technical_head";
            break;
        case "Administrator":
            $folder = "administrator";
            break;
    }

    $destination = "ongoing_cases.php";

    mysqli_begin_transaction($connection);

    try {
        // --- UPDATE case status and set date_solved ---
        $setCaseNumberStatus = mysqli_prepare($connection, "UPDATE cases SET case_status = ?, date_solved = NOW(), last_modified = NOW() WHERE case_number = ? LIMIT 1");
        if (!$setCaseNumberStatus) {
            throw new Exception("Error preparing case status/solve date update: " . mysqli_error($connection));
        }
        // Bind parameters: caseStatus (s), caseNumber (s)
        mysqli_stmt_bind_param($setCaseNumberStatus, "ss", $caseStatus, $caseNumber);
        if (!mysqli_stmt_execute($setCaseNumberStatus)) {
            throw new Exception("Error updating case status/solve date: " . mysqli_stmt_error($setCaseNumberStatus));
        }
        $affected_rows = mysqli_stmt_affected_rows($setCaseNumberStatus);
        mysqli_stmt_close($setCaseNumberStatus);
        if ($affected_rows === 0) {
            throw new Exception("Case #$caseNumber not found or status already set.");
        }

        // --- Fetch engineer info ---
        $engineerId = null;
        $engineerName = 'N/A';
        $getEngineer = mysqli_prepare($connection, "SELECT u.id, u.full_name FROM users AS u JOIN cases AS c ON u.id = c.user_id WHERE c.case_number = ?");
        if (!$getEngineer) {
            throw new Exception("Error preparing engineer info query: " . mysqli_error($connection));
        }
        mysqli_stmt_bind_param($getEngineer, "s", $caseNumber);
        if (!mysqli_stmt_execute($getEngineer)) {
            throw new Exception("Error executing engineer info query: " . mysqli_stmt_error($getEngineer));
        }
        $getEngineerResult = mysqli_stmt_get_result($getEngineer);
        if ($row = mysqli_fetch_assoc($getEngineerResult)) {
            $engineerId = $row["id"];
            $engineerName = $row["full_name"];
        } else {
            error_log("Could not find assigned engineer (user_id) for case $caseNumber.");
        }
        mysqli_free_result($getEngineerResult);
        mysqli_stmt_close($getEngineer);

        // --- Fetch user info ---
        $userId = null;
        $userEmail = null;
        $userName = 'Valued Customer';
        $getUser = mysqli_prepare($connection, "SELECT u.id, u.full_name, u.email FROM users AS u JOIN cases AS c ON u.id = c.case_owner WHERE c.case_number = ?");
        if (!$getUser) {
            throw new Exception("Error preparing user info query: " . mysqli_error($connection));
        }
        mysqli_stmt_bind_param($getUser, "s", $caseNumber);
        if (!mysqli_stmt_execute($getUser)) {
            throw new Exception("Error executing user info query: " . mysqli_stmt_error($getUser));
        }
        $getUserResult = mysqli_stmt_get_result($getUser);
        if ($row = mysqli_fetch_assoc($getUserResult)) {
            $userId = $row["id"];
            $userEmail = $row["email"];
            $userName = $row["full_name"];
        } else {
            throw new Exception("Could not retrieve case owner information for case $caseNumber.");
        }
        mysqli_free_result($getUserResult);
        mysqli_stmt_close($getUser);

        // --- Prepare email content ---
        $base_url = "http://localhost/sts";
        $encodedEngineerId = base64_encode($engineerId ?? '0');
        $encodedCaseNumber = base64_encode($caseNumber);
        $ratingLink = "$base_url/rate_engineer.php?id=$encodedEngineerId&case=$encodedCaseNumber";

        $emailSubject = "Support Case #" . htmlspecialchars($caseNumber) . " Has Been Resolved";
        $emailBody = "
            <!DOCTYPE html>
            <html lang='en'>
            <head> <meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'> <title>Support Case Resolved</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 20px; }
                    .container { max-width: 600px; margin: 0 auto; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    h1 { color: #2a6496; border-bottom: 2px solid #eef; padding-bottom: 10px; margin-top: 0; }
                    p { margin-bottom: 15px; } .highlight { font-weight: bold; color: #2a6496; }
                    .button-link { display: inline-block; padding: 12px 20px; background-color: #007bff; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; }
                    .link-text { font-size: 0.9em; color: #555; }
                    .footer { margin-top: 25px; padding-top: 15px; border-top: 1px solid #eee; font-size: 0.85em; color: #888; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h1>Support Case Resolved</h1>
                    <p>Hello <span class='highlight'>" . htmlspecialchars($userName) . "</span>,</p>
                    <p>We're pleased to inform you that your support case <span class='highlight'>#" . htmlspecialchars($caseNumber) . "</span> has been marked as resolved by our engineer, <span class='highlight'>" . htmlspecialchars($engineerName) . "</span>.</p>
                    <p>If you believe the issue is not fully resolved, please reply to this message or contact us through the support portal within 7 days to reopen the case.</p>
                    <p>We value your feedback! Please take a moment to rate your support experience with <span class='highlight'>" . htmlspecialchars($engineerName) . "</span>:</p>
                    <p><a href='" . htmlspecialchars($ratingLink) . "' class='button-link'>Rate Your Support Experience</a></p>
                    <p class='link-text'>If the button doesn't work, copy and paste this link into your browser:<br>" . htmlspecialchars($ratingLink) . "</p>
                    <p>Thank you for choosing our services.</p>
                    <div class='footer'>
                        Best regards,<br>
                        <b>Technical Support Team</b><br>
                        <i>This is an automated message. Please use the support portal for inquiries.</i>
                    </div>
                </div>
            </body>
            </html>";

        $plainMessage = "Hello " . htmlspecialchars($userName) . ",\n\n"
            . "We're pleased to inform you that your support case #" . htmlspecialchars($caseNumber) . " has been marked as resolved by our engineer, " . htmlspecialchars($engineerName) . ".\n\n"
            . "If you believe the issue is not fully resolved, please reply to this message or contact us through the support portal within 7 days to reopen the case.\n\n"
            . "We value your feedback! Please take a moment to rate your support experience with " . htmlspecialchars($engineerName) . " by visiting the link below:\n"
            . htmlspecialchars($ratingLink) . "\n\n"
            . "Thank you for choosing our services.\n\n"
            . "Best regards,\nTechnical Support Team\n"
            . "This is an automated message. Please use the support portal for inquiries.";

        // --- Get email settings ---
        $email_settings = get_email_settings($connection);
        if (empty($email_settings)) {
            throw new Exception("Failed to load critical email settings from the database.");
        }

        // --- Insert notification ---
        $getCaseIdStmt = mysqli_prepare($connection, "SELECT id FROM cases WHERE case_number = ?");
        if (!$getCaseIdStmt) throw new Exception("Error preparing case ID fetch: " . mysqli_error($connection));
        mysqli_stmt_bind_param($getCaseIdStmt, "s", $caseNumber);
        if (!mysqli_stmt_execute($getCaseIdStmt)) throw new Exception("Error fetching case ID: " . mysqli_stmt_error($getCaseIdStmt));
        $caseIdResult = mysqli_stmt_get_result($getCaseIdStmt);
        $caseIdRow = mysqli_fetch_assoc($caseIdResult);
        mysqli_free_result($caseIdResult);
        mysqli_stmt_close($getCaseIdStmt);
        $dbCaseId = $caseIdRow['id'] ?? null;

        if ($dbCaseId) {
            $insertNotificationStmt = mysqli_prepare($connection, "INSERT INTO notifications (case_id, recipient_username, recipient_email, message_subject, message_body) VALUES (?, ?, ?, ?, ?)");
            if (!$insertNotificationStmt) {
                throw new Exception("Error preparing notification insert: " . mysqli_error($connection));
            }
            mysqli_stmt_bind_param($insertNotificationStmt, "issss", $dbCaseId, $userName, $userEmail, $emailSubject, $plainMessage);
            if (!mysqli_stmt_execute($insertNotificationStmt)) {
                throw new Exception("Error saving notification: " . mysqli_stmt_error($insertNotificationStmt));
            }
            mysqli_stmt_close($insertNotificationStmt);
        } else {
            error_log("Could not find primary key for case $caseNumber, skipping notification insertion.");
        }

        // --- Send email ---
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $email_settings['smtp_host'] ?? 'smtp.gmail.com';
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
            $mail->addAddress($userEmail, $userName);

            $mail->isHTML(true);
            $mail->Subject = $emailSubject;
            $mail->Body = $emailBody;
            $mail->AltBody = $plainMessage;

            $mail->send();
            error_log("Resolution email sent successfully to $userEmail for case $caseNumber.");

            // --- Commit transaction ---
            mysqli_commit($connection);

            $_SESSION['success_message'] = "Case #" . htmlspecialchars($caseNumber) . " marked as solved and notification sent to " . htmlspecialchars($userName) . ".";
            header("Location: ../$folder/$destination?success=solved");
            exit;

        } catch (Exception $e) { // Catch PHPMailer exception
            mysqli_rollback($connection); // Rollback if email fails
            error_log("Mailer Error for case $caseNumber to $userEmail using Host: "
                . ($mail->Host ?? 'N/A') . ", Port: " . ($mail->Port ?? 'N/A')
                . ", Secure: " . ($mail->SMTPSecure === false ? 'None' : ($mail->SMTPSecure ?? 'N/A'))
                . ", Username: " . ($mail->Username ?? 'N/A')
                . ". PHPMailer Error: {$mail->ErrorInfo}");
            $_SESSION['error_message'] = "Error: Case status updated, but could not send email notification. The changes have been rolled back. Please try again or contact support. Mailer Error: " . htmlspecialchars($mail->ErrorInfo);
            header("Location: ../$folder/$destination?error=email_rollback");
            exit;
        }
    } catch (Exception $e) { // Catch DB/Logic exceptions
        mysqli_rollback($connection);
        error_log("Database/Logic Error processing case $caseNumber: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred while processing the case: " . htmlspecialchars($e->getMessage()) . " Database changes rolled back.";
        header("Location: ../$folder/$destination?error=db_rollback");
        exit;
    } finally {
        if (isset($connection) && $connection instanceof mysqli && mysqli_ping($connection)) {
            mysqli_close($connection);
        }
    }
} else {
    header("Location: ../index.php");
    exit;
}

if (isset($connection) && $connection instanceof mysqli) {
    $connection->close();
}
?>