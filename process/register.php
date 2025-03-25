<?php

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $repeat_password = $_POST["repeat_password"];

    if ($password !== $repeat_password) {
        header("Location: ../register.php?error=1");
        exit();
    }

    $full_name = $first_name . " " . $last_name;

    $password = password_hash($password, PASSWORD_DEFAULT);

    $role = "User";
    $setUser = mysqli_prepare($connection, "INSERT INTO users (full_name, email, username, password, role) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($setUser, "sssss", $full_name, $email, $username, $password, $role);
    mysqli_stmt_execute($setUser);
    header("Location: ../index.php?register=1");
    exit();
}