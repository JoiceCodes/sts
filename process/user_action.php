<?php 
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productId = $_POST["user_id"];
    $action = $_POST["action"];

    switch ($action) {
        case "activate":
            $status = "Active";
            break;
        case "deactivate":
            $status = "Deactivated";
            break;
    }

    switch ($_SESSION["user_role"]) {
        case "Technical Engineer":
            $folder = "technical_engineer";
            break;
        case "Technical Head":
            $folder = "technical_head";
            break;
    }

    $getUserRole = mysqli_prepare($connection, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($getUserRole, "i", $productId);
    mysqli_stmt_execute($getUserRole);
    $getUserRoleResult = mysqli_stmt_get_result($getUserRole);
    if (mysqli_num_rows($getUserRoleResult) > 0) {
        $row = mysqli_fetch_assoc($getUserRoleResult);
        $userRole = $row["role"];
    }

    switch ($userRole) {
        case "Engineer":
            $destination = "engineers.php";
            break;
        case "User":
            $destination = "users.php";
            break;
    }

    $setUserStatus = mysqli_prepare($connection, "UPDATE users SET account_status = ? WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($setUserStatus, "si", $status, $productId);
    mysqli_stmt_execute($setUserStatus);

    header("Location: ../$folder/$destination?success=1");
    exit;
}