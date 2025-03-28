<?php
session_start();
require_once "../config/database.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["case_id"]) || !filter_var($_POST["case_id"], FILTER_VALIDATE_INT)) {
        die("Invalid Case ID.");
    }
    if (!isset($_SESSION["user_id"]) || !filter_var($_SESSION["user_id"], FILTER_VALIDATE_INT)) {
        header("Location: ../login.php?error=session_expired");
        exit;
    }

    $caseId = (int)$_POST["case_id"];
    $engineerId = (int)$_SESSION["user_id"];
    $caseStatus = "Waiting in Progress";
    $caseNumber = null;
    $engineerName = 'Support Engineer';

    // --- Fetch Engineer's Name ---
    $getEngineerNameStmt = mysqli_prepare($connection, "SELECT full_name FROM users WHERE id = ? LIMIT 1");
    if ($getEngineerNameStmt) {
        mysqli_stmt_bind_param($getEngineerNameStmt, "i", $engineerId);
        mysqli_stmt_execute($getEngineerNameStmt);
        mysqli_stmt_bind_result($getEngineerNameStmt, $fetchedEngineerName);
        if (mysqli_stmt_fetch($getEngineerNameStmt)) {
            $engineerName = $fetchedEngineerName;
        }
        mysqli_stmt_close($getEngineerNameStmt);
    } else {
        error_log("Failed to prepare statement for fetching engineer name: " . mysqli_error($connection));
    }

    // --- Get Case Number ---
    $getCaseNumberStmt = mysqli_prepare($connection, "SELECT case_number FROM cases WHERE id = ? LIMIT 1");
    if ($getCaseNumberStmt) {
        mysqli_stmt_bind_param($getCaseNumberStmt, "i", $caseId);
        mysqli_stmt_execute($getCaseNumberStmt);
        $getCaseNumberResult = mysqli_stmt_get_result($getCaseNumberStmt);
        if ($row = mysqli_fetch_assoc($getCaseNumberResult)) {
            $caseNumber = $row["case_number"];
        }
        mysqli_stmt_close($getCaseNumberStmt);
    } else {
        error_log("Failed to prepare statement for fetching case number: " . mysqli_error($connection));
        die("Error retrieving case details.");
    }

    if ($caseNumber === null) {
        die("Case not found.");
    }

    // --- Update case in database ---
    $updateCaseStmt = mysqli_prepare($connection, "UPDATE cases SET user_id = ?, case_status = ? WHERE id = ?");
    if ($updateCaseStmt) {
        mysqli_stmt_bind_param($updateCaseStmt, "isi", $engineerId, $caseStatus, $caseId);
        if (!mysqli_stmt_execute($updateCaseStmt)) {
            error_log("Failed to execute statement for updating case: " . mysqli_stmt_error($updateCaseStmt));
            die("Error updating case status.");
        }
        mysqli_stmt_close($updateCaseStmt);
    } else {
        error_log("Failed to prepare statement for updating case: " . mysqli_error($connection));
        die("Error preparing case update.");
    }

    // --- Fetch user details who created the case ---
    $userName = null;
    $userEmail = null;
    $queryUserStmt = mysqli_prepare($connection, "SELECT users.full_name, users.email FROM cases JOIN users ON cases.case_owner = users.id WHERE cases.id = ? LIMIT 1");
    if ($queryUserStmt) {
        mysqli_stmt_bind_param($queryUserStmt, "i", $caseId);
        mysqli_stmt_execute($queryUserStmt);
        mysqli_stmt_bind_result($queryUserStmt, $fetchedUserName, $fetchedUserEmail);
        if (mysqli_stmt_fetch($queryUserStmt)) {
            $userName = $fetchedUserName;
            $userEmail = $fetchedUserEmail;
        }
        mysqli_stmt_close($queryUserStmt);
    } else {
        error_log("Failed to prepare statement for fetching user details: " . mysqli_error($connection));
        die("Error retrieving user details for notification.");
    }

    if ($userName === null || $userEmail === null) {
        die("Could not find owner details for the case.");
    }

    // --- Prepare Plain Text Message for Database ---
    $plainMessage = "Hello " . htmlspecialchars($userName) . ",\n\n"
        . "We have received your request for technical assistance (Case #" . htmlspecialchars($caseNumber) . "). One of our support engineers, " . htmlspecialchars($engineerName) . ", has accepted your case and will assist you during regular support hours.\n\n"
        . "To help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility.\n\n"
        . "Thank you,\nTechnical Support Team";

    // --- Prepare Email Notification ---
    $mail = new PHPMailer(true);
    $emailSubject = "Technical Assistance Request Received - Case #$caseNumber";

    // Enhanced Email Body (HTML)
    $emailBody = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Technical Assistance Request Received</title>
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
            <h1>Technical Assistance Request Received</h1>
            <p>Hello <span class='highlight'>" . htmlspecialchars($userName) . "</span>,</p>
            <p>We have received your request for technical assistance (Case #<span class='highlight'>" . htmlspecialchars($caseNumber) . "</span>). One of our support engineers, <span class='highlight'>" . htmlspecialchars($engineerName) . "</span>, has accepted your case and will assist you during regular support hours.</p>
            <p>To help us diagnose the issue faster, please provide any relevant screenshots, error messages, or the output of the WUG MD utility.</p>
            <p class='footer'>Thank you,<br>Technical Support Team</p>
        </div>
    </body>
    </html>
    ";

    // --- Save notification to database (Plain Text) ---
    $insertNotificationStmt = mysqli_prepare($connection, "INSERT INTO notifications (case_id, recipient_username, recipient_email, message_subject, message_body) VALUES (?, ?, ?, ?, ?)");
    if ($insertNotificationStmt) {
        mysqli_stmt_bind_param($insertNotificationStmt, "issss", $caseId, $userName, $userEmail, $emailSubject, $plainMessage); // Store plainMessage
        if (!mysqli_stmt_execute($insertNotificationStmt)) {
            error_log("Failed to save notification to database: " . mysqli_stmt_error($insertNotificationStmt));
        }
        mysqli_stmt_close($insertNotificationStmt);
    } else {
        error_log("Failed to prepare statement for saving notification: " . mysqli_error($connection));
    }

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

        // Content (HTML)
        $mail->isHTML(true);
        $mail->Subject = $emailSubject;
        $mail->Body = $emailBody; // Send HTML email

        $mail->send();
    } catch (Exception $e) {
        error_log("Error: Could not send email for Case ID $caseId. Mailer Error: {$mail->ErrorInfo}");
    }

    header("Location: ../engineer/new_cases.php?success=1");
    exit;
} else {
    header("Location: ../engineer/dashboard.php");
    exit;
}
?>