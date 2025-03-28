<?php
session_start();
require_once "../config/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Adjust path as needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ensure case_number is received
    if (!isset($_POST["case_number"])) {
        die("Error: Case number not provided."); // Basic error handling
    }
    $caseNumber = $_POST["case_number"];
    $isReopen = isset($_POST["is_reopen"]) && $_POST["is_reopen"] === "true" ? true : false; // Safer check
    $caseStatus = "Solved";

    // ... (switch for $folder remains the same) ...
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
            // Handle unexpected user role if necessary
            die("Error: Invalid user role.");
    }

    // ... (switch for $destination remains the same) ...
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
        $setCaseNumberStatus = mysqli_prepare($connection, "UPDATE cases SET case_status = ? WHERE case_number = ? LIMIT 1");
        mysqli_stmt_bind_param($setCaseNumberStatus, "ss", $caseStatus, $caseNumber);
        if (!mysqli_stmt_execute($setCaseNumberStatus)) {
           throw new Exception("Error updating case status: " . mysqli_stmt_error($setCaseNumberStatus));
        }
        mysqli_stmt_close($setCaseNumberStatus); // Close statement


        // Fetch Engineer Info (Ensure variables are initialized)
        $engineerId = null;
        $engineerName = 'N/A'; // Default value
        $getEngineer = mysqli_prepare($connection, "SELECT u.id, u.full_name FROM users AS u INNER JOIN cases AS c ON u.id = c.user_id WHERE c.case_number = ?");
        mysqli_stmt_bind_param($getEngineer, "s", $caseNumber);
        mysqli_stmt_execute($getEngineer);
        $getEngineerResult = mysqli_stmt_get_result($getEngineer);
        if ($row = mysqli_fetch_assoc($getEngineerResult)) {
            $engineerId = $row["id"];
            $engineerName = $row["full_name"];
        }
        mysqli_stmt_close($getEngineer); // Close statement


        // Fetch User (Case Owner) Info (Ensure variables are initialized)
        $userEmail = null;
        $userName = 'Valued Customer'; // Default value
        $getUser = mysqli_prepare($connection, "SELECT u.full_name, u.email FROM users AS u INNER JOIN cases AS c ON u.id = c.case_owner WHERE c.case_number = ?");
        mysqli_stmt_bind_param($getUser, "s", $caseNumber);
        mysqli_stmt_execute($getUser);
        $getUserResult = mysqli_stmt_get_result($getUser);
        if ($row = mysqli_fetch_assoc($getUserResult)) {
            $userEmail = $row["email"];
            $userName = $row["full_name"];
        }
        mysqli_stmt_close($getUser); // Close statement

        // Check if we have necessary info before proceeding
        if (!$engineerId || !$userEmail) {
            throw new Exception("Could not retrieve engineer or user information for case $caseNumber.");
        }


        // Send email notification
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'joicebarandon31@gmail.com'; // Your email
            $mail->Password = 'gmbviduachzzyazu'; // Your email password (use App Passwords for security)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('joicebarandon31@gmail.com', 'Technical Support');
            $mail->addAddress($userEmail, $userName);

            // *** MODIFICATION: Include case_number in the link ***
            $encodedEngineerId = base64_encode($engineerId);
            $encodedCaseNumber = base64_encode($caseNumber); // Encode case number
            // *** Construct link with both parameters ***
            $ratingLink = "https://group5.cs42a.com/sts/rate_engineer.php?id=$encodedEngineerId&case=$encodedCaseNumber";

            $mail->Subject = "Technical Assistance Case #$caseNumber Resolved";
            $mail->isHTML(true);
            $mail->Body = "
                <p>Hello <b>$userName</b>,</p>

                <p>We are pleased to inform you that your technical assistance case <b>#$caseNumber</b> has been successfully resolved by <b>$engineerName</b>. If you encounter any further issues, feel free to reach out.</p>

                <p>We value your feedback! Please take a moment to rate your experience with <b>$engineerName</b> regarding this case by clicking the link below:</p>

                <p><a href='$ratingLink' style='display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Rate Your Engineer</a></p>
                <p><small>If the button doesn't work, copy and paste this link into your browser: $ratingLink</small></p>

                <p>Thank you for reaching out to our support team.</p>

                <p>Best regards,<br><b>Technical Support Team</b></p>
            ";

            $mail->send();
             // Commit transaction if everything succeeded
            mysqli_commit($connection);

            // Redirect only after successful commit and email send attempt
            header("Location: ../$folder/$destination?success=1");
            exit;

        } catch (Exception $e) {
             // Rollback transaction on email error
            mysqli_rollback($connection);
            // Log the error or display a more user-friendly message
            error_log("Mailer Error for case $caseNumber: {$mail->ErrorInfo}");
            echo "Error: Could not send email notification. Please contact support. Mailer Error: {$mail->ErrorInfo}";
            // Don't redirect here, show the error
            exit;
        }

    } catch (Exception $e) {
        // Rollback transaction on database error
        mysqli_rollback($connection);
        // Log the error or display a more user-friendly message
        error_log("Database Error processing case $caseNumber: " . $e->getMessage());
        echo "An error occurred while processing the case: " . $e->getMessage();
         // Don't redirect here, show the error
        exit;
    } finally {
        // Close the connection if it's still open
         if (isset($connection) && mysqli_ping($connection)) {
            mysqli_close($connection);
         }
    }


} else {
    // Redirect or show error if not a POST request
    header("Location: ../index.php"); // Redirect to a default page
    exit;
}
?>