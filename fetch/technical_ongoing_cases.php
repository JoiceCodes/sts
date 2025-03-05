<?php
require_once "../config/database.php";

$getOngoingCases = mysqli_prepare($connection, "SELECT * FROM cases WHERE case_status = ?");
$caseStatus = "Waiting in Progress";
mysqli_stmt_bind_param($getOngoingCases, "s", $caseStatus);
mysqli_stmt_execute($getOngoingCases);
$getOngoingCasesResult = mysqli_stmt_get_result($getOngoingCases);

$ongoingCasesTable = [];

if (mysqli_num_rows($getOngoingCasesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getOngoingCasesResult)) {
        $ongoingCasesTable[] = $row;
    }   
}