<?php
require_once "../config/database.php";

$getTotalOngoingCases = mysqli_prepare($connection, "SELECT COUNT(*) AS total_ongoing_cases FROM cases WHERE case_status = ? AND reopen = 0 AND user_id = ?");

$caseStatus = "Waiting in Progress";
mysqli_stmt_bind_param($getTotalOngoingCases, "si", $caseStatus, $_SESSION["user_id"]);
mysqli_stmt_execute($getTotalOngoingCases);
$getTotalOngoingCasesResult = mysqli_stmt_get_result($getTotalOngoingCases);
$totalOngoingCases = 0;
if (mysqli_num_rows($getTotalOngoingCasesResult) > 0) {
    $row = mysqli_fetch_assoc($getTotalOngoingCasesResult);
    $totalOngoingCases = $row["total_ongoing_cases"];
}