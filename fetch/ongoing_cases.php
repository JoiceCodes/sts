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
c.case_status,
c.attachment,
c.company,
c.last_modified,
c.datetime_opened,
c.reopen,
u.full_name AS case_owner
FROM cases AS c
LEFT JOIN users AS u ON c.case_owner = u.id
WHERE case_status = ? AND reopen = 0 AND user_id = ?");
$caseStatus = "Waiting in Progress";
mysqli_stmt_bind_param($getOngoingCases, "si", $caseStatus, $_SESSION["user_id"]);
mysqli_stmt_execute($getOngoingCases);
$getOngoingCasesResult = mysqli_stmt_get_result($getOngoingCases);

$ongoingCasesTable = [];

if (mysqli_num_rows($getOngoingCasesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getOngoingCasesResult)) {
        $row["last_modified"] = date("F j, Y h:i:s A", strtotime($row["last_modified"]));
        $row["datetime_opened"] = date("F j, Y h:i:s A", strtotime($row["datetime_opened"]));
        $ongoingCasesTable[] = $row;
    }
}
