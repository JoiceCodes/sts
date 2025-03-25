<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $otp = $_POST["otp"];
    $email = $_SESSION["email"];

    $getUserId = mysqli_prepare($connection, "SELECT id FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($getUserId, "s", $email);
    mysqli_stmt_execute($getUserId);
    $getUserIdResult = mysqli_stmt_get_result($getUserId);
    $row = mysqli_fetch_assoc($getUserIdResult);
    $user_id = $row["id"];

    $getOtp = mysqli_prepare($connection, "SELECT * FROM forgot_password_requests WHERE user_id = ? AND reset_token = ? AND expiry_time > NOW() LIMIT 1");
    mysqli_stmt_bind_param($getOtp, "is", $user_id, $otp);
    mysqli_stmt_execute($getOtp);
    $getOtpResult = mysqli_stmt_get_result($getOtp);
    if (mysqli_num_rows($getOtpResult) > 0) {
        $deleteOtp = mysqli_prepare($connection, "DELETE FROM forgot_password_requests WHERE user_id = ?");
        mysqli_stmt_bind_param($deleteOtp, "i", $user_id);
        mysqli_stmt_execute($deleteOtp);
        header("Location: ../forgot-password-reset-password.php?success=1");
        exit;
    } else {
        header("Location: ../forgot-password-verify-otp.php?error=1");
        exit;
    }
}
