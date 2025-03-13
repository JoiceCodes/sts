<?php
require_once "../config/database.php";

$getTotalReopenedCases = mysqli_prepare($connection, "SELECT COUNT(*) AS total_reopened_cases FROM cases WHERE case_status = ? AND user_id = ? AND reopen > 0");
$caseStatus = "Waiting in Progress";
mysqli_stmt_bind_param($getTotalReopenedCases, "si", $caseStatus, $_SESSION["user_id"]);
mysqli_stmt_execute($getTotalReopenedCases);
$getTotalReopenedCasesResult = mysqli_stmt_get_result($getTotalReopenedCases);
$totalReopenedCases = 0;
if (mysqli_num_rows($getTotalReopenedCasesResult) > 0) {
    $row = mysqli_fetch_assoc($getTotalReopenedCasesResult);
    $totalReopenedCases = $row["total_reopened_cases"];
}