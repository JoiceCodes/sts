<?php
session_start();

// Include your database connection file
// Assuming db_connect.php handles PDO connection and error handling
require_once "../config/database2.php"; // Adjust path as needed

// Ensure the request is a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Redirect back to the companies page with an error
    header("Location: ../administrator/all_companies.php?addError=" . urlencode("Invalid request method."));
    exit();
}

// Get and sanitize the company name from the form
$companyName = trim($_POST["company_name"]);

// Validate the company name
if (empty($companyName)) {
    // Redirect back with an error message
    header("Location: ../administrator/all_companies.php?addError=" . urlencode("Company Name cannot be empty."));
    exit();
}

// Basic validation: check for minimum length or invalid characters if necessary
// Example: minimum length
if (strlen($companyName) < 2) {
     header("Location: ../administrator/all_companies.php?addError=" . urlencode("Company Name must be at least 2 characters long."));
     exit();
}

// Database insertion
try {
    // Check if the company name already exists (optional, but good practice)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM companies WHERE name = :name");
    $stmtCheck->bindParam(':name', $companyName);
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn() > 0) {
         header("Location: ../administrator/all_companies.php?addError=" . urlencode("Company '" . htmlspecialchars($companyName) . "' already exists."));
         exit();
    }


    // Prepare the INSERT statement
    $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (:name)");

    // Bind the parameter
    $stmt->bindParam(':name', $companyName);

    // Execute the statement
    if ($stmt->execute()) {
        // Success
        header("Location: ../administrator/all_companies.php?addSuccess=1");
        exit();
    } else {
        // Database error on execution
        error_log("Database error adding company: " . implode(":", $stmt->errorInfo())); // Log the error
        header("Location: ../administrator/all_companies.php?addError=" . urlencode("Database error occurred."));
        exit();
    }

} catch (PDOException $e) {
    // PDO Exception error
    error_log("PDO Exception adding company: " . $e->getMessage()); // Log the error
    header("Location: ../administrator/all_companies.php?addError=" . urlencode("An unexpected database error occurred."));
    exit();
} catch (Exception $e) {
     // Other potential errors
    error_log("General Exception adding company: " . $e->getMessage()); // Log the error
    header("Location: ../administrator/all_companies.php?addError=" . urlencode("An unexpected error occurred."));
    exit();
}

?>