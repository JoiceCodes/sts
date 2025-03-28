<?php
session_start();
require_once "../config/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseNumber = $_POST["case_number"];
    $caseStatus = "Waiting in Progress";

    // Reopen the case
    $reopenCase = mysqli_prepare($connection, "UPDATE cases SET case_status = ?, reopen = reopen + 1 WHERE case_number = ?");
    mysqli_stmt_bind_param($reopenCase, "ss", $caseStatus, $caseNumber);
    mysqli_stmt_execute($reopenCase);

    // Get the case owner ID and issue description
    $getCaseInfo = mysqli_prepare($connection, "SELECT case_owner FROM cases WHERE case_number = ?");
    mysqli_stmt_bind_param($getCaseInfo, "s", $caseNumber);
    mysqli_stmt_execute($getCaseInfo);
    $caseInfoResult = mysqli_stmt_get_result($getCaseInfo);

    if ($caseInfo = mysqli_fetch_assoc($caseInfoResult)) {
        $caseOwnerId = $caseInfo["case_owner"];
        $issueDescription = $caseInfo["issue_description"];

        // Get the case owner's full name and email
        $getUserDetails = mysqli_prepare($connection, "SELECT full_name, email FROM users WHERE id = ?");
        mysqli_stmt_bind_param($getUserDetails, "i", $caseOwnerId);
        mysqli_stmt_execute($getUserDetails);
        $userDetailsResult = mysqli_stmt_get_result($getUserDetails);

        if ($userDetails = mysqli_fetch_assoc($userDetailsResult)) {
            $ownerFullName = $userDetails["full_name"];
            $ownerEmail = $userDetails["email"];

            // --- Prepare Plain Text Email Body ---
            $emailSubject = 'Ticket Reopened: ' . $caseNumber;
            $emailBodyPlain = "Dear " . htmlspecialchars($ownerFullName) . ",\n\n" .
                              "We would like to inform you that your support ticket (" . htmlspecialchars($caseNumber) . ") regarding " . htmlspecialchars($issueDescription) . " has been reopened.\n\n" .
                              "Our team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.\n\n" .
                              "If you have any additional details or questions, please feel free to share them with us.\n\n" .
                              "Thank you for your continued patience and cooperation.\n\n" .
                              "Best regards,\n" .
                              "i-Secure Networks and Business Solutions Inc.";

            // --- Prepare HTML Email Body for Sending ---
            $emailBodyHTML = '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0;">' .
                             '<div style="max-width: 600px; margin: 20px auto; background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">' .
                             '<h1 style="color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px;">Ticket Reopened: ' . htmlspecialchars($caseNumber) . '</h1>' .
                             '<p>Dear <span style="font-weight: bold;">' . htmlspecialchars($ownerFullName) . '</span>,</p>' .
                             '<p>We would like to inform you that your support ticket (<span style="font-weight: bold; color: #0056b3;">' . htmlspecialchars($caseNumber) . '</span>) regarding <span style="font-style: italic;">' . htmlspecialchars($issueDescription) . '</span> has been reopened.</p>' .
                             '<p>Our team is actively reviewing the case to ensure a thorough resolution. You will be notified of any updates or progress.</p>' .
                             '<p>If you have any additional details or questions, please feel free to share them with us.</p>' .
                             '<p>Thank you for your continued patience and cooperation.</p>' .
                             '<div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 0.8em; color: #777;">' .
                             'Best regards,<br>' .
                             'i-Secure Networks and Business Solutions Inc.' .
                             '</div>' .
                             '</div>' .
                             '</div>';

            // --- Save Notification to Database (Plain Text) ---
            $insertNotificationStmt = mysqli_prepare($connection, "INSERT INTO notifications (case_id, recipient_username, recipient_email, message_subject, message_body) VALUES (?, ?, ?, ?, ?)");
            if ($insertNotificationStmt) {
                mysqli_stmt_bind_param($insertNotificationStmt, "issss", $caseNumber, $ownerFullName, $ownerEmail, $emailSubject, $emailBodyPlain); // Save the plain text email
                if (!mysqli_stmt_execute($insertNotificationStmt)) {
                    error_log("Failed to save notification to database: " . mysqli_stmt_error($insertNotificationStmt));
                }
                mysqli_stmt_close($insertNotificationStmt);
            } else {
                error_log("Failed to prepare statement for saving notification: " . mysqli_error($connection));
            }

            // --- Send Email Notification ---
            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'joicebarandon31@gmail.com';
                $mail->Password = 'gmbviduachzzyazu';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('joicebarandon31@gmail.com', 'i-Secure Networks and Business Solutions Inc.');
                $mail->addAddress($ownerEmail, $ownerFullName);

                // Content
                $mail->isHTML(true);
                $mail->Subject = $emailSubject;
                $mail->Body = $emailBodyHTML; // Send HTML email
                $mail->AltBody = $emailBodyPlain; // Send plain text alternative

                $mail->send();
                $emailSent = true;
            } catch (Exception $e) {
                $emailSent = false;
                $errorMessage = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                error_log("PHPMailer Error: " . $e->getMessage()); // Log the error
            }
        } else {
            // Handle the case where the user with the case owner ID is not found
            $errorMessage = "Error: Case owner details not found.";
            error_log("Database Error: Case owner details not found.");
        }
        mysqli_stmt_close($getUserDetails);
    } else {
        // Handle the case where the case number is not found
        $errorMessage = "Error: Case number not found.";
        error_log("Database Error: Case number not found.");
    }
    mysqli_stmt_close($getCaseInfo);
    mysqli_stmt_close($reopenCase);

    switch ($_SESSION["user_role"]) {
        case "Engineer":
            $folder = "engineer";
            break;
        case "Technical Engineer":
            $folder = "technical_engineer";
            break;
        case "Technical Head":
            $folder = "technical_head";
            break;
        default:
            $folder = "unknown"; // Handle unknown roles appropriately
            break;
    }

    $redirectURL = "../$folder/solved_cases.php";
    if (isset($emailSent) && $emailSent) {
        $redirectURL .= "?success=1&email_sent=1";
    } elseif (isset($errorMessage)) {
        $redirectURL .= "?error=" . urlencode($errorMessage);
    } else {
        $redirectURL .= "?success=1";
    }
    header("Location: " . $redirectURL);
    exit;
}
?>