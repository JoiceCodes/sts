<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseId = $_POST["case_id"];
    $engineerId = $_POST["engineer"];

    $setEngineer = mysqli_prepare($connection, "UPDATE cases SET user_id = ? WHERE id = ?");
    mysqli_stmt_bind_param($setEngineer, "ii", $engineerId, $caseId);
    mysqli_stmt_execute($setEngineer);

    switch ($_SESSION["user_role"]) {
        case "Technical Engineer":
            $folder = "technical_engineer";
            break;
        case "Technical Head":
            $folder = "technical_head";
            break;
    }

    header("Location: ../$folder/ongoing_cases.php?reassigned=1");
    exit;
}