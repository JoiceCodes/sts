<?php
date_default_timezone_set("Asia/Manila");

$host = "localhost";
$serverUsername = "root";
$serverPassword = "";
$databaseName = "sts_db";

$connection = mysqli_connect($host, $serverUsername, $serverPassword, $databaseName);

if (!$connection) {
    die("Connection error");
}