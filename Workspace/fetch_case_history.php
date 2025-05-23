<?php
// Filename: fetch/fetch_case_history.php (MINIMAL DEBUGGING VERSION for created_at)
header('Content-Type: application/json');

// --- Step 1: Database Connection ---
// <<< ADAPT >>> Make absolutely sure this path is correct and the script works!
require_once '../db/db_conn.php'; // Provides $pdo or $conn

// Initialize response
$response = ['success' => false, 'message' => 'Minimal script initialized.', 'history' => ['created_time' => null]]; // Default history

// --- Step 2: Check DB Connection Variable ---
// <<< ADAPT >>> Change '$pdo' to '$conn' if your connection uses MySQLi
$db_connection = null;
if (isset($pdo)) {
    $db_connection = $pdo;
    $db_type = 'PDO';
} elseif (isset($conn)) {
    $db_connection = $conn;
    $db_type = 'MySQLi';
}

if ($db_connection === null) {
    $response['message'] = 'Minimal Error: Database connection variable ($pdo or $conn) not found after include.';
    error_log($response['message']); // Log this critical error
    echo json_encode($response);
    exit;
}
$response['message'] = "Minimal: DB Connection ($db_type) seems ok.";


// --- Step 3: Get Case ID ---
if (!isset($_GET['case_id']) || empty($_GET['case_id'])) {
    $response['message'] = 'Minimal Error: Case ID not provided.';
    echo json_encode($response); exit;
}
$caseId = filter_var($_GET['case_id'], FILTER_SANITIZE_NUMBER_INT);
if (!$caseId) {
     $response['message'] = 'Minimal Error: Invalid Case ID format.';
     echo json_encode($response); exit;
}
$response['message'] .= " Received Case ID: " . $caseId . ".";


// --- Step 4: Minimal SQL Query ---
// <<< ADAPT >>> Change 'cases', 'id', 'created_at' ONLY if they are different in your DB
$sql = "SELECT created_at FROM cases WHERE id = ?";
$response['message'] .= " SQL Prepared: " . $sql . ".";


// --- Step 5: Execute Query & Fetch ---
try {
    $stmt = $db_connection->prepare($sql);
    if (!$stmt) {
        throw new Exception("Minimal Error: Failed to prepare statement - " . $db_connection->error); // Include DB error if available
    }

    if ($db_type === 'MySQLi') {
        $stmt->bind_param("i", $caseId);
    }

    if (!$stmt->execute($db_type === 'PDO' ? [$caseId] : [])) {
         throw new Exception("Minimal Error: Failed to execute statement - " . $stmt->error); // Include DB error if available
    }

    // Fetch the single column value
    $createdAtValue = null;
    if ($db_type === 'PDO') {
        $createdAtValue = $stmt->fetchColumn();
    } else { // MySQLi
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
             $row = $result->fetch_row(); // Fetch numerically indexed row
             $createdAtValue = $row[0];   // Get the first column
        }
         $result->free();
    }
    $stmt->close();

    $response['message'] .= " Query executed.";

    // Even if null, report success if query ran okay
    $response['success'] = true;
    $response['history']['created_time'] = $createdAtValue; // Assign the fetched value (could be null)
    $response['message'] .= " Fetched created_at value: " . ($createdAtValue === null ? 'NULL' : $createdAtValue);


} catch (Exception $e) {
    error_log("Minimal DB Exception in fetch_case_history.php: " . $e->getMessage());
    $response['message'] = "Minimal DB Exception: " . $e->getMessage(); // Show specific error for debugging
    $response['success'] = false; // Ensure success is false on error
}

// Close MySQLi connection if applicable
if ($db_type === 'MySQLi' && $db_connection) {
    $db_connection->close();
}

// --- Step 6: Output Final JSON ---
echo json_encode($response);
exit;
?>