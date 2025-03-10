<?php
require_once "../config/database.php";

$getProducts = mysqli_prepare($connection, "SELECT 
id, 
product_name,
product_category,
product_type,
product_version,
license_type,
serial_number,
license_duration,
created_at
FROM products");
mysqli_stmt_execute($getProducts);
$getProductsResult = mysqli_stmt_get_result($getProducts);
$products = [];
if (mysqli_num_rows($getProductsResult) > 0) {
    while ($row = mysqli_fetch_assoc($getProductsResult)) {
        $row["created_at"] = date("F j, Y", strtotime($row["created_at"]));
        $products[] = $row;
    }
}