<?php
require_once "../config/database.php";

$getSolvedCases = mysqli_prepare($connection, "SELECT * FROM cases WHERE case_status = ? AND user_id = ?");
$caseStatus = "Solved";
mysqli_stmt_bind_param($getSolvedCases, "si", $caseStatus, $_SESSION["user_id"]);
mysqli_stmt_execute($getSolvedCases);
$getSolvedCasesResult = mysqli_stmt_get_result($getSolvedCases);

$solvedCasesTable = [];

if (mysqli_num_rows($getSolvedCasesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getSolvedCasesResult)) {
        $row["last_modified"] = date("F j, Y h:i:s", strtotime($row["last_modified"]));
        $row["datetime_opened"] = date("F j, Y h:i:s", strtotime($row["datetime_opened"]));
        $row["reopen"] = $row["reopen"] . " time(s)";
        $solvedCasesTable[] = $row;
    }   
}