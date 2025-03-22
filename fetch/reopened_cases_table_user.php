<?php
require_once "../config/database.php";

$getReopenedCases = mysqli_prepare($connection, "SELECT
c.id,
c.case_number,
c.type,
c.subject,
c.product_group,
c.product,
c.product_version,
c.severity,
c.company,
c.reopen,
u.full_name AS contact_name
FROM cases AS c
INNER JOIN users AS u ON c.user_id = u.id
WHERE c.case_status = ? AND c.case_owner = ? AND c.reopen > 0");
$caseStatus = "Waiting in Progress";
mysqli_stmt_bind_param($getReopenedCases, "si", $caseStatus, $_SESSION["user_id"]);
mysqli_stmt_execute($getReopenedCases);
$getReopenedCasesResult = mysqli_stmt_get_result($getReopenedCases);
$reopenedCasesTable = [];
if (mysqli_num_rows($getReopenedCasesResult) > 0) {
    $row = mysqli_fetch_assoc($getReopenedCasesResult);
    $reopenedCasesTable[] = $row;
}