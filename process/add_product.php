<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $category = $_POST["category"];
    $type = $_POST["type"];
    $version = $_POST["version"];
    $licenseType = $_POST["license_type"];
    $serialNumber = $_POST["serial_number"];
    $licenseDuration = $_POST["license_duration"];

    $supportedPlatforms = $_POST["supported_platform"];

    $setProduct = mysqli_prepare($connection, "INSERT INTO products (product_name, product_category, product_type, product_version, license_type, serial_number, license_duration) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($setProduct, "sssssss", $name, $category, $type, $version, $licenseType, $serialNumber, $licenseDuration);
    mysqli_stmt_execute($setProduct);

    $currentId = mysqli_stmt_insert_id($setProduct);

    foreach ($supportedPlatforms as $supportedPlatform) {
        $setPlatform = mysqli_prepare($connection, "INSERT INTO platforms (product_id, platform) VALUES (?, ?)");
        mysqli_stmt_bind_param($setPlatform, "is", $currentId, $supportedPlatform);
        mysqli_stmt_execute($setPlatform);
    }

    header("Location: ../technical_head/products.php");
    exit();
}