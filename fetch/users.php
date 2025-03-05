<?php
require_once "../config/database.php";

$getUsers = mysqli_prepare($connection, "SELECT * FROM users WHERE role = ?");
$role = "User";
mysqli_stmt_bind_param($getUsers, "s", $role);
mysqli_stmt_execute($getUsers);
$getUsersResult = mysqli_stmt_get_result($getUsers);

$users = [];
if (mysqli_num_rows($getUsersResult) > 0) {
    while ($row = mysqli_fetch_assoc($getUsersResult)) {
        $row["created_at"] = date("F j, Y h:i A", strtotime($row["created_at"]));
        $users[] = $row;
    }
}