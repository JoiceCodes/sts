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

    // Get the case owner ID
    $getCaseOwner = mysqli_prepare($connection, "SELECT case_owner FROM cases WHERE case_number = ?");
    mysqli_stmt_bind_param($getCaseOwner, "s", $caseNumber);
    mysqli_stmt_execute($getCaseOwner);
    $caseOwnerResult = mysqli_stmt_get_result($getCaseOwner);

    if ($caseOwner = mysqli_fetch_assoc($caseOwnerResult)) {
        $caseOwnerId = $caseOwner["case_owner"];

        // Get the case owner's full name and email
        $getUserDetails = mysqli_prepare($connection, "SELECT full_name, email FROM users WHERE id = ?");
        mysqli_stmt_bind_param($getUserDetails, "i", $caseOwnerId);
        mysqli_stmt_execute($getUserDetails);
        $userDetailsResult = mysqli_stmt_get_result($getUserDetails);

        if ($userDetails = mysqli_fetch_assoc($userDetailsResult)) {
            $ownerFullName = $userDetails["full_name"];
            $ownerEmail = $userDetails["email"];

            // Send email notification
            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->SMTPDebug = 2;  // Enable verbose debug output
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'joicebarandon31@gmail.com';
                $mail->Password = 'gmbviduachzzyazu';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('joicebarandon31@gmail.com', 'Technical Support');
                $mail->addAddress($ownerEmail, $ownerFullName);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Case Reopened: ' . $caseNumber;
                $mail->Body    = '<p>Dear ' . $ownerFullName . ',</p>' .
                                 '<p>Case number <strong>' . $caseNumber . '</strong> has been reopened and is now in "Waiting in Progress" status.</p>' .
                                 '<p>Please review the case details and take appropriate action.</p>';
                $mail->AltBody = 'Dear ' . $ownerFullName . ",\n\n" .
                                 'Case number ' . $caseNumber . ' has been reopened and is now in "Waiting in Progress" status.\n\n' .
                                 'Please review the case details and take appropriate action.';

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
    mysqli_stmt_close($getCaseOwner);
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