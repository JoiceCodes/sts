<?php

include_once '../config/database2.php'; // Include the database configuration file

// Set options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch rows as associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation for better performance and safety
];

// Initialize the $companies array
$companies = [];
$errorMessage = ''; // Variable to store potential database errors

try {

    $query = "SELECT DISTINCT name FROM companies ORDER BY name ASC";

    // Execute the query
    $stmt = $pdo->query($query);

    // Fetch all the distinct company names into an array
    // PDO::FETCH_COLUMN fetches the values of a single column from all rows
    $companies = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

} catch (\PDOException $e) {
    // Handle database connection or query errors
    // Log the error for debugging (replace with your preferred logging method)
    error_log("Database Error fetching companies: " . $e->getMessage());

    // Set an error message for the user (optional)
    $errorMessage = "An error occurred while fetching companies.";

    // Ensure $companies is empty in case of error
    $companies = [];
}

// The $companies array is now available to the main PHP file
// It contains a simple indexed array of company names like ['Company A', 'Company B', ...]
?>