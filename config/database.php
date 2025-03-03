<?php
$host = "localhost";
$serverUsername = "root";
$serverPassword = "";
$databaseName = "sts_db";

$connection = mysqli_connect($host, $serverUsername, $serverPassword, $databaseName);

if (!$connection) {
    die("Connection error");
}