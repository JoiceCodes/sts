<?php
session_start();
require_once "../config/database.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Adjust path as needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseId = $_POST["case_id"];
    $engineerId = $_POST["engineer"];
    $caseStatus = "Waiting in Progress";

    $getCaseNumber = mysqli_prepare($connection, "SELECT case_number FROM cases WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($getCaseNumber, "i", $caseId);
    mysqli_stmt_execute($getCaseNumber);
    $getCaseNumberResult = mysqli_stmt_get_result($getCaseNumber);
    if (mysqli_num_rows($getCaseNumberResult) > 0) {
        $row = mysqli_fetch_assoc($getCaseNumberResult);
        $caseNumber = $row["case_number"];
    }

    // Update case in database
    $setCase = mysqli_prepare($connection, "UPDATE cases SET user_id = ?, case_status = ?, datetime_opened = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($setCase, "isi", $engineerId, $caseStatus, $caseId);
    mysqli_stmt_execute($setCase);

    // Fetch user details who created the case
    $queryUser = mysqli_prepare($connection, "SELECT users.full_name, users.email FROM cases 
                                              JOIN users ON cases.case_owner = users.id 
                                              WHERE cases.id = ?");
    mysqli_stmt_bind_param($queryUser, "i", $caseId);
    mysqli_stmt_execute($queryUser);
    mysqli_stmt_bind_result($queryUser, $userName, $userEmail);
    mysqli_stmt_fetch($queryUser);
    mysqli_stmt_close($queryUser);

    // Fetch engineer details
    $queryEngineer = mysqli_prepare($connection, "SELECT full_name FROM users WHERE id = ?");
    mysqli_stmt_bind_param($queryEngineer, "i", $engineerId);
    mysqli_stmt_execute($queryEngineer);
    mysqli_stmt_bind_result($queryEngineer, $engineerName);
    mysqli_stmt_fetch($queryEngineer);
    mysqli_stmt_close($queryEngineer);

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

        $mail->Subject = "Technical Assistance Request Assigned - Case #$caseNumber";
        $mail->isHTML(true);
        $mail->Body = "
            <p>Hello <b>$userName</b>,</p>
            
            <p>Your request for technical assistance has been assigned to <b>$engineerName</b>. The engineer will contact you during regular support hours to assist further.</p>
            
            <p>Please include any relevant screenshots or error messages to help resolve the issue faster.</p>
            
            <p>Thank you,<br><b>Technical Support Team</b></p>
        ";

        $mail->send();
    } catch (Exception $e) {
        echo "Error: Could not send email. Mailer Error: {$mail->ErrorInfo}";
    }

    // Redirect based on user role
    switch($_SESSION["user_role"]) {
        case "Technical Engineer":
            $folder = "technical_engineer";
            break;
        case "Technical Head":
            $folder = "technical_head";
            break;
    }

    header("Location: ../$folder/new_cases.php?success=1");
    exit;
}
?>
