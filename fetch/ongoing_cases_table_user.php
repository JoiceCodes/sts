<?php
require_once "../config/database.php";

$getOngoingCases = mysqli_prepare($connection, "SELECT 
c.id,
c.case_number,
c.type,
c.subject,
c.product_group,
c.product,
c.product_version,
c.severity,
c.company,
c.last_modified,
c.datetime_opened,
u.full_name AS contact_name
FROM cases AS c
INNER JOIN users AS u ON c.user_id = u.id
WHERE c.case_status = ? AND c.case_owner = ? AND c.reopen = 0");
$caseStatus = "Waiting in Progress";
mysqli_stmt_bind_param($getOngoingCases, "si", $caseStatus, $_SESSION["user_id"]);
mysqli_stmt_execute($getOngoingCases);
$getOngoingCasesResult = mysqli_stmt_get_result($getOngoingCases);
$ongoingCasesTable = [];
if (mysqli_num_rows($getOngoingCasesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getOngoingCasesResult)) {
        $ongoingCasesTable[] = $row;
    }
}