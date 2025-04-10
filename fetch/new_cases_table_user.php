<?php
require_once "../config/database.php";

$getNewCases = mysqli_prepare($connection, "SELECT * FROM cases WHERE case_status = ? AND case_owner = ?");
$caseStatus = "New";
mysqli_stmt_bind_param($getNewCases, "si", $caseStatus, $_SESSION["user_id"]);
mysqli_stmt_execute($getNewCases);
$getNewCasesResult = mysqli_stmt_get_result($getNewCases);
$newCasesTable = [];
if (mysqli_num_rows($getNewCasesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getNewCasesResult)) {
        $newCasesTable[] = $row;
    }
}