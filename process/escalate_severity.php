<?php
session_start();
require_once "../config/database.php"; // Adjust path as needed

// Optional: Include helper functions if you have them
// require_once "../helpers/functions.php";

// Function to log actions (example, place in functions.php or similar)
function log_case_action($connection, $caseId, $userId, $actionType, $details = null, $oldValue = null, $newValue = null) {
    $stmt = mysqli_prepare($connection, "INSERT INTO case_history (case_id, user_id, action_type, details, old_value, new_value, timestamp) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iissss", $caseId, $userId, $actionType, $details, $oldValue, $newValue);
        if (!mysqli_stmt_execute($stmt)) {
             error_log("Failed to log case action: " . mysqli_stmt_error($stmt));
             // Return false or throw exception depending on how critical logging is
             mysqli_stmt_close($stmt);
             return false;
        }
        mysqli_stmt_close($stmt);
        return true;
    } else {
         error_log("Failed to prepare log statement: " . mysqli_error($connection));
         return false;
    }
}


// Check if user is logged in (Add role check if needed)
if (!isset($_SESSION["user_id"])) {
    $_SESSION['error_message'] = "Authentication required.";
    header("Location: ../login.php");
    exit;
}

// Process only POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --- Input Validation ---
    $caseId = filter_input(INPUT_POST, 'case_id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    $newPriority = filter_input(INPUT_POST, 'new_priority', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 4]]);
    $escalationReason = trim(filter_input(INPUT_POST, 'escalation_reason', FILTER_SANITIZE_SPECIAL_CHARS));
    $userId = $_SESSION['user_id'];
    $userFullName = $_SESSION['user_full_name'] ?? 'System User';

    if (!$caseId || $newPriority === false || $newPriority === null) {
        $_SESSION['error_message'] = "Invalid input provided for escalation.";
        header("Location: ../engineer/ongoing_cases.php?error=input");
        exit;
    }

    // --- Database Operations ---
    mysqli_begin_transaction($connection);

    try {
        // 1. Get current priority and case number
        $currentPriority = null;
        $caseNumber = null;
        $stmt_get = mysqli_prepare($connection, "SELECT priority, case_number FROM cases WHERE id = ? FOR UPDATE"); // Lock row
        if (!$stmt_get) throw new Exception("DB prepare error (fetch).");

        mysqli_stmt_bind_param($stmt_get, "i", $caseId);
        mysqli_stmt_execute($stmt_get);
        $result_get = mysqli_stmt_get_result($stmt_get);
        $caseData = mysqli_fetch_assoc($result_get);
        mysqli_stmt_close($stmt_get);

        if (!$caseData) throw new Exception("Case not found.");

        $currentPriority = $caseData['priority'];
        $caseNumber = $caseData['case_number'];

        if ($currentPriority == $newPriority) throw new Exception("New priority cannot be the same as the current one (P{$currentPriority}).");

        // 2. Update the case priority and escalation flag
        $stmt_update = mysqli_prepare($connection, "UPDATE cases SET priority = ?, is_escalated = 1, last_modified = NOW() WHERE id = ?");
        if (!$stmt_update) throw new Exception("DB prepare error (update).");

        mysqli_stmt_bind_param($stmt_update, "ii", $newPriority, $caseId);
        if (!mysqli_stmt_execute($stmt_update)) throw new Exception("Failed to update case priority: " . mysqli_stmt_error($stmt_update));
        if (mysqli_stmt_affected_rows($stmt_update) === 0) throw new Exception("Case update failed (no rows affected).");
        mysqli_stmt_close($stmt_update);

        // 3. Log the escalation action in history
        $actionType = 'ESCALATE';
        $oldValue = "P" . ($currentPriority ?? '?');
        $newValue = "P" . $newPriority;
        $details = "Priority changed from {$oldValue} to {$newValue} by {$userFullName}.";
        if (!empty($escalationReason)) { $details .= " Reason: " . $escalationReason; }

        // Use helper function or direct logging:
        $logged = log_case_action($connection, $caseId, $userId, $actionType, $details, $oldValue, $newValue);
        if (!$logged) {
            // Decide whether to rollback if logging fails. For now, just log server-side.
            error_log("CRITICAL: Escalation action for case $caseId NOT LOGGED to history.");
        }

        // 4. Commit the transaction
        mysqli_commit($connection);

        $_SESSION['success_message'] = "Case #" . htmlspecialchars($caseNumber) . " escalated to priority {$newValue}.";

        // 5. TODO: Send Notifications (Email/In-app)

    } catch (Exception $e) {
        mysqli_rollback($connection);
        error_log("Escalation failed for case ID $caseId: " . $e->getMessage());
        $_SESSION['error_message'] = "Error during escalation: " . $e->getMessage();
    }

    // --- Close connection and Redirect ---
    if (isset($connection) && $connection instanceof mysqli) { mysqli_close($connection); }
    header("Location: ../engineer/ongoing_cases.php?escalate_status=1");
    exit;

} else {
    header("Location: ../engineer/dashboard.php");
    exit;
}
?>