<?php
session_start();
require_once "../config/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Adjust path as needed


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseNumber = $_POST["case_number"];
    $isReopen = $_POST["is_reopen"] === "true" ? true : false;
    $caseStatus = "Solved";

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
    }

    switch ($isReopen) {
        case true:
            $destination = "reopened_cases.php";
            break;
        case false:
            $destination = "ongoing_cases.php";
            break;
    }

    $setCaseNumberStatus = mysqli_prepare($connection, "UPDATE cases SET case_status = ? WHERE case_number = ? LIMIT 1");
    mysqli_stmt_bind_param($setCaseNumberStatus, "ss", $caseStatus, $caseNumber);
    mysqli_stmt_execute($setCaseNumberStatus);

    $getEngineer = mysqli_prepare($connection, "SELECT 
    u.id,
    u.full_name
    FROM users AS u
    INNER JOIN cases AS c ON u.id = c.user_id
    WHERE c.case_number = ?");
    mysqli_stmt_bind_param($getEngineer, "s", $caseNumber);
    mysqli_stmt_execute($getEngineer);
    $getEngineerResult = mysqli_stmt_get_result($getEngineer);
    if (mysqli_num_rows($getEngineerResult) > 0) {
        $row = mysqli_fetch_assoc($getEngineerResult);
        $engineerId = $row["id"];
        $engineerName = $row["full_name"];
    }

    $getUser = mysqli_prepare($connection, "SELECT 
    u.full_name,
    u.email
    FROM users AS u
    INNER JOIN cases AS c ON u.id = c.case_owner
    WHERE c.case_number = ?");
    mysqli_stmt_bind_param($getUser, "s", $caseNumber);
    mysqli_stmt_execute($getUser);
    $getUserResult = mysqli_stmt_get_result($getUser);
    if (mysqli_num_rows($getUserResult) > 0) {
        $row = mysqli_fetch_assoc($getUserResult);
        $userEmail = $row["email"];
        $userName = $row["full_name"];
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

        $encodedEngineerId = base64_encode($engineerId);
        $ratingLink = "http://localhost/sts/rate_engineer.php?id=$encodedEngineerId";

        $mail->Subject = "Technical Assistance Case #$caseNumber Resolved";
        $mail->isHTML(true);
        $mail->Body = "
            <p>Hello <b>$userName</b>,</p>
    
            <p>We are pleased to inform you that your technical assistance case <b>#$caseNumber</b> has been successfully resolved by <b>$engineerName</b>. If you encounter any further issues, feel free to reach out.</p>
    
            <p>We value your feedback! Please take a moment to rate your experience with <b>$engineerName</b> by clicking the link below:</p>
    
            <p><a href='$ratingLink' style='color: blue; text-decoration: underline;'>Rate Your Engineer</a></p>
    
            <p>Thank you for reaching out to our support team.</p>
    
            <p>Best regards,<br><b>Technical Support Team</b></p>
        ";

        $mail->send();
    } catch (Exception $e) {
        echo "Error: Could not send email. Mailer Error: {$mail->ErrorInfo}";
    }

    header("Location: ../$folder/$destination?success=1");
    exit;
}
