<?php
session_start();
require_once "../config/database.php"; 
define('UPLOAD_DIR', '../uploads/chat_attachments/');
define('UPLOAD_URL_PREFIX', 'uploads/chat_attachments/'); 

header('Content-Type: application/json'); // Ensure we always output JSON

// --- Check if user is logged in ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_full_name'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

// --- We expect POST requests with multipart/form-data ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

// --- Get data from POST fields ---
$caseNumber = isset($_POST['case_number']) ? trim($_POST['case_number']) : null;
$receiverId = isset($_POST['case_owner']) ? trim($_POST['case_owner']) : null; // Use case_owner as sent by JS
$message = isset($_POST['message']) ? trim($_POST['message']) : ''; // Message can be empty if only sending file

// --- Basic Validation ---
if (empty($caseNumber) || empty($receiverId)) {
    echo json_encode(['success' => false, 'error' => 'Missing required case information (case_number or case_owner).']);
    exit;
}

// --- File Upload Handling ---
$attachmentPath = null; // Initialize as NULL (will be stored in DB if no attachment)
$uploadError = null;    // To store any upload specific errors

if (isset($_FILES['attachment'])) {
    // Check for upload errors
    if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['attachment']['tmp_name'];
        $fileName = $_FILES['attachment']['name'];
        $fileSize = $_FILES['attachment']['size'];
        $fileType = $_FILES['attachment']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // **SECURITY:** Basic validation (Add more robust checks!)
        // Example: Limit file size (e.g., 10MB)
        if ($fileSize > 10 * 1024 * 1024) {
             $uploadError = "File exceeds maximum size limit (10MB).";
        } else {
            // **SECURITY:** Limit allowed file types (adjust as needed)
            $allowedfileExtensions = ['jpg', 'jpeg', 'gif', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                // Sanitize filename and create a unique name
                // Using uniqid with more_entropy ensures better uniqueness
                $safeBaseName = preg_replace("/[^a-zA-Z0-9\-\._]/", "", basename($fileName, "." . $fileExtension)); // Remove potentially harmful chars
                $newFileName = $safeBaseName . '_' . uniqid('', true) . '.' . $fileExtension;
                $dest_path = UPLOAD_DIR . $newFileName;

                // Ensure upload directory exists (optional but good practice)
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true); // Create recursively with appropriate permissions
                }

                // Move the file
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Store the relative URL path for DB and retrieval
                    $attachmentPath = UPLOAD_URL_PREFIX . $newFileName;
                } else {
                    $uploadError = "Failed to move uploaded file. Check permissions for " . UPLOAD_DIR;
                }
            } else {
                $uploadError = 'Upload failed. Invalid file type.';
            }
        }
    } elseif ($_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors if needed (e.g., UPLOAD_ERR_INI_SIZE)
        $uploadError = 'File upload error code: ' . $_FILES['attachment']['error'];
    }
    // If there was an upload error, stop processing and return it
    if ($uploadError !== null) {
         echo json_encode(['success' => false, 'error' => $uploadError]);
         exit;
    }

} // End file upload check

// --- Proceed only if there was no upload error (or no file was uploaded) ---

// We need either a message or an attachment to proceed
if (empty($message) && $attachmentPath === null) {
     echo json_encode(['success' => false, 'error' => 'No message content or attachment provided.']);
     exit;
}


// --- Get Receiver's Full Name ---
$receiverFullName = "Unknown User"; // Default
$getReceiverName = mysqli_prepare($connection, "SELECT full_name FROM users WHERE id = ? LIMIT 1");
if ($getReceiverName) {
    mysqli_stmt_bind_param($getReceiverName, "i", $receiverId);
    mysqli_stmt_execute($getReceiverName);
    $getReceiverNameResult = mysqli_stmt_get_result($getReceiverName);
    if ($row = mysqli_fetch_assoc($getReceiverNameResult)) {
        $receiverFullName = $row["full_name"];
    }
    mysqli_stmt_close($getReceiverName);
} else {
     // Handle error preparing statement if needed
     error_log("Failed to prepare statement to get receiver name: " . mysqli_error($connection));
}


// --- Insert Chat into Database ---
$success = false; // Initialize success flag

// Added attachment_path to query and binding
$query = "INSERT INTO chats (case_number, sender, receiver, message, attachment_path) VALUES (?, ?, ?, ?, ?)";
$stmt = $connection->prepare($query);

if ($stmt) {
    // Bind parameters: s = string. 5 parameters now.
    // Pass $attachmentPath which is either the file path (string) or NULL
    $stmt->bind_param("sssss",
        $caseNumber,
        $_SESSION['user_full_name'], // Sender's name from session
        $receiverFullName,           // Receiver's name looked up
        $message,                    // Message text (can be empty)
        $attachmentPath              // File path (or NULL)
    );

    if ($stmt->execute()) {
        $success = true;
    } else {
        // Log database error for debugging
        error_log("Database Error: Failed to insert chat message. Error: " . $stmt->error);
        $dbError = "Failed to save message to database."; // Generic error for user
    }
    $stmt->close();
} else {
     error_log("Database Error: Failed to prepare insert statement. Error: " . $connection->error);
     $dbError = "Database preparation error."; // Generic error for user
}

$connection->close();

// --- Return JSON Response ---
if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $dbError ?? 'Failed to send message. Unknown error.']);
}

?>