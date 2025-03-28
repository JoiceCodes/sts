<?php
// fetch_engineers.php
header('Content-Type: application/json');
require_once '../config/database2.php'; // Adjust path to your database connection file ($pdo)

// Ensure you have a PDO connection object named $pdo
if (!isset($pdo) || !($pdo instanceof PDO)) {
     // Log error in production
     error_log("fetch_engineers.php: Database connection object (\$pdo) is not a valid PDO instance.");
     http_response_code(500); // Internal Server Error
     echo json_encode(['error' => 'Database configuration error.']);
     exit;
}


try {
    // Fetch users with the role 'engineer' who are active
    // Adjust 'role' and 'account_status' column names if different in your 'users' table
    $sql = "SELECT id, full_name FROM users WHERE role = :role AND account_status = :status ORDER BY full_name ASC";
    $stmt = $pdo->prepare($sql);
    $role = 'Engineer'; // The role you want to fetch
    $status = 'Active'; // Assuming 'Active' means they can receive cases
    $stmt->bindParam(':role', $role, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->execute();

    $engineers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($engineers); // Output the array of engineers

} catch (PDOException $e) {
    // Log error instead of echoing sensitive details in production
    error_log("Database error fetching engineers: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    // Provide generic error for client, detailed error in logs
    echo json_encode(['error' => 'Database error occurred while fetching engineers.']);
    // echo json_encode(['error' => 'Database error occurred.', 'details' => $e->getMessage()]); // For debugging only
} catch (Exception $e) {
    error_log("Unexpected error fetching engineers: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'An unexpected error occurred while fetching engineers.']);
}

// No need to explicitly close PDO connection usually
// $pdo = null;
?>