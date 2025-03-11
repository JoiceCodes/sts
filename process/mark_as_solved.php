<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseNumber = $_POST["case_number"];
    $isReopen = $_POST["is_reopen"] === "true" ? true : false;
    $caseStatus = "Solved";

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

    switch ($isReopen) {
        case true:
            $destination = "reopened_cases.php";
            break;
        case false:
            $destination = "ongoing_cases.php";
            break;
    }

    $setCaseNumberStatus = mysqli_prepare($connection, "UPDATE cases SET case_status = ? WHERE case_number = ? LIMIT 1");
    mysqli_stmt_bind_param($setCaseNumberStatus, "ss", $caseStatus, $caseNumber);
    mysqli_stmt_execute($setCaseNumberStatus);

    header("Location: ../$folder/$destination?success=1");
    exit;
}
