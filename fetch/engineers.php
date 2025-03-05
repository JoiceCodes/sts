<?php
require_once "../config/database.php";

$getEngineers = mysqli_prepare($connection, "SELECT * FROM users WHERE role = ?");
$role = "Engineer";
mysqli_stmt_bind_param($getEngineers, "s", $role);
mysqli_stmt_execute($getEngineers);
$getEngineersResult = mysqli_stmt_get_result($getEngineers);

$engineers = [];
if (mysqli_num_rows($getEngineersResult) > 0) {
    while ($row = mysqli_fetch_assoc($getEngineersResult)) {
        $row["created_at"] = date("F j, Y h:i A", strtotime($row["created_at"]));
        $engineers[] = $row;
    }
}