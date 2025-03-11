<?php 
require_once "../config/database.php";

$getProductCategories = mysqli_prepare($connection, "SELECT * FROM product_categories");
mysqli_stmt_execute($getProductCategories);
$getProductCategoriesResult = mysqli_stmt_get_result($getProductCategories);

$productCategories = [];
if (mysqli_num_rows($getProductCategoriesResult) > 0) {
    while ($row = mysqli_fetch_assoc($getProductCategoriesResult)) {
        $productCategories[] = $row;
    }
}