<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseNumber = $_POST["case_number"];
    $caseStatus = "Waiting in Progress";

    $reopenCase = mysqli_prepare($connection, "UPDATE cases SET case_status = ?, reopen = reopen + 1 WHERE case_number = ?");
    mysqli_stmt_bind_param($reopenCase, "ss", $caseStatus, $caseNumber);
    mysqli_stmt_execute($reopenCase);

    switch ($_SESSION["user_role"]) {
        case "Engineer":
            $folder = "engineer";
            break;
        case "Technical Engineer":
            $folder = "technical_engineer";
            break;
        case "Technical Head":
            $folder = "technical_head";
            break;
    }

    header("Location: ../$folder/solved_cases.php?success=1");
    exit;
}