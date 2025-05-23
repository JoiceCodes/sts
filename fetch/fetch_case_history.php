<?php
// ../fetch/fetch_case_history.php

// Start session if you need to check user authentication
session_start();

// Set the correct header for JSON output
header('Content-Type: application/json');

// Include your database configuration file
require_once "../config/database.php"; // Adjust path if needed

// Initialize the response array
$response = ['success' => false, 'message' => 'An error occurred fetching history.', 'history_events' => []];

// --- Optional: Authentication Check ---
// Ensure the user is logged in before allowing access to history data
if (!isset($_SESSION["user_id"])) {
    $response['message'] = 'Authentication required to view history.';
    // Send response and stop script execution
    echo json_encode($response);
    exit;
}

// --- Get and Validate Input ---
// Fetch 'case_id' from the GET parameters sent by the AJAX request
$caseId = filter_input(INPUT_GET, 'case_id', FILTER_VALIDATE_INT);

// Check if case_id is valid
if (!$caseId || $caseId <= 0) {
    $response['message'] = 'Invalid or missing Case ID.';
    echo json_encode($response);
    exit;
}

// --- Database Interaction ---
// Check if the database connection exists (defined in database.php)
if (!isset($connection) || !$connection) {
     // Log error server-side for admin review
     error_log("[fetch_case_history] Database connection is not available.");
     $response['message'] = "Database connection error."; // User-friendly message
     echo json_encode($response);
     exit;
}

try {
    // Prepare the SQL query
    // Select columns from case_history and join with users table to get user's name
    // *** ADJUST 'users.full_name' if your user name column has a different name ***
    $sql = "SELECT
                ch.id AS history_id, -- Select specific columns you need
                ch.case_id,
                ch.user_id,
                ch.action_type,
                ch.details,
                ch.old_value,
                ch.new_value,
                ch.timestamp,
                u.full_name AS user_name -- Get the user's full name from the users table
            FROM
                case_history ch -- Alias the table for clarity
            LEFT JOIN
                users u ON ch.user_id = u.id -- JOIN based on user_id
            WHERE
                ch.case_id = ? -- Filter by the requested case ID
            ORDER BY
                ch.timestamp DESC"; // Order by timestamp descending (newest first)

    $stmt = mysqli_prepare($connection, $sql);

    if ($stmt) {
        // Bind the case_id parameter (integer type 'i')
        mysqli_stmt_bind_param($stmt, "i", $caseId);

        // Execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $historyEvents = []; // Initialize array to hold history events

            // Fetch results row by row
            while ($row = mysqli_fetch_assoc($result)) {
                // Sanitize user_name: If the JOIN didn't find a name (e.g., NULL user_id or missing user)
                // provide a fallback like 'System' or 'User ID X'.
                if (empty($row['user_name'])) {
                     $row['user_name'] = $row['user_id'] ? 'User (' . $row['user_id'] . ')' : 'System';
                }
                $historyEvents[] = $row; // Add the processed row to the results array
            }

            // Update the response array on successful fetch
            $response['success'] = true;
            $response['message'] = 'History fetched successfully.';
            $response['history_events'] = $historyEvents;

        } else {
            // Execution failed
             throw new Exception("Database error executing history query: " . mysqli_stmt_error($stmt));
        }
        // Close the statement
        mysqli_stmt_close($stmt);

    } else {
        // Statement preparation failed
        throw new Exception("Database error preparing history query: " . mysqli_error($connection));
    }

} catch (Exception $e) {
    // Log the detailed error for server admins/developers
    error_log("[fetch_case_history] Error for case ID {$caseId}: " . $e->getMessage());
    // Set a more generic user-facing error message
    $response['message'] = "An error occurred while retrieving the case history.";
    // Optionally include $e->getMessage() in response during development for debugging
    // $response['debug_message'] = $e->getMessage();
} finally {
     // Ensure the database connection is closed
     if (isset($connection) && $connection instanceof mysqli) {
        mysqli_close($connection);
     }
}

// --- Output JSON Response ---
// Encode the final $response array into JSON and output it
echo json_encode($response);
exit; // Terminate script execution
?>