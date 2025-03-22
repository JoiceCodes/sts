<?php
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $engineerId = $_POST["engineer_id"];
    $rating = $_POST["rating"];
    
    $setRating = mysqli_prepare($connection, "INSERT INTO ratings (engineer_id, star) VALUES (?, ?)");
    mysqli_stmt_bind_param($setRating, "ii", $engineerId, $rating);
    mysqli_stmt_execute($setRating);

    header("Location: ../user/home.php");
    exit;
}