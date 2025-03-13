<?php
require_once "../config/database.php";

$getTotalSolvedCases = mysqli_prepare($connection, "SELECT COUNT(*) AS total_solved_cases FROM cases WHERE case_status = ? AND case_owner = ?");

$caseStatus = "Solved";
mysqli_stmt_bind_param($getTotalSolvedCases, "si", $caseStatus, $_SESSION["user_id"]);
mysqli_stmt_execute($getTotalSolvedCases);
$getTotalSolvedCasesResult = mysqli_stmt_get_result($getTotalSolvedCases);
$totalSolvedCases = 0;
if (mysqli_num_rows($getTotalSolvedCasesResult) > 0) {
    $row = mysqli_fetch_assoc($getTotalSolvedCasesResult);
    $totalSolvedCases = $row["total_solved_cases"];
}