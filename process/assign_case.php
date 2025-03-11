<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseId = $_POST["case_id"];
    $engineerId = $_POST["engineer"];
    $caseStatus = "Waiting in Progress";

    $getEngineer = mysqli_prepare($connection, "SELECT * FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($getEngineer, "i", $engineerId);
    mysqli_stmt_execute($getEngineer);
    $getEngineersResult = mysqli_stmt_get_result($getEngineer);
    if (mysqli_num_rows($getEngineersResult) > 0) {
        $row = mysqli_fetch_assoc($getEngineersResult);
        $engineer = $row["id"];
    }

    $setCase = mysqli_prepare($connection, "UPDATE cases SET user_id = ?, case_status = ?, datetime_opened = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($setCase, "isi", $engineer, $caseStatus, $caseId);
    mysqli_stmt_execute($setCase);

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