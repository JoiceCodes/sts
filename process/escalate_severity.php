<?php
session_start();
require_once "../config.php";

switch ($_SESSION["user_role"]) {
    case "Technical Engineer":
        $folder = "technical_engineer";
        break;
    case "Technical Head":
        $folder = "technical_head";
        break;
}

if ($_SERVER["REQUEST_METHOD"] === "POSt") {
    $caseId = $_POST["case_id"];
    $severity = $_POST["severity"];

    $setSeverity = mysqli_prepare($connection, "UPDATE cases SET severity = ? WHERE id = ?");
    mysqli_stmt_bind_param($setSeverity, "si", $severity, $caseId);
    mysqli_stmt_execute($setSeverity);
    mysqli_stmt_close($setSeverity);
    header("Location: ../$folder/ongoing_cases.php");
    exit;
}