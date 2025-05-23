<?php
// delete_customer_product.php
header('Content-Type: application/json'); // Indicate JSON response

require_once '../config/database.php'; // **Adjust path as needed** - your database connec$connectionection file

$product_id = $_POST['product_id'] ?? '';

if (empty($product_id)) {
    echo json_encode(['success' => false, 'message' => 'Product ID is missing.']);
    exit;
}

// Prepare SQL DELETE statement
$sql = "DELETE FROM customer_products WHERE id = ?";

$stmt = $connection->prepare($sql);

if ($stmt === false) {
    error_log("Database prepare failed: " . $connection->error);
    echo json_encode(['success' => false, 'message' => 'Database error preparing statement.']);
    $connection->close();
    exit;
}

// Bind parameters
$stmt->bind_param("i", $product_id); // 'i' for integer ID

// Execute the statement
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully!']);
    } else {
         echo json_encode(['success' => false, 'message' => 'No product found with that ID.']);
    }

} else {
    error_log("Database execute failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Database error executing statement: ' . $stmt->error]);
}

$stmt->close();
$connection->close();
?>