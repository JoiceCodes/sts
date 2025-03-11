<?php
header("Content-Type: application/json");
require_once "../config/database.php";

if (isset($_POST['category_id'])) {
    $categoryId = intval($_POST['category_id']); // Sanitize input

    $query = "SELECT id, product_type FROM product_types WHERE product_category_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    $productTypes = [];
    while ($row = $result->fetch_assoc()) {
        $productTypes[] = $row;
    }

    echo json_encode($productTypes); // Return as JSON
}
