<?php
require_once "../config/database.php";

$getTotalNewCases = mysqli_prepare($connection, "SELECT COUNT(*) AS total_new_cases FROM cases WHERE case_status = ? AND case_owner = ?");

$caseStatus = "New";
mysqli_stmt_bind_param($getTotalNewCases, "si", $caseStatus, $_SESSION["user_id"]);
mysqli_stmt_execute($getTotalNewCases);
$getTotalNewCasesResult = mysqli_stmt_get_result($getTotalNewCases);
$totalNewCases = 0;
if (mysqli_num_rows($getTotalNewCasesResult) > 0) {
    $row = mysqli_fetch_assoc($getTotalNewCasesResult);
    $totalNewCases = $row["total_new_cases"];
}