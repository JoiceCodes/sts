<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Adjust path as needed
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];

    // Prepare to retrieve user data
    $getUser = mysqli_prepare($connection, "SELECT * FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($getUser, "s", $email);
    mysqli_stmt_execute($getUser);
    $getUserResult = mysqli_stmt_get_result($getUser);

    if (mysqli_num_rows($getUserResult) > 0) {
        // Generate OTP (One Time Password)
        $otp = rand(100000, 999999); // Random 6-digit OTP

        // Insert OTP into forgot_password_requests table with a timestamp and expiry
        $expiry_time = date("Y-m-d H:i:s", strtotime("+1 hour")); // OTP expires in 1 hour
        $user = mysqli_fetch_assoc($getUserResult);
        $user_id = $user['id'];

        $insertOtp = mysqli_prepare($connection, "INSERT INTO forgot_password_requests (user_id, reset_token, expiry_time) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insertOtp, "iss", $user_id, $otp, $expiry_time);
        mysqli_stmt_execute($insertOtp);

        // Send OTP email to the user
        $mail = new PHPMailer(true);

        $emailBody = "
            <p>Dear User,</p>
            <p>You have requested to reset your password. Please use the following OTP to reset your password:</p>
            <h2>$otp</h2>
            <p>This OTP will expire in 1 hour.</p>
            <p>If you did not request this, please ignore this email.</p>
            <br>
            <p>Thank you, <br> Support Team</p>
        ";

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'joicebarandon31@gmail.com'; // Your SMTP username
            $mail->Password = 'gmbviduachzzyazu'; // Your SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Sender and recipient
            $mail->setFrom('joicebarandon31@gmail.com', 'Support Team');
            $mail->addAddress($email); // Send OTP to the user's email

            // Email subject and body
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->isHTML(true);
            $mail->Body = $emailBody;
            
            // Send the email
            $mail->send();
            session_start();
            $_SESSION["email"] = $email;
            header("Location: ../forgot-password-verify-otp.php?success=1");
            exit;
        } catch (Exception $e) {
            echo "Error: Could not send email. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        header("Location: ../forgot-password.php?error=1");
        exit;
    }
}
