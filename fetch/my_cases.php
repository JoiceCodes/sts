<?php
require_once "../config/database.php";

$getMyCases  = mysqli_prepare($connection, "SELECT * FROM cases WHERE case_owner = ?");
mysqli_stmt_bind_param($getMyCases, "s", $_SESSION["user_id"]);
mysqli_stmt_execute($getMyCases);
$getMyCasesResult = mysqli_stmt_get_result($getMyCases);
$myCases = [];
if (mysqli_num_rows($getMyCasesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getMyCasesResult)) {
        $row["last_modified"] = date("F j, Y h:i A", strtotime($row["last_modified"]));
        $row["datetime_opened"] = date("F j, Y h:i:s A", strtotime($row["datetime_opened"]));
        $myCases[] = $row;
    }
}