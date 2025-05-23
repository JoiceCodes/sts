<?php
// fetch_customer_products.php

// !! IMPORTANT !!
// Adjust the path below to point to your actual database conn$connectionection file
require_once '../config/database.php';

$customerProductsTable = []; // Initialize an empty array to hold the fetched data

// Prepare the SQL query to select all columns from the customer_products table
// Ordering by 'created_at' DESC will show the newest products first in the table
$sql = "SELECT * FROM customer_products ORDER BY created_at DESC";

// Execute the query
$result = $connection->query($sql);

// Check if the query was successful
if ($result) {
    // Check if there are any rows returned
    if ($result->num_rows > 0) {
        // Loop through the result set and add each row (as an associative array)
        // to the $customerProductsTable array
        while ($row = $result->fetch_assoc()) {
            $customerProductsTable[] = $row;
        }
    }
    // If num_rows is 0, the loop won't run, and $customerProductsTable remains an empty array,
    // which is the correct behavior for no results.
} else {
    // If the query failed, log the error.
    // In a production environment, you might want a more sophisticated error handling system.
    error_log("Error fetching customer products: " . $connection->error);
    // Optionally, you could populate the array with an error indicator or message,
    // but for populating an HTML table, an empty array or logging the error is often sufficient.
}

// Close the database conn$connectionection

// The $customerProductsTable array is now populated with the data (or is empty)
// and is ready to be used when this file is included in purchased_products.php
?>