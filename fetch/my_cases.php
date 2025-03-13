<?php
require_once "../config/database.php";

$getMyCases  = mysqli_prepare($connection, "SELECT 
c.case_number,
c.type,
c.subject,
c.user_id,
c.product_group,
c.product,
c.product_version,
c.severity,
c.case_status,
c.attachment,
c.case_owner,
c.company,
c.last_modified,
c.datetime_opened,
c.reopen,
u_contact_name.full_name
FROM cases AS c 
LEFT JOIN users AS u_contact_name ON c.user_id = u_contact_name.id
WHERE case_owner = ?");
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