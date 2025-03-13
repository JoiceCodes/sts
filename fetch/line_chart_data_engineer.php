<?php
header("Content-Type: application/json"); // Ensure the response is JSON

// Include the database connection file
require_once "../config/database.php";

// Start session to access session variables
session_start();

// Read the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Get the filters from the decoded JSON data
$timeFilter = isset($data['timeFilter']) ? $data['timeFilter'] : '';
$statusFilter = isset($data['statusFilter']) ? $data['statusFilter'] : '';

// Set up default response
$response = [
    'labels' => [],
    'data' => []
];

// Start building the query
$query = "SELECT ";
$groupBy = "";
$orderBy = "ORDER BY last_modified";

// Process the status filter
$query .= "MONTH(last_modified) AS month, COUNT(*) AS cases FROM cases WHERE 1"; // Default grouping by month

if ($statusFilter == "new") {
    $query .= " AND case_status = 'New'";
} elseif ($statusFilter == "ongoing") {
    $query .= " AND case_status = 'Waiting in Progress' AND reopen = 0";
} elseif ($statusFilter == "solved") {
    $query .= " AND case_status = 'Solved'";
} elseif ($statusFilter == "reopened") {
    $query .= " AND reopen > 0";
}

// Only check the user_id if the case_status is NOT "New"
if ($statusFilter != "new" && isset($_SESSION["user_id"])) {
    $query .= " AND user_id = " . (int)$_SESSION["user_id"];
}

// Handle time filter logic: weekly, monthly, or yearly
if ($timeFilter == 'weekly') {
    // Group by week of the year and year (use WEEK() function)
    $query = "SELECT YEAR(last_modified) AS year, WEEK(last_modified) AS week, COUNT(*) AS cases FROM cases WHERE 1";
    $groupBy = "GROUP BY YEAR(last_modified), WEEK(last_modified)";
    $orderBy = "ORDER BY YEAR(last_modified), WEEK(last_modified)";
} elseif ($timeFilter == 'monthly') {
    // Group by month and year
    $query = "SELECT YEAR(last_modified) AS year, MONTH(last_modified) AS month, COUNT(*) AS cases FROM cases WHERE 1";
    $groupBy = "GROUP BY YEAR(last_modified), MONTH(last_modified)";
    $orderBy = "ORDER BY YEAR(last_modified), MONTH(last_modified)";
} elseif ($timeFilter == 'yearly') {
    // Group by year
    $query = "SELECT YEAR(last_modified) AS year, COUNT(*) AS cases FROM cases WHERE 1";
    $groupBy = "GROUP BY YEAR(last_modified)";
    $orderBy = "ORDER BY YEAR(last_modified)";
}

// Add the status filter condition (if any)
if ($statusFilter == "new") {
    $query .= " AND case_status = 'New'";
} elseif ($statusFilter == "ongoing") {
    $query .= " AND case_status = 'Waiting in Progress' AND reopen = 0";
} elseif ($statusFilter == "solved") {
    $query .= " AND case_status = 'Solved'";
} elseif ($statusFilter == "reopened") {
    $query .= " AND reopen > 0";
}

// Complete the query with GROUP BY and ORDER BY clauses
$query .= " $groupBy $orderBy";

// Prepare and execute the query
if ($stmt = $connection->prepare($query)) {
    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the results and process them
    while ($row = $result->fetch_assoc()) {
        if ($timeFilter == 'weekly') {
            // Format the label for the week (e.g., "Year-Week")
            $response['labels'][] = $row['year'] . '-W' . str_pad($row['week'], 2, '0', STR_PAD_LEFT);
        } elseif ($timeFilter == 'monthly') {
            // Format the label for the month (e.g., "Jan 2021")
            $response['labels'][] = date('M Y', mktime(0, 0, 0, $row['month'], 10, $row['year']));
        } elseif ($timeFilter == 'yearly') {
            // Format the label for the year (e.g., "2021")
            $response['labels'][] = (string)$row['year'];
        }
        // Add the case count to the data array
        $response['data'][] = (int)$row['cases'];
    }

    $stmt->close();
} else {
    // If the query fails, return an error response
    $response = [
        'error' => 'Failed to prepare the SQL query'
    ];
}

// Return the data as JSON
echo json_encode($response);

// Close the database connection
$connection->close();
