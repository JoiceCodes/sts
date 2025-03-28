<?php
session_start();
require_once "../config/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ensure case_number is received
    if (!isset($_POST["case_number"])) {
        die("Error: Case number not provided.");
    }
    $caseNumber = $_POST["case_number"];
    $isReopen = isset($_POST["is_reopen"]) && $_POST["is_reopen"] === "true" ? true : false;
    $caseStatus = "Solved";

    // Determine the folder based on user role
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
            die("Error: Invalid user role.");
    }

    // Determine the destination based on reopen status
    switch ($isReopen) {
        case true:
            $destination = "reopened_cases.php";
            break;
        case false:
            $destination = "ongoing_cases.php";
            break;
    }

    // Begin transaction for atomicity
    mysqli_begin_transaction($connection);

    try {
        // 1. Update Case Status
        $setCaseNumberStatus = mysqli_prepare($connection, "UPDATE cases SET case_status = ? WHERE case_number = ? LIMIT 1");
        if (!$setCaseNumberStatus) {
            throw new Exception("Error preparing case status update: " . mysqli_error($connection));
        }
        mysqli_stmt_bind_param($setCaseNumberStatus, "ss", $caseStatus, $caseNumber);
        if (!mysqli_stmt_execute($setCaseNumberStatus)) {
            throw new Exception("Error updating case status: " . mysqli_stmt_error($setCaseNumberStatus));
        }
        mysqli_stmt_close($setCaseNumberStatus);

        // 2. Fetch Engineer Info
        $engineerId = null;
        $engineerName = 'N/A';
        $getEngineer = mysqli_prepare($connection, "SELECT u.id, u.full_name FROM users AS u INNER JOIN cases AS c ON u.id = c.user_id WHERE c.case_number = ?");
        if (!$getEngineer) {
            throw new Exception("Error preparing engineer info query: " . mysqli_error($connection));
        }
        mysqli_stmt_bind_param($getEngineer, "s", $caseNumber);
        mysqli_stmt_execute($getEngineer);
        $getEngineerResult = mysqli_stmt_get_result($getEngineer);
        if ($row = mysqli_fetch_assoc($getEngineerResult)) {
            $engineerId = $row["id"];
            $engineerName = $row["full_name"];
        }
        mysqli_stmt_close($getEngineer);

        // 3. Fetch User (Case Owner) Info
        $userEmail = null;
        $userName = 'Valued Customer';
        $getUser = mysqli_prepare($connection, "SELECT u.full_name, u.email FROM users AS u INNER JOIN cases AS c ON u.id = c.case_owner WHERE c.case_number = ?");
        if (!$getUser) {
            throw new Exception("Error preparing user info query: " . mysqli_error($connection));
        }
        mysqli_stmt_bind_param($getUser, "s", $caseNumber);
        mysqli_stmt_execute($getUser);
        $getUserResult = mysqli_stmt_get_result($getUser);
        if ($row = mysqli_fetch_assoc($getUserResult)) {
            $userEmail = $row["email"];
            $userName = $row["full_name"];
        }
        mysqli_stmt_close($getUser);

        // Check if we have necessary info before proceeding
        if (!$engineerId || !$userEmail) {
            throw new Exception("Could not retrieve engineer or user information for case $caseNumber.");
        }

        // 4. Prepare Email Content
        $encodedEngineerId = base64_encode($engineerId);
        $encodedCaseNumber = base64_encode($caseNumber);
        $ratingLink = "http://localhost/sts/rate_engineer.php?id=$encodedEngineerId&case=$encodedCaseNumber";

        $emailSubject = "Technical Assistance Case #$caseNumber Resolved";
        $emailBody = "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Technical Assistance Case Resolved</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        max-width: 600px;
                        margin: 20px auto;
                        background-color: #fff;
                        padding: 20px;
                        border-radius: 5px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h1 {
                        color: #0056b3;
                        border-bottom: 2px solid #0056b3;
                        padding-bottom: 10px;
                    }
                    p {
                        margin-bottom: 10px;
                    }
                    .highlight {
                        font-weight: bold;
                        color: #0056b3;
                    }
                    .footer {
                        margin-top: 20px;
                        padding-top: 10px;
                        border-top: 1px solid #ddd;
                        font-size: 0.8em;
                        color: #777;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h1>Technical Assistance Case Resolved</h1>
                    <p>Hello <span class='highlight'>" . htmlspecialchars($userName) . "</span>,</p>
                    <p>We are pleased to inform you that your technical assistance case <span class='highlight'>#" . htmlspecialchars($caseNumber) . "</span> has been successfully resolved by <span class='highlight'>" . htmlspecialchars($engineerName) . "</span>. If you encounter any further issues, feel free to reach out.</p>
                    <p>We value your feedback! Please take a moment to rate your experience with <span class='highlight'>" . htmlspecialchars($engineerName) . "</span> regarding this case by clicking the link below:</p>
                    <p><a href='" . htmlspecialchars($ratingLink) . "' style='display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Rate Your Engineer</a></p>
                    <p><small>If the button doesn't work, copy and paste this link into your browser: " . htmlspecialchars($ratingLink) . "</small></p>
                    <p>Thank you for reaching out to our support team.</p>
                    <div class='footer'>
                        Best regards,<br>
                        <b>Technical Support Team</b>
                    </div>
                </div>
            </body>
            </html>
        ";

        // Prepare plain text version of the email for database storage
        $plainMessage = "Hello " . htmlspecialchars($userName) . ",\n\n"
            . "We are pleased to inform you that your technical assistance case #" . htmlspecialchars($caseNumber) . " has been successfully resolved by " . htmlspecialchars($engineerName) . ". If you encounter any further issues, feel free to reach out.\n\n"
            . "We value your feedback! Please take a moment to rate your experience with " . htmlspecialchars($engineerName) . " regarding this case by clicking the link below:\n"
            . htmlspecialchars($ratingLink) . "\n\n"
            . "Thank you for reaching out to our support team.\n\n"
            . "Best regards,\nTechnical Support Team";

        // 5. Save Notification to Database
        $insertNotificationStmt = mysqli_prepare($connection, "INSERT INTO notifications (case_id, recipient_username, recipient_email, message_subject, message_body) SELECT c.id, ?, ?, ?, ? FROM cases c WHERE c.case_number = ?");
        if (!$insertNotificationStmt) {
            throw new Exception("Error preparing notification insert: " . mysqli_error($connection));
        }
        mysqli_stmt_bind_param($insertNotificationStmt, "sssss", $userName, $userEmail, $emailSubject, $plainMessage, $caseNumber);
        if (!mysqli_stmt_execute($insertNotificationStmt)) {
            throw new Exception("Error saving notification: " . mysqli_stmt_error($insertNotificationStmt));
        }
        mysqli_stmt_close($insertNotificationStmt);

        // 6. Send Email Notification
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'joicebarandon31@gmail.com';
            $mail->Password = 'gmbviduachzzyazu';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('joicebarandon31@gmail.com', 'Technical Support');
            $mail->addAddress($userEmail, $userName);

            $mail->isHTML(true);
            $mail->Subject = $emailSubject;
            $mail->Body = $emailBody;

            $mail->send();

            // Commit transaction if everything succeeded
            mysqli_commit($connection);

            // Redirect only after successful commit and email send attempt
            header("Location: ../$folder/$destination?success=1");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on email error
            mysqli_rollback($connection);
            error_log("Mailer Error for case $caseNumber: {$mail->ErrorInfo}");
            echo "Error: Could not send email notification. Please contact support. Mailer Error: {$mail->ErrorInfo}";
            exit;
        }
    } catch (Exception $e) {
        // Rollback transaction on database error
        mysqli_rollback($connection);
        error_log("Database Error processing case $caseNumber: " . $e->getMessage());
        echo "An error occurred while processing the case: " . $e->getMessage();
        exit;
    } finally {
        // Close the connection if it's still open
        if (isset($connection) && mysqli_ping($connection)) {
            mysqli_close($connection);
        }
    }
} else {
    // Redirect or show error if not a POST request
    header("Location: ../index.php");
    exit;
}
?>