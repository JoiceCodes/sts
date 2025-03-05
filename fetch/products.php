<?php
require_once "../config/database.php";

$getProducts = mysqli_prepare($connection, "SELECT * FROM products");
mysqli_stmt_execute($getProducts);
$getProductsResult = mysqli_stmt_get_result($getProducts);
$products = [];
if (mysqli_num_rows($getProductsResult) > 0) {
    while ($row = mysqli_fetch_assoc($getProductsResult)) {
        $row["created_at"] = date("F j, Y", strtotime($row["created_at"]));
        $products[] = $row;
    }
}