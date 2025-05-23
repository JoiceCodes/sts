<?php
// session_start();
// require_once "../config/database.php";

// if ($_SERVER["REQUEST_METHOD"] === "POST") {
//     $name = $_POST["name"];
//     $category = $_POST["category"];
//     $type = $_POST["type"];
//     $version = "v" . $_POST["version"];
//     $licenseType = $_POST["license_type"];
//     $serialNumber = $_POST["serial_number"];
//     $licenseDuration = $_POST["license_duration"];

//     $supportedPlatforms = implode(", ", $_POST["supported_platform"]);

//     $setProduct = mysqli_prepare($connection, "INSERT INTO products (product_name, product_category, product_type, product_version, supported_platforms, license_type, serial_number, license_duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
//     mysqli_stmt_bind_param($setProduct, "ssssssss", $name, $category, $type, $version, $supportedPlatforms, $licenseType, $serialNumber, $licenseDuration);
//     mysqli_stmt_execute($setProduct);

//     $currentId = mysqli_stmt_insert_id($setProduct);

//     header("Location: ../administrator/products.php");
//     exit();
// }

session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect data from the form (only for columns in the products table)
    // $company = $_POST["company"];
    $productGroup = $_POST["product_group"];
    $productType = $_POST["product_type"];
    $productVersion = $_POST["product_version"];
    $licenseType = $_POST["license_type"];
    $serialNumber = $_POST["serial_number"];
    $status = $_POST["status"];

    $createdAt = date('Y-m-d H:i:s');

    $setProduct = mysqli_prepare($connection, "INSERT INTO products (product_group, product_type, product_version, license_type, serial_number, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Bind parameters (s = string) - matches the columns in the INSERT statement
    mysqli_stmt_bind_param($setProduct, "sssssss",  $productGroup, $productType, $productVersion, $licenseType, $serialNumber, $status, $createdAt);

    // Execute the statement
    mysqli_stmt_execute($setProduct);

    // Check for errors (optional but recommended)
    if (mysqli_stmt_error($setProduct)) {
        error_log("Error adding product: " . mysqli_stmt_error($setProduct));
        // Handle the error, e.g., show an error message to the user
        $_SESSION['error_message'] = "Error adding product: " . mysqli_stmt_error($setProduct);
        header("Location: ../administrator/products.php"); // Redirect back with error
        exit();
    }


    $currentId = mysqli_stmt_insert_id($setProduct);

    // Close the statement
    mysqli_stmt_close($setProduct);

    // Redirect after successful insertion
    header("Location: ../administrator/products.php");
    exit();
} else {
    // If accessed via GET or other methods, redirect or show an error
    header("Location: ../administrator/products.php");
    exit();
}
