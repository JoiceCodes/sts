<?php 
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productId = $_POST["product_id"];
    $action = $_POST["action"];

    switch ($action) {
        case "activate":
            $status = "Active";
            break;
        case "deactivate":
            $status = "Deactivated";
            break;
    }

    $setProductStatus = mysqli_prepare($connection, "UPDATE products SET status = ? WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($setProductStatus, "si", $status, $productId);
    mysqli_stmt_execute($setProductStatus);
    header("Location: ../technical_head/products.php?success=1");
    exit;
}