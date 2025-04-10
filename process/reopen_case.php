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
    if (!isset($_POST["case_number"]) || empty(trim($_POST["case_number"]))) {
        $_SESSION['error_message'] = "Error: Case number not provided.";
        header("Location: ../dashboard/index.php?error=missing_case");
        exit;
    }
    $caseNumber = trim($_POST["case_number"]);
    $caseStatus = "Waiting in Progress";
    $emailSent = false;
    $errorMessage = null;

    $reopenCase = mysqli_prepare($connection, "UPDATE cases SET case_status = ?, reopen = reopen + 1, updated_at = NOW() WHERE case_number = ?");
    if (!$reopenCase) {
        error_log("Prepare Error (reopenCase): " . mysqli_error($connection));
        $_SESSION['error_message'] = "Database error preparing case update.";
        header("Location: ../error_page.php");
        exit;
    }
    mysqli_stmt_bind_param($reopenCase, "ss", $caseStatus, $caseNumber);
    if (!mysqli_stmt_execute($reopenCase)) {
        error_log("Execute Error (reopenCase): " . mysqli_stmt_error($reopenCase));
        $_SESSION['error_message'] = "Database error updating case status.";
        mysqli_stmt_close($reopenCase);
        header("Location: ../error_page.php");
        exit;
    }
    $affectedRows = mysqli_stmt_affected_rows($reopenCase);
    mysqli_stmt_close($reopenCase);

    if ($affectedRows > 0) {
        $getCaseOwner = mysqli_prepare($connection, "SELECT case_owner FROM cases WHERE case_number = ?");
        if ($getCaseOwner) {
             mysqli_stmt_bind_param($getCaseOwner, "s", $caseNumber);
             if (mysqli_stmt_execute($getCaseOwner)) {
                 $caseOwnerResult = mysqli_stmt_get_result($getCaseOwner);
                 $caseOwner = mysqli_fetch_assoc($caseOwnerResult);
                 mysqli_free_result($caseOwnerResult);
             } else {
                 error_log("Execute Error (getCaseOwner): " . mysqli_stmt_error($getCaseOwner));
                 $caseOwner = null;
             }
             mysqli_stmt_close($getCaseOwner);
        } else {
             error_log("Prepare Error (getCaseOwner): " . mysqli_error($connection));
             $caseOwner = null;
        }


        if ($caseOwner && isset($caseOwner["case_owner"])) {
            $caseOwnerId = $caseOwner["case_owner"];

            $getUserDetails = mysqli_prepare($connection, "SELECT full_name, email FROM users WHERE id = ?");
             if ($getUserDetails) {
                 mysqli_stmt_bind_param($getUserDetails, "i", $caseOwnerId);
                 if (mysqli_stmt_execute($getUserDetails)) {
                     $userDetailsResult = mysqli_stmt_get_result($getUserDetails);
                     $userDetails = mysqli_fetch_assoc($userDetailsResult);
                     mysqli_free_result($userDetailsResult);
                 } else {
                     error_log("Execute Error (getUserDetails): " . mysqli_stmt_error($getUserDetails));
                     $userDetails = null;
                 }
                 mysqli_stmt_close($getUserDetails);
            } else {
                 error_log("Prepare Error (getUserDetails): " . mysqli_error($connection));
                 $userDetails = null;
            }


            if ($userDetails && isset($userDetails["email"])) {
                $ownerFullName = $userDetails["full_name"] ?? 'Valued Customer';
                $ownerEmail = $userDetails["email"];

                $email_settings = get_email_settings($connection);
                if (empty($email_settings)) {
                    error_log("Critical Error: Failed to load email settings from database for case reopen $caseNumber.");
                    $errorMessage = "Case reopened, but email settings could not be loaded. Notification not sent.";
                } else {
                    $mail = new PHPMailer(true);
                    try {
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
                        $mail->addAddress($ownerEmail, $ownerFullName);

                        $mail->isHTML(true);
                        $mail->Subject = 'Case Reopened: ' . htmlspecialchars($caseNumber);
                        $mail->Body = '<p>Dear ' . htmlspecialchars($ownerFullName) . ',</p>' .
                                        '<p>Case number <strong>' . htmlspecialchars($caseNumber) . '</strong> has been reopened and is now in "Waiting in Progress" status.</p>' .
                                        '<p>An engineer will review your case shortly. You can view updates in the support portal.</p>' .
                                        '<p>Thank you,<br>Technical Support Team</p>';
                        $mail->AltBody = 'Dear ' . htmlspecialchars($ownerFullName) . ",\n\n" .
                                        'Case number ' . htmlspecialchars($caseNumber) . ' has been reopened and is now in "Waiting in Progress" status.\n\n' .
                                        'An engineer will review your case shortly. You can view updates in the support portal.\n\n' .
                                        'Thank you,\nTechnical Support Team';

                        $mail->send();
                        $emailSent = true;
                        error_log("Reopen notification sent for case $caseNumber to $ownerEmail");

                    } catch (Exception $e) {
                        $emailSent = false;
                        error_log("Mailer Error (Reopen Case $caseNumber) to $ownerEmail using Host: "
                            . ($mail->Host ?? 'N/A') . ", Port: " . ($mail->Port ?? 'N/A')
                            . ", Secure: " . ($mail->SMTPSecure === false ? 'None' : ($mail->SMTPSecure ?? 'N/A'))
                            . ", Username: " . ($mail->Username ?? 'N/A')
                            . ". PHPMailer Error: {$mail->ErrorInfo}");
                        $errorMessage = "Case reopened, but the notification email could not be sent. Mailer Error: " . htmlspecialchars($mail->ErrorInfo);
                    }
                }
            } else {
                 $errorMessage = "Error: Case owner details could not be retrieved for ID $caseOwnerId associated with case $caseNumber.";
                 error_log("Database Error: Case owner details not found or could not be retrieved for ID $caseOwnerId, case $caseNumber.");
            }
        } else {
            $errorMessage = "Error: Could not retrieve case owner after update for case $caseNumber.";
            error_log("Database Error: Could not retrieve case owner after update for case $caseNumber.");
        }
    } else {
        $errorMessage = "Error: Case #$caseNumber not found or could not be reopened.";
        error_log("Database Warning: Case #$caseNumber not found or could not be reopened (affectedRows=0).");
    }

    $folder = 'dashboard';
    switch ($_SESSION["user_role"] ?? '') {
        case "Engineer": $folder = "engineer"; break;
        case "Technical Head": $folder = "technical_head"; break;
        case "Administrator": $folder = "administrator"; break;
    }

    $redirectURL = "../$folder/solved_cases.php";
    if ($errorMessage) {
        $_SESSION['error_message'] = $errorMessage;
        $redirectURL .= "?warning=reopen_failed";
    } else {
        $_SESSION['success_message'] = "Case #" . htmlspecialchars($caseNumber) . " reopened successfully." . ($emailSent ? " Notification sent." : " Notification could not be sent.");
        $redirectURL .= "?success=reopened";
    }

    if (isset($connection) && $connection instanceof mysqli) {
        mysqli_close($connection);
    }
    header("Location: " . $redirectURL);
    exit;
} else {
    header("Location: ../index.php");
    exit;
}

if (isset($connection) && $connection instanceof mysqli) {
    mysqli_close($connection);
}
?>