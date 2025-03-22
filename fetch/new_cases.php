<?php
require_once "../config/database.php";

$getNewCases = mysqli_prepare($connection, "SELECT 
c.id,
c.case_number,
c.type,
c.subject,
c.product_group,
c.product,
c.product_version,
c.severity,
c.case_status,
c.attachment,
c.company,
c.last_modified,
c.datetime_opened,
c.reopen,
u.full_name AS case_owner

FROM cases AS c 
LEFT JOIN users AS u ON c.case_owner = u.id
WHERE case_status = ?");
$caseStatus = "New";
mysqli_stmt_bind_param($getNewCases, "s", $caseStatus);
mysqli_stmt_execute($getNewCases);
$getNewCasesResult = mysqli_stmt_get_result($getNewCases);

$newCasesTable = [];

if (mysqli_num_rows($getNewCasesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getNewCasesResult)) {
        $newCasesTable[] = $row;
    }   
}