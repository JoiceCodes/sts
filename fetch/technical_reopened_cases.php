<?php
require_once "../config/database.php";

$getReopenedCases = mysqli_prepare($connection, "SELECT * FROM cases WHERE case_status = ? AND reopen > 0");
$caseStatus = "Waiting in Progress";
mysqli_stmt_bind_param($getReopenedCases, "s", $caseStatus);
mysqli_stmt_execute($getReopenedCases);
$getReopenedCasesResult = mysqli_stmt_get_result($getReopenedCases);

$reopenedCasesTable = [];

if (mysqli_num_rows($getReopenedCasesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getReopenedCasesResult)) {
        $row["last_modified"] = date("F j, Y h:i:s A", strtotime($row["last_modified"]));
        $row["datetime_opened"] = date("F j, Y h:i:s A", strtotime($row["datetime_opened"]));
        $reopenedCasesTable[] = $row;
    }   
}