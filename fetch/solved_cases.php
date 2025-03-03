<?php
require_once "../config/database.php";

$getSolvedCases = mysqli_prepare($connection, "SELECT * FROM cases WHERE case_status = ?");
$caseStatus = "Solved";
mysqli_stmt_bind_param($getSolvedCases, "s", $caseStatus);
mysqli_stmt_execute($getSolvedCases);
$getSolvedCasesResult = mysqli_stmt_get_result($getSolvedCases);

$solvedCasesTable = [];

if (mysqli_num_rows($getSolvedCasesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getSolvedCasesResult)) {
        $solvedCasesTable[] = $row;
    }   
}