<?php
session_start();

header('Content-Type: application/json');

// Include database connection file using conn$connection
require_once '../config/database.php';

// Check if the conn$connection connection is successful
// $connection is created in database_conn$connection.php
if ($connection->connect_error) {
    // Log the specific connection error
    error_log("conn$connection Connection Error in add_new_customer_product.php: " . $connection->connect_error, 0);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed. Please try again later."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get values directly from POST - these should be the names now from the updated JS
    $customer_name = filter_var($_POST['customer_name'] ?? '', FILTER_UNSAFE_RAW); // Getting name (string)
    $serial_number = filter_var($_POST['serial_number'] ?? '', FILTER_UNSAFE_RAW);
    $company = filter_var($_POST['company'] ?? '', FILTER_UNSAFE_RAW); // Getting company name (string)

    // Get other fields
    $product_group = filter_var($_POST['product_group'] ?? '', FILTER_UNSAFE_RAW);
    $product_type = filter_var($_POST['product_type'] ?? '', FILTER_UNSAFE_RAW);
    $product_version = filter_var($_POST['product_version'] ?? '', FILTER_UNSAFE_RAW);
    $license_type = filter_var($_POST['license_type'] ?? '', FILTER_UNSAFE_RAW);
    $license_duration = filter_var($_POST['license_duration'] ?? '', FILTER_UNSAFE_RAW);

    // Dates and Datetime
    $license_date_start = filter_var($_POST['license_date_start'] ?? '', FILTER_SANITIZE_STRING); // YYYY-MM-DD
    $end_license_date = filter_var($_POST['end_license_date'] ?? '', FILTER_SANITIZE_STRING); // YYYY-MM-DD
    $created_at = filter_var($_POST['created_at'] ?? '', FILTER_SANITIZE_STRING); // YYYY-MM-DDTHH:MM

    // Basic validation for required fields - using names now
    if (empty($customer_name) || empty($serial_number) || empty($company) || empty($license_date_start) || empty($end_license_date) || empty($created_at)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Required fields are missing (Customer Name, Serial Number, Company, License Start Date, License End Date, Created At)."]);
        exit;
    }

    // Adjust created_at format for MySQL DATETIME (YYYY-MM-DD HH:MM:SS)
    $created_at_db = str_replace('T', ' ', $created_at);
    if (strlen($created_at_db) == 16) { // YYYY-MM-DD HH:MM
         $created_at_db .= ':00';
     }

    // --- conn$connection Prepared Statement ---
    // SQL query uses the correct column names from your table schema
    $sql = "INSERT INTO customer_products (customer_name, serial_number, company, product_group, product_type, product_version, license_type, license_duration, license_date_start, end_license_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $connection->prepare($sql);

    if ($stmt === false) {
        // Log the specific prepare error
        error_log("conn$connection Prepare Error in add_new_customer_product.php: " . $connection->error, 0);
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error preparing statement. Please contact support."]); // Generic error for user
        exit;
    }

    // Bind parameters
    // The types string 'sssssssssss' corresponds to:
    // s: string for all 11 parameters, as we are inserting names and strings
    $bind_result = $stmt->bind_param('sssssssssss',
        $customer_name,
        $serial_number,
        $company,
        $product_group,
        $product_type,
        $product_version,
        $license_type,
        $license_duration,
        $license_date_start, // This should be YYYY-MM-DD format, which MySQL handles as string
        $end_license_date,   // This should be YYYY-MM-DD format
        $created_at_db       // This is YYYY-MM-DD HH:MM:SS format
    );

     if ($bind_result === false) {
         // Log the specific bind_param error
         error_log("conn$connection Bind_Param Error in add_new_customer_product.php: " . $stmt->error, 0);
         http_response_code(500);
         echo json_encode(["success" => false, "message" => "Database error binding parameters. Please contact support."]); // Generic error
         $stmt->close();
         exit;
    }

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Product added successfully!"]);
    } else {
        // Log the specific execute error
        error_log("conn$connection Execute Error in add_new_customer_product.php: " . $stmt->error, 0);
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error adding product to the database. Please try again."]); // Generic error
    }

    // Close statement
    $stmt->close();

} else {
    // Method not allowed
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
}

// Close database connection (optional, PHP closes it automatically at the end of script execution)
// $connection->close();
?>