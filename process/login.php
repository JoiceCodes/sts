<?php
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $getUser = mysqli_prepare($connection, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($getUser, "s", $username);
    mysqli_stmt_execute($getUser);
    $getUserResult = mysqli_stmt_get_result($getUser);
    if (mysqli_num_rows($getUserResult) > 0) {
        $userRow = mysqli_fetch_assoc($getUserResult);
        if (password_verify($password, $userRow["password"]) || $password == $userRow["password"]) {

            session_start();
            $_SESSION["user_id"] = $userRow["id"];
            $_SESSION["user_full_name"] = $userRow["full_name"];
            $_SESSION["user_email"] = $userRow["email"];
            $_SESSION["user_username"] = $userRow["username"];
            $_SESSION["user_role"] = $userRow["role"];

            switch ($_SESSION["user_role"]) {
                case "Administrator":
                    $folder = "administrator";
                    break;
                case "Technical Head":
                    $folder = "technical_head";
                    break;
                case "Engineer":
                    $folder = "engineer";
                    break;
                case "User":
                    $folder = "user";
                    break;
            }

            if (isset($_POST["rate"]) && $_GET["rate"]) {
                header("Location: ../rate_engineer.php");
                exit;
            } else {
                header("Location: ../$folder/home.php");
                exit;
            }
        } else {
            echo "Invalid username or password";
        }
    } else {
        echo "Invalid username or password";
    }
}
