<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseId = $_POST["case_id"];
    $contactName = $_SESSION["user_full_name"];
    $caseStatus = "Waiting in Progress";

    $setCase = mysqli_prepare($connection, "UPDATE cases SET contact_name = ?, case_status = ? WHERE id = ?");
    mysqli_stmt_bind_param($setCase, "ssi", $contactName, $caseStatus, $caseId);
    mysqli_stmt_execute($setCase);

    header("Location: ../engineer/new_cases.php");
    exit;
}