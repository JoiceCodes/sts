<?php
session_start();
require_once "./config/database.php"; // Adjust path if needed

// Check if it's a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

// Check if required POST data is present
// case_number_encoded is optional here, only needed if passing to thank you page
if (!isset($_POST['engineer_id_encoded']) || !isset($_POST['rating'])) {
     header("Location: rate_engineer.php?error=Missing required information."); // Go back to generic error page maybe
    exit;
}

// Get and decode data
$engineerIdEncoded = $_POST['engineer_id_encoded'];
$rating = $_POST['rating'];
// Get case number only to pass it along to the thank you page for context
$caseNumberEncoded = isset($_POST['case_number_encoded']) ? $_POST['case_number_encoded'] : '';


$engineerId = base64_decode($engineerIdEncoded);

// --- Validate Data ---
if (!$engineerId) {
     header("Location: rate_engineer.php?error=Invalid engineer identifier."); // Go back to generic error page
    exit;
}
// Validate rating value
if (!filter_var($rating, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]])) {
    // Redirect back with original encoded ID and an error. Need case number too if link needs it.
     $redirectUrl = sprintf("rate_engineer.php?id=%s&case=%s&error=Invalid rating value.", urlencode($engineerIdEncoded), urlencode($caseNumberEncoded)); // Reconstruct link
    header("Location: " . $redirectUrl);
    exit;
}

// --- REMOVED: Check again if already rated ---

// --- Insert Rating into Database ---
// Adjusted query for the simpler table structure
$insertStmt = mysqli_prepare($connection, "INSERT INTO engineer_ratings (engineer_id, rating, rated_at) VALUES (?, ?, NOW())");

if ($insertStmt) {
    mysqli_stmt_bind_param($insertStmt, "ii", $engineerId, $rating); // Both are integers now

    if (mysqli_stmt_execute($insertStmt)) {
        // Success! Redirect to thank you page, passing case number for context
        mysqli_stmt_close($insertStmt);
        mysqli_close($connection);
        header("Location: rating_thank_you.php?success=1&case=" . urlencode($caseNumberEncoded));
        exit;
    } else {
        // Insertion failed
        $error = mysqli_stmt_error($insertStmt);
        mysqli_stmt_close($insertStmt);
        mysqli_close($connection);
        error_log("Failed to insert rating for engineer $engineerId: $error");
        // Redirect back with error
         $redirectUrl = sprintf("rate_engineer.php?id=%s&case=%s&error=Could not save rating. Please try again later.", urlencode($engineerIdEncoded), urlencode($caseNumberEncoded));
        header("Location: " . $redirectUrl);
        exit;
    }
} else {
    // Preparing statement failed
    $error = mysqli_error($connection);
    mysqli_close($connection);
    error_log("Failed to prepare insert rating statement: $error");
     // Redirect back with error
     $redirectUrl = sprintf("rate_engineer.php?id=%s&case=%s&error=An internal error occurred. Please try again later.", urlencode($engineerIdEncoded), urlencode($caseNumberEncoded));
    header("Location: " . $redirectUrl);
    exit;
}

?>