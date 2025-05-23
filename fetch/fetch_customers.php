<?php
// fetch_customers.php - Modified for Select2

header('Content-Type: application/json'); // Set the header to indicate JSON response

require_once '../config/database.php'; // **Adjust path as needed** - your database conn$connectionection file

// Select2 sends the search term in the 'q' parameter by default
$term = $_GET['q'] ?? '';

if (empty($term)) {
    // Select2 might still make a request with an empty term,
    // return an empty results array if no term is provided.
    // Some configurations might require returning initial options here.
    echo json_encode(['results' => []]);
    exit;
}

// Use prepared statement to prevent SQL injection
// Query the full_name from users where role is 'User' and full_name matches the term
$sql = "SELECT full_name FROM users WHERE role = 'User' AND full_name LIKE ? LIMIT 10"; // Limit results
$stmt = $connection->prepare($sql);

if ($stmt === false) {
    // Handle potential prepare errors
    error_log("Database prepare failed: " . $connection->error);
     // Return Select2-compatible error structure or empty results
    echo json_encode(['results' => [], 'error' => 'Database error']);
    exit;
}

$searchTerm = '%' . $term . '%'; // Add wildcards for LIKE search
$stmt->bind_param("s", $searchTerm);

$stmt->execute();
$result = $stmt->get_result();

$customers = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Select2 expects an array of objects with 'id' and 'text'
        // Use full_name for both id and text for simplicity based on your table structure
        $customers[] = ['id' => $row['full_name'], 'text' => $row['full_name']];
    }
} else {
    error_log("Database execute failed: " . $stmt->error);
    // Return Select2-compatible error structure or empty results on execution failure
    echo json_encode(['results' => [], 'error' => 'Execution error']);
     $stmt->close();
     $connection->close();
    exit;
}

$stmt->close();
$connection->close();

// Select2 expects the results array to be inside a 'results' key
echo json_encode(['results' => $customers]);
?>