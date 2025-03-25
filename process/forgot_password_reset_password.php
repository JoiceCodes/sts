<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST["password"];
    $repeat_password = $_POST["repeat_password"];

    if ($password !== $repeat_password) {
        header("Location: ../forgot-password-reset-password.php?error=1");
        exit;
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    $email = $_SESSION["email"];

    $setPassword = mysqli_prepare($connection, "UPDATE users SET password = ? WHERE email = ?");
    mysqli_stmt_bind_param($setPassword, "ss", $password, $email);
    mysqli_stmt_execute($setPassword);
    header("Location: ../index.php?password_reset=1");
    exit;
}