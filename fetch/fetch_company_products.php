<?php
// This script is called via AJAX from the main page
// It fetches products for a specific company from the database

// Include the database connection file
// This will create the $pdo object if connection is successful,
// or stop the script if connection fails (based on database2.php logic)
require_once "../config/database2.php";

$responseHtml = ''; // Variable to build the HTML response

// Check if the PDO connection object ($pdo) was successfully created by database2.php
// database2.php will die() on connection failure, so if we reach here, $pdo exists.

// --- Get and Sanitize Company Name from AJAX request ---
// Get the company name sent from the JavaScript via $_GET
$companyName = isset($_GET['company']) ? trim($_GET['company']) : '';

// Basic validation for the company name
if (empty($companyName)) {
    // If no company name is provided, return an error message
    $responseHtml = '<div class="col-12 text-danger">Error: No company name provided.</div>';
} else {
    try {
        // --- Database Query to Fetch Products for the Company ---
        // Use the $pdo object from database2.php
        // Prepare the SQL query using a placeholder (:companyName) for safety
        $query = "SELECT * FROM customer_products WHERE company = :companyName";
        $stmt = $pdo->prepare($query);

        // Bind the company name parameter to the placeholder
        $stmt->bindParam(':companyName', $companyName, PDO::PARAM_STR);

        // Execute the prepared query
        $stmt->execute();

        // Fetch all the products that match the company name
        $products = $stmt->fetchAll();

        // --- Generate HTML Cards for the Products ---
        if (!empty($products)) {
            // If products are found, loop through them and build the HTML for cards
            foreach ($products as $product) {
                // Use Bootstrap grid classes for layout within the modal
                // Adjust col- classes (e.g., col-sm-6, col-md-4, col-lg-3) based on how many cards per row you want
                $responseHtml .= '<div class="col-sm-6 col-md-4 col-lg-3">';
                // Use card classes for each product
                $responseHtml .= '<div class="card bg-light product-modal-card">'; // Added a custom class for modal card styling
                $responseHtml .= '<div class="card-body">';
                // Display product details
                // Use htmlspecialchars() to prevent XSS when displaying database content
                $responseHtml .= '<h6 class="card-title">' . htmlspecialchars($product['customer_name']) . '</h6>';
                $responseHtml .= '<p class="card-text mb-1"><strong>Type:</strong> ' . htmlspecialchars($product['product_type']) . '</p>';
                $responseHtml .= '<p class="card-text mb-1"><strong>Version:</strong> ' . htmlspecialchars($product['product_version']) . '</p>';
                $responseHtml .= '<p class="card-text mb-1"><strong>License:</strong> ' . htmlspecialchars($product['license_type']) . '</p>';
                // Display Serial Number (consider truncation or handling very long strings)
                $serialDisplay = htmlspecialchars($product['serial_number']);
                // Example: Truncate if too long
                // if (strlen($serialDisplay) > 30) {
                //     $serialDisplay = substr($serialDisplay, 0, 27) . '...';
                // }
                $responseHtml .= '<p class="card-text mb-1"><strong>Serial:</strong> ' . $serialDisplay . '</p>';
                $responseHtml .= '<p class="card-text mb-1"><strong>Duration:</strong> ' . htmlspecialchars($product['license_duration']) . '</p>';
                $responseHtml .= '<p class="card-text mb-1"><strong>Start Date:</strong> ' . htmlspecialchars($product['license_date_start']) . '</p>';
                $responseHtml .= '<p class="card-text mb-1"><strong>End Date:</strong> ' . htmlspecialchars($product['end_license_date']) . '</p>';

                $responseHtml .= '</div>'; // .card-body
                $responseHtml .= '</div>'; // .card
                $responseHtml .= '</div>'; // .col
            }
        } else {
            // If no products are found for the given company
            $responseHtml = '<div class="col-12 text-center">No products found for this company.</div>';
        }

    } catch (\PDOException $e) {
        // --- Query Error Handling ---
        // This catches errors specifically during the *query* execution
        error_log("Database Query Error fetching company products: " . $e->getMessage());
        $responseHtml = '<div class="col-12 text-danger">A database error occurred while trying to load products.</div>';
    }
}

// Note: $pdo connection is automatically closed when the script ends or goes out of scope.

// --- Output the generated HTML ---
// Set the content type header to ensure the browser/AJAX handles it correctly
header('Content-Type: text/html');
// Echo the HTML string back to the AJAX request
echo $responseHtml;

?>