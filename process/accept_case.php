<?php
session_start();
require_once "../config/database.php"; // Ensure this path is correct
// Use PHPMailer for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Ensure this path points to PHPMailer's autoload

// Function to retrieve email settings from the database
function get_email_settings($connection) {
    $settings = [];
    $query = "SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'"; // Fetch SMTP settings
    $result = $connection->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $result->free();
    } else {
        error_log("Failed to fetch email settings from database: " . $connection->error); // Log error if query fails
    }
    return $settings;
}

// Check database connection (assuming $connection is established in database.php)
if (!$connection) {
    error_log("Database connection failed in accept_case script: " . mysqli_connect_error());
    // Provide a user-friendly error message and stop execution
    die("Critical Error: Database connection failed. Please contact support.");
}

// Process only POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // --- Input Validation ---
    // Validate Case ID
    if (!isset($_POST["case_id"]) || !filter_var($_POST["case_id"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        $_SESSION['error_message'] = "Invalid or missing Case ID.";
        header("Location: ../engineer/new_cases.php?error=invalid_case_id"); // Redirect with error
        exit;
    }
    // Validate User Session
    if (!isset($_SESSION["user_id"]) || !filter_var($_SESSION["user_id"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        $_SESSION['error_message'] = "Your session has expired or is invalid. Please log in again.";
        header("Location: ../login.php?error=session_expired"); // Redirect to login
        exit;
    }

    // --- Variable Initialization ---
    $caseId = (int)$_POST["case_id"]; // The primary ID of the case (used for fetching related data)
    $engineerId = (int)$_SESSION["user_id"]; // ID of the engineer accepting the case
    $caseStatus = "Waiting in Progress"; // Status to set when accepted
    $caseNumber = null; // To be fetched from the database
    $engineerName = 'Support Engineer'; // Default, will be fetched
    $userName = null; // Case owner's name, to be fetched
    $userEmail = null; // Case owner's email, to be fetched
    $errorMessage = null; // To store any non-fatal error messages
    $emailSent = false; // Flag for email status

    // --- Fetch Engineer's Full Name ---
    $getEngineerNameStmt = mysqli_prepare($connection, "SELECT full_name FROM users WHERE id = ? LIMIT 1");
    if ($getEngineerNameStmt) {
        mysqli_stmt_bind_param($getEngineerNameStmt, "i", $engineerId);
        mysqli_stmt_execute($getEngineerNameStmt);
        $resultEng = mysqli_stmt_get_result($getEngineerNameStmt);
        if($rowEng = mysqli_fetch_assoc($resultEng)){
            $engineerName = $rowEng['full_name']; // Assign fetched name
        } else {
            error_log("Could not find engineer name for ID: $engineerId"); // Log if engineer not found
        }
        mysqli_free_result($resultEng);
        mysqli_stmt_close($getEngineerNameStmt);
    } else {
        error_log("Failed to prepare statement for fetching engineer name: " . mysqli_error($connection));
        // Continue execution, but use the default name
    }

    // --- Fetch Case Number using Case ID ---
    $getCaseNumberStmt = mysqli_prepare($connection, "SELECT case_number FROM cases WHERE id = ? LIMIT 1");
    if ($getCaseNumberStmt) {
        mysqli_stmt_bind_param($getCaseNumberStmt, "i", $caseId);
        mysqli_stmt_execute($getCaseNumberStmt);
        $getCaseNumberResult = mysqli_stmt_get_result($getCaseNumberStmt);
        if ($row = mysqli_fetch_assoc($getCaseNumberResult)) {
            $caseNumber = $row["case_number"]; // Store the fetched case number
        } else {
            // Case not found, critical error
            $_SESSION['error_message'] = "Case with ID $caseId not found.";
            mysqli_stmt_close($getCaseNumberStmt);
            header("Location: ../engineer/new_cases.php?error=case_not_found");
            exit;
        }
        mysqli_free_result($getCaseNumberResult);
        mysqli_stmt_close($getCaseNumberStmt);
    } else {
        // Database error, critical
        error_log("Failed to prepare statement for fetching case number: " . mysqli_error($connection));
        $_SESSION['error_message'] = "Error retrieving case details.";
        header("Location: ../error_page.php?error=db_prepare_casenum"); // Redirect to a generic error page
        exit;
    }

    // --- Update Case using Case Number ---
    // Update the case identified by case_number, assign the engineer, set status, and record acceptance date.
    // IMPORTANT: The WHERE clause ensures we only update unassigned cases to prevent race conditions.
    $updateCaseStmt = mysqli_prepare($connection, "UPDATE cases SET user_id = ?, case_status = ?, last_modified = NOW(), date_accepted = NOW() WHERE case_number = ? AND (user_id IS NULL OR user_id = 0)");
    if ($updateCaseStmt) {
        // Bind parameters: engineerId (integer), caseStatus (string), caseNumber (string)
        mysqli_stmt_bind_param($updateCaseStmt, "iss", $engineerId, $caseStatus, $caseNumber);
        if (!mysqli_stmt_execute($updateCaseStmt)) {
            // Execute error, critical
            error_log("Failed to execute statement for updating case using case_number $caseNumber: " . mysqli_stmt_error($updateCaseStmt));
            $_SESSION['error_message'] = "Error accepting case status.";
            mysqli_stmt_close($updateCaseStmt);
            header("Location: ../error_page.php?error=db_execute_accept");
            exit;
        }

        // Check if any row was actually updated
        $affectedRows = mysqli_stmt_affected_rows($updateCaseStmt);
        mysqli_stmt_close($updateCaseStmt);

        // If no rows affected, the case was likely already assigned by another engineer
        if ($affectedRows === 0) {
            error_log("Accept case failed (affectedRows=0) for case number $caseNumber by engineer ID $engineerId. Case might be already assigned.");
            $_SESSION['error_message'] = "Could not accept case #$caseNumber. It might already be assigned to another.";
            header("Location: ../engineer/new_cases.php?error=already_assigned"); // Redirect back with specific error
            exit;
        }
        // If affectedRows > 0, the update was successful

    } else {
        // Prepare statement error, critical
        error_log("Failed to prepare statement for updating case using case_number: " . mysqli_error($connection));
        $_SESSION['error_message'] = "Error preparing case update.";
        header("Location: ../error_page.php?error=db_prepare_accept");
        exit;
    }

    // --- Fetch Case Owner Details for Notification (using case ID) ---
    // Case is accepted, now get owner details for email
    $queryUserStmt = mysqli_prepare($connection, "SELECT u.full_name, u.email FROM cases c JOIN users u ON c.case_owner = u.id WHERE c.id = ? LIMIT 1");
    if ($queryUserStmt) {
        mysqli_stmt_bind_param($queryUserStmt, "i", $caseId);
        mysqli_stmt_execute($queryUserStmt);
        $resultUser = mysqli_stmt_get_result($queryUserStmt);
        if($rowUser = mysqli_fetch_assoc($resultUser)){
            $userName = $rowUser['full_name']; // Get owner's name
            $userEmail = $rowUser['email']; // Get owner's email
        } else {
             // Log if owner details couldn't be fetched (non-critical, email won't send)
             error_log("Could not fetch case owner details for case ID $caseId after accepting.");
        }
        mysqli_free_result($resultUser);
        mysqli_stmt_close($queryUserStmt);
    } else {
        error_log("Failed to prepare statement for fetching user details: " . mysqli_error($connection));
        // Continue, but email won't send
    }

    // --- Send Email Notification & Save to DB ---
    // Proceed only if owner name and email were successfully fetched
    if ($userName !== null && $userEmail !== null) {
        // --- Prepare Email Content ---
        // Plain text version for email clients that don't support HTML
         $plainMessage = "Hello " . htmlspecialchars($userName) . ",\n\n"
             . "We have received your request for technical assistance (Case #" . htmlspecialchars($caseNumber) . "). One of our support engineers, " . htmlspecialchars($engineerName) . ", has accepted your case and will assist you during regular support hours.\n\n"
             . "To help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility via the support portal.\n\n"
             . "Thank you,\nTechnical Support Team";

        // HTML version for better formatting
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

        // --- Insert Notification into Database ---
        // Store a record of the notification sent (or attempted)
        $insertNotificationStmt = mysqli_prepare($connection, "INSERT INTO notifications (case_id, recipient_username, recipient_email, message_subject, message_body) VALUES (?, ?, ?, ?, ?)");
        if ($insertNotificationStmt) {
            // Use plain message for database storage for simplicity
            mysqli_stmt_bind_param($insertNotificationStmt, "issss", $caseId, $userName, $userEmail, $emailSubject, $plainMessage);
            if (!mysqli_stmt_execute($insertNotificationStmt)) {
                error_log("Failed to save notification to database for case $caseId: " . mysqli_stmt_error($insertNotificationStmt));
            }
            mysqli_stmt_close($insertNotificationStmt);
        } else {
            error_log("Failed to prepare statement for saving notification: " . mysqli_error($connection));
        }

        // --- Attempt to Send Email using PHPMailer ---
        $email_settings = get_email_settings($connection); // Fetch SMTP settings
        if (empty($email_settings)) {
            // Log error if settings are missing
            error_log("Critical Error: Failed to load email settings from database for case accept $caseId.");
            $errorMessage = "Case accepted, but email settings failed. Notification not sent."; // Set warning message
        } else {
            $mail = new PHPMailer(true); // Enable exceptions
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = $email_settings['smtp_host'] ?? 'smtp.example.com'; // Use fetched or default host
                $mail->SMTPAuth = true;
                $mail->Username = $email_settings['smtp_username'] ?? ''; // Use fetched username
                $mail->Password = $email_settings['smtp_password'] ?? ''; // Use fetched password
                $secure_type = strtolower($email_settings['smtp_secure'] ?? 'tls'); // tls or ssl
                if ($secure_type === 'tls') { $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; }
                elseif ($secure_type === 'ssl') { $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; }
                else { $mail->SMTPSecure = false; } // No encryption
                $mail->Port = (int)($email_settings['smtp_port'] ?? 587); // Use fetched or default port

                // Recipients
                $mail->setFrom( $email_settings['smtp_from_email'] ?? 'noreply@example.com', $email_settings['smtp_from_name'] ?? 'Technical Support' );
                $mail->addAddress($userEmail, $userName); // Add case owner as recipient

                // Content
                $mail->isHTML(true);
                $mail->Subject = $emailSubject;
                $mail->Body = $emailBody;
                $mail->AltBody = $plainMessage; // Plain text version

                $mail->send();
                $emailSent = true; // Mark email as sent successfully
                error_log("Acceptance email sent for case $caseNumber to $userEmail.");

            } catch (Exception $e) {
                // Email sending failed
                $emailSent = false;
                 // Log detailed error, including connection details (excluding password)
                 error_log("Mailer Error (Accept Case $caseNumber) to $userEmail using Host: "
                     . ($mail->Host ?? 'N/A') . ", Port: " . ($mail->Port ?? 'N/A')
                     . ", Secure: " . ($mail->SMTPSecure === false ? 'None' : ($mail->SMTPSecure ?? 'N/A'))
                     . ", Username: " . ($mail->Username ?? 'N/A') // Avoid logging password
                     . ". PHPMailer Error: {$mail->ErrorInfo}");
                $errorMessage = "Case accepted, but the notification email could not be sent. Mailer Error: " . htmlspecialchars($mail->ErrorInfo); // Set warning message for user
            }
        }
    } else {
         // Handle case where owner details were missing
         error_log("Case accepted (ID $caseId), but user email ($userEmail) or name ($userName) is missing. Notification not sent.");
         $errorMessage = "Case accepted, but notification could not be sent (missing owner details)."; // Set warning message
    }

    // --- Set Session Message and Redirect ---
    if ($errorMessage) {
        // If there was a non-critical error (like email failure), set a warning message
        $_SESSION['warning_message'] = $errorMessage; // This should be displayed on the target page
    } else {
        // If everything went well, set a success message
        $_SESSION['success_message'] = "Case #" . htmlspecialchars($caseNumber) . " accepted successfully.";
    }

    // Close the database connection if it's still open
    if (isset($connection) && $connection instanceof mysqli) {
        mysqli_close($connection);
    }

    // Redirect to the On-going Cases page regardless of email success/failure
    header("Location: ../engineer/ongoing_cases.php?status=accepted"); // Add status for potential display logic
    exit;

} else {
    // If not a POST request, redirect to dashboard or login
    header("Location: ../engineer/dashboard.php"); // Or perhaps login page
    exit;
}

// Final check to close connection if script exits unexpectedly before redirection
if (isset($connection) && $connection instanceof mysqli) {
    mysqli_close($connection);
}
?>