<?php
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseId = $_POST["case_id"];
    $severity = $_POST["severity"];

    $setSeverity = mysqli_prepare($connection, "UPDATE cases SET severity = ? WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($setSeverity, "si", $severity, $caseId);
    mysqli_stmt_execute($setSeverity);
    header("Location: ../engineer/ongoing_cases.php?escalate_severity=1");
    exit;
}