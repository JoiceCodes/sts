<?php
require_once "../config/database.php";

$getReopenedCases = mysqli_prepare($connection, "SELECT * FROM cases WHERE reopen > 0");
mysqli_stmt_execute($getReopenedCases);
$getReopenedCasesResult = mysqli_stmt_get_result($getReopenedCases);

$reopenedCasesTable = [];

if (mysqli_num_rows($getReopenedCasesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getReopenedCasesResult)) {
        $reopenedCasesTable[] = $row;
    }   
}