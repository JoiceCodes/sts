<?php
date_default_timezone_set("Asia/Manila");

// $host = "localhost";
// $serverUsername = "root";
// $serverPassword = "";
// $databaseName = "sts_db";

$host = "localhost";
$serverUsername = "u714551035_sts";
$serverPassword = "6&6MCdMdxW>";
$databaseName = "u714551035_sts_db";

$connection = mysqli_connect($host, $serverUsername, $serverPassword, $databaseName);

if (!$connection) {
    die("Connection error");
}