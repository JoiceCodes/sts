<?php
session_start();

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the user is logged in and is a 'User'
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "User") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Check if product_id is provided in the GET request
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID provided.']);
    exit();
}

$productId = $_GET['product_id'];
$userFullName = $_SESSION["user_full_name"];
// Get user ID for an *even stronger* security check if needed,
// but we'll stick to filtering by customer_name matching the session name as requested.
// $userId = $_SESSION["user_id"];


// --- Database Connection ---
// Adjust the path to your database connection file
require_once '../config/database.php'; // Assuming your database connection is in db.php

// Check database connection
if ($connection->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $connection->connect_error]);
    exit();
}

// --- Fetch Product Details for the given ID AND the current user ---
// This query selects all details for a specific product ID *only if* it belongs to the logged-in user
$sql = "SELECT * FROM customer_products WHERE id = ? AND customer_name = ?";

$stmt = $connection->prepare($sql);
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $connection->error]);
    $connection->close();
    exit();
}

$stmt->bind_param("is", $productId, $userFullName); // Bind the ID (integer) and user full name (string)
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Product found and belongs to the user, fetch details
    $productDetails = $result->fetch_assoc();
    echo json_encode(['success' => true, 'product' => $productDetails]);
} else {
    // Product not found or does not belong to the user
    echo json_encode(['success' => false, 'message' => 'Product not found or unauthorized.']);
}

$stmt->close();
$connection->close();
?>