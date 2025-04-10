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

if (!$connection) {
    error_log("Database connection failed in accept_case script: " . mysqli_connect_error());
    die("Critical Error: Database connection failed. Please contact support.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["case_id"]) || !filter_var($_POST["case_id"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        $_SESSION['error_message'] = "Invalid or missing Case ID.";
        header("Location: ../engineer/new_cases.php?error=invalid_case_id");
        exit;
    }
    if (!isset($_SESSION["user_id"]) || !filter_var($_SESSION["user_id"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        $_SESSION['error_message'] = "Your session has expired or is invalid. Please log in again.";
        header("Location: ../login.php?error=session_expired");
        exit;
    }

    $caseId = (int)$_POST["case_id"]; // Keep caseId for fetching related data
    $engineerId = (int)$_SESSION["user_id"];
    $caseStatus = "Waiting in Progress";
    $caseNumber = null;
    $engineerName = 'Support Engineer';
    $userName = null;
    $userEmail = null;
    $errorMessage = null;
    $emailSent = false;

    $getEngineerNameStmt = mysqli_prepare($connection, "SELECT full_name FROM users WHERE id = ? LIMIT 1");
    if ($getEngineerNameStmt) {
        mysqli_stmt_bind_param($getEngineerNameStmt, "i", $engineerId);
        mysqli_stmt_execute($getEngineerNameStmt);
        $resultEng = mysqli_stmt_get_result($getEngineerNameStmt);
        if($rowEng = mysqli_fetch_assoc($resultEng)){
            $engineerName = $rowEng['full_name'];
        } else {
            error_log("Could not find engineer name for ID: $engineerId");
        }
        mysqli_free_result($resultEng);
        mysqli_stmt_close($getEngineerNameStmt);
    } else {
        error_log("Failed to prepare statement for fetching engineer name: " . mysqli_error($connection));
    }

    $getCaseNumberStmt = mysqli_prepare($connection, "SELECT case_number FROM cases WHERE id = ? LIMIT 1");
    if ($getCaseNumberStmt) {
        mysqli_stmt_bind_param($getCaseNumberStmt, "i", $caseId);
        mysqli_stmt_execute($getCaseNumberStmt);
        $getCaseNumberResult = mysqli_stmt_get_result($getCaseNumberStmt);
        if ($row = mysqli_fetch_assoc($getCaseNumberResult)) {
            $caseNumber = $row["case_number"]; // Get the case number
        } else {
            $_SESSION['error_message'] = "Case with ID $caseId not found.";
            mysqli_stmt_close($getCaseNumberStmt);
            header("Location: ../engineer/new_cases.php?error=case_not_found");
            exit;
        }
        mysqli_free_result($getCaseNumberResult);
        mysqli_stmt_close($getCaseNumberStmt);
    } else {
        error_log("Failed to prepare statement for fetching case number: " . mysqli_error($connection));
        $_SESSION['error_message'] = "Error retrieving case details.";
        header("Location: ../error_page.php?error=db_prepare_casenum");
        exit;
    }

    // --- UPDATE using CASE NUMBER and add date_accepted ---
    $updateCaseStmt = mysqli_prepare($connection, "UPDATE cases SET user_id = ?, case_status = ?, last_modified = NOW(), date_accepted = NOW() WHERE case_number = ? AND (user_id IS NULL OR user_id = 0)");
    if ($updateCaseStmt) {
        // Bind parameters: engineerId (i), caseStatus (s), caseNumber (s)
        mysqli_stmt_bind_param($updateCaseStmt, "iss", $engineerId, $caseStatus, $caseNumber);
        if (!mysqli_stmt_execute($updateCaseStmt)) {
            error_log("Failed to execute statement for updating case using case_number $caseNumber: " . mysqli_stmt_error($updateCaseStmt));
            $_SESSION['error_message'] = "Error accepting case status.";
            mysqli_stmt_close($updateCaseStmt);
            header("Location: ../error_page.php?error=db_execute_accept");
            exit;
        }
        $affectedRows = mysqli_stmt_affected_rows($updateCaseStmt);
        mysqli_stmt_close($updateCaseStmt);

        if ($affectedRows === 0) {
            error_log("Accept case failed (affectedRows=0) for case number $caseNumber by engineer ID $engineerId. Case might be already assigned.");
            $_SESSION['error_message'] = "Could not accept case #$caseNumber. It might already be assigned to another engineer.";
            header("Location: ../engineer/new_cases.php?error=already_assigned");
            exit;
        }

    } else {
        error_log("Failed to prepare statement for updating case using case_number: " . mysqli_error($connection));
        $_SESSION['error_message'] = "Error preparing case update.";
        header("Location: ../error_page.php?error=db_prepare_accept");
        exit;
    }

    // --- Fetch owner details using case ID ---
    $queryUserStmt = mysqli_prepare($connection, "SELECT u.full_name, u.email FROM cases c JOIN users u ON c.case_owner = u.id WHERE c.id = ? LIMIT 1");
    if ($queryUserStmt) {
        mysqli_stmt_bind_param($queryUserStmt, "i", $caseId);
        mysqli_stmt_execute($queryUserStmt);
        $resultUser = mysqli_stmt_get_result($queryUserStmt);
        if($rowUser = mysqli_fetch_assoc($resultUser)){
             $userName = $rowUser['full_name'];
             $userEmail = $rowUser['email'];
        } else {
             error_log("Could not fetch case owner details for case ID $caseId after accepting.");
        }
        mysqli_free_result($resultUser);
        mysqli_stmt_close($queryUserStmt);
    } else {
        error_log("Failed to prepare statement for fetching user details: " . mysqli_error($connection));
    }

    if ($userName !== null && $userEmail !== null) {
        $plainMessage = "Hello " . htmlspecialchars($userName) . ",\n\n"
            . "We have received your request for technical assistance (Case #" . htmlspecialchars($caseNumber) . "). One of our support engineers, " . htmlspecialchars($engineerName) . ", has accepted your case and will assist you during regular support hours.\n\n"
            . "To help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility via the support portal.\n\n"
            . "Thank you,\nTechnical Support Team";

        $emailSubject = "Technical Assistance Request Accepted - Case #" . htmlspecialchars($caseNumber);
        $emailBody = "
            <!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>" . htmlspecialchars($emailSubject) . "</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 20px; }
                    .container { max-width: 600px; margin: 0 auto; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    h1 { color: #2a6496; border-bottom: 2px solid #eef; padding-bottom: 10px; margin-top: 0; font-size: 1.4em; }
                    p { margin-bottom: 15px; } .highlight { font-weight: bold; color: #2a6496; }
                    .footer { margin-top: 25px; padding-top: 15px; border-top: 1px solid #eee; font-size: 0.85em; color: #888; text-align: center; }
                </style>
            </head><body><div class='container'><h1>" . htmlspecialchars($emailSubject) . "</h1>
                <p>Hello <span class='highlight'>" . htmlspecialchars($userName) . "</span>,</p>
                <p>We have received your request for technical assistance (Case #<span class='highlight'>" . htmlspecialchars($caseNumber) . "</span>). One of our support engineers, <span class='highlight'>" . htmlspecialchars($engineerName) . "</span>, has accepted your case and will assist you during regular support hours.</p>
                <p>To help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility via the support portal.</p>
                <div class='footer'>Thank you,<br><b>Technical Support Team</b><br><i>This is an automated message.</i></div>
            </div></body></html>";

        $insertNotificationStmt = mysqli_prepare($connection, "INSERT INTO notifications (case_id, recipient_username, recipient_email, message_subject, message_body) VALUES (?, ?, ?, ?, ?)");
        if ($insertNotificationStmt) {
            mysqli_stmt_bind_param($insertNotificationStmt, "issss", $caseId, $userName, $userEmail, $emailSubject, $plainMessage);
            if (!mysqli_stmt_execute($insertNotificationStmt)) {
                error_log("Failed to save notification to database for case $caseId: " . mysqli_stmt_error($insertNotificationStmt));
            }
            mysqli_stmt_close($insertNotificationStmt);
        } else {
            error_log("Failed to prepare statement for saving notification: " . mysqli_error($connection));
        }

        $email_settings = get_email_settings($connection);
        if (empty($email_settings)) {
            error_log("Critical Error: Failed to load email settings from database for case accept $caseId.");
            $errorMessage = "Case accepted, but email settings failed. Notification not sent.";
        } else {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $email_settings['smtp_host'] ?? 'smtp.example.com';
                $mail->SMTPAuth = true;
                $mail->Username = $email_settings['smtp_username'] ?? '';
                $mail->Password = $email_settings['smtp_password'] ?? '';
                $secure_type = strtolower($email_settings['smtp_secure'] ?? 'tls');
                if ($secure_type === 'tls') { $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; }
                elseif ($secure_type === 'ssl') { $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; }
                else { $mail->SMTPSecure = false; }
                $mail->Port = (int)($email_settings['smtp_port'] ?? 587);
                $mail->setFrom( $email_settings['smtp_from_email'] ?? 'noreply@example.com', $email_settings['smtp_from_name'] ?? 'Technical Support' );
                $mail->addAddress($userEmail, $userName);

                $mail->isHTML(true);
                $mail->Subject = $emailSubject;
                $mail->Body = $emailBody;
                $mail->AltBody = $plainMessage;

                $mail->send();
                $emailSent = true;
                error_log("Acceptance email sent for case $caseNumber to $userEmail.");

            } catch (Exception $e) {
                $emailSent = false;
                error_log("Mailer Error (Accept Case $caseNumber) to $userEmail using Host: "
                    . ($mail->Host ?? 'N/A') . ", Port: " . ($mail->Port ?? 'N/A')
                    . ", Secure: " . ($mail->SMTPSecure === false ? 'None' : ($mail->SMTPSecure ?? 'N/A'))
                    . ", Username: " . ($mail->Username ?? 'N/A')
                    . ". PHPMailer Error: {$mail->ErrorInfo}");
                $errorMessage = "Case accepted, but the notification email could not be sent. Mailer Error: " . htmlspecialchars($mail->ErrorInfo);
            }
        }
    } else {
        error_log("Case accepted (ID $caseId), but user email ($userEmail) or name ($userName) is missing. Notification not sent.");
        $errorMessage = "Case accepted, but notification could not be sent (missing owner details).";
    }

    if ($errorMessage) {
        $_SESSION['warning_message'] = $errorMessage;
    } else {
        $_SESSION['success_message'] = "Case #" . htmlspecialchars($caseNumber) . " accepted successfully.";
    }

    if (isset($connection) && $connection instanceof mysqli) {
        mysqli_close($connection);
    }

    header("Location: ../engineer/ongoing_cases.php?status=accepted");
    exit;

} else {
    header("Location: ../engineer/dashboard.php");
    exit;
}

if (isset($connection) && $connection instanceof mysqli) {
    mysqli_close($connection);
}
?>