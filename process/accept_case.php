<?php
session_start();
require_once "../config/database.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Adjust path as needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseId = $_POST["case_id"];
    $engineerId = $_SESSION["user_id"];
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
    $updateCase = mysqli_prepare($connection, "UPDATE cases SET user_id = ?, case_status = ? WHERE id = ?");
    mysqli_stmt_bind_param($updateCase, "isi", $engineerId, $caseStatus, $caseId);
    mysqli_stmt_execute($updateCase);

    // Fetch user details who created the case
    $queryUser = mysqli_prepare($connection, "SELECT users.full_name, users.email FROM cases 
                                              JOIN users ON cases.case_owner = users.id 
                                              WHERE cases.id = ?");
    mysqli_stmt_bind_param($queryUser, "i", $caseId);
    mysqli_stmt_execute($queryUser);
    mysqli_stmt_bind_result($queryUser, $userName, $userEmail);
    mysqli_stmt_fetch($queryUser);
    mysqli_stmt_close($queryUser);

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

        $mail->Subject = "Technical Assistance Request Received - Case #$caseNumber";
        $mail->isHTML(true);
        $mail->Body = "
            <p>Hello <b>$userName</b>,</p>
            
            <p>We got your request for technical assistance. One of our support engineers, <b>$engineerName</b>, has accepted your case and will assist you further during regular support hours.</p>
            
            <p>Please include any screenshots or error messages that might help us diagnose the issue faster. You can also provide the output of the WUG MD utility for troubleshooting:</p>
   
            <p>Thank you,<br><b>Technical Support Team</b></p>
        ";

        $mail->send();
    } catch (Exception $e) {
        echo "Error: Could not send email. Mailer Error: {$mail->ErrorInfo}";
    }

    header("Location: ../engineer/new_cases.php?success=1");
    exit;
}
?>
