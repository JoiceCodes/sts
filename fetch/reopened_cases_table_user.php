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
// Make sure $_SESSION["user_id"] is correctly set and holds the intended user ID (e.g., 3 based on your image)
mysqli_stmt_bind_param($getReopenedCases, "si", $caseStatus, $_SESSION["user_id"]);
mysqli_stmt_execute($getReopenedCases);
$getReopenedCasesResult = mysqli_stmt_get_result($getReopenedCases);
$reopenedCasesTable = [];

// Use a while loop to fetch ALL matching rows
while ($row = mysqli_fetch_assoc($getReopenedCasesResult)) {
    $reopenedCasesTable[] = $row;
}

// Now $reopenedCasesTable will contain all the reopened cases
// You can then proceed to display them.

// Don't forget to close the statement
mysqli_stmt_close($getReopenedCases);
?>