<?php
session_start();
// Use __DIR__ for reliable path resolution
require_once __DIR__ . "/../config/database.php";

// --- Configuration ---
define("CHAT_ATTACHMENT_DIR", __DIR__ . "/../uploads/chat_attachments/");
define("CHAT_ATTACHMENT_URL_BASE", "../uploads/chat_attachments/");
define("CHAT_ATTACHMENT_MAX_SIZE", 5 * 1024 * 1024); // 5 MB
define("CHAT_ATTACHMENT_ALLOWED_TYPES", [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'text/plain' => 'txt'
]);
// Ensure upload directory exists and is writable (error logging added)
if (!is_dir(CHAT_ATTACHMENT_DIR)) {
    if (!mkdir(CHAT_ATTACHMENT_DIR, 0775, true)) {
        error_log("CRITICAL ERROR: Failed to create chat attachment directory: " . CHAT_ATTACHMENT_DIR);
    }
}
if (!is_writable(CHAT_ATTACHMENT_DIR)) {
    error_log("CRITICAL ERROR: Chat attachment directory is not writable: " . CHAT_ATTACHMENT_DIR);
}

// --- User Authentication ---
if (!isset($_SESSION["user_full_name"])) {
    header("Location: /login.php");
    exit;
}
$current_user = $_SESSION["user_full_name"]; // Identifier used in chats.sender/receiver

// --- Get and Validate Case Number ---
$current_case_number = null;
$error_message = null;
$other_participant = null; // Actual recipient for sending messages
$engineer_name = "[Unknown Engineer]"; // Name for display
$engineer_user_id = null; // Engineer's ID from users table
$messages = [];
$pageTitle = "Chat"; // Default title
$is_authorized = false; // Authorization flag

if (isset($_GET['case_number'])) {
    $current_case_number = trim(preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['case_number']));
    if (empty($current_case_number)) {
        $error_message = "Invalid Case Number format.";
        $current_case_number = null;
    }
} else {
    $error_message = "No chat conversation specified.";
}

// --- Database Connection Check ---
if (!isset($connection)) {
    $error_message = "Database connection not available.";
    error_log("Database connection failed in chat_view.php");
    $current_case_number = null; // Prevent further processing
}

// --- Process only if we have a valid case number and DB connection ---
if ($current_case_number && !$error_message) {

    // --- Authorization Check: Verify current user is involved in the chat for this case ---
    // (This assumes either the client or engineer can view)
    $auth_check_sql = "SELECT 1 FROM chats WHERE case_number = ? AND (sender = ? OR receiver = ?) LIMIT 1";
    // If chat might be empty initially, we might also need to check if user owns the case or is the assigned engineer
    $auth_check_sql_alt = "SELECT 1 FROM cases WHERE case_number = ? AND (case_owner = ? OR user_id = (SELECT id FROM users WHERE full_name = ? LIMIT 1)) LIMIT 1"; // Added alternative check

    $auth_stmt = $connection->prepare($auth_check_sql);
    if ($auth_stmt) {
        $auth_stmt->bind_param("sss", $current_case_number, $current_user, $current_user);
        $auth_stmt->execute();
        $auth_stmt->store_result();
        if ($auth_stmt->num_rows > 0) {
            $is_authorized = true;
        }
        $auth_stmt->close();
    } else {
        error_log("Error preparing chat auth check: " . $connection->error);
    }

    // Alternative auth check if no chat messages exist yet
    if (!$is_authorized) {
        $auth_stmt_alt = $connection->prepare($auth_check_sql_alt);
        if ($auth_stmt_alt) {
            // Need user ID for engineer check if $current_user is full name. This part is tricky without knowing what $current_user contains reliably.
            // Assuming $current_user IS full_name for this example path. Compare against case_owner directly.
            // This alt check needs refinement based on whether $current_user is ID, username, or full_name.
            // Let's assume $current_user matches case_owner directly for simplicity here if they are the owner.
            // A better approach might involve fetching the current user's ID first.
            $auth_stmt_alt->bind_param("sss", $current_case_number, $current_user, $current_user); // Placeholder binding, might need adjustment
            $auth_stmt_alt->execute();
            $auth_stmt_alt->store_result();
            if ($auth_stmt_alt->num_rows > 0) {
                $is_authorized = true; // Authorized if they own the case or are the assigned engineer (name match is weak)
            }
            $auth_stmt_alt->close();
        } else {
            error_log("Error preparing alt chat auth check: " . $connection->error);
        }
    }


    if (!$is_authorized && !$error_message) {
        $error_message = "You do not have permission to view this chat.";
    }

    // --- Proceed if Authorized ---
    if ($is_authorized) {

        // --- Mark Received Messages as Read ---
        $update_sql = "UPDATE chats SET is_read = 1 WHERE case_number = ? AND receiver = ? AND is_read = 0";
        $update_stmt = $connection->prepare($update_sql);
        if ($update_stmt) {
            $update_stmt->bind_param("ss", $current_case_number, $current_user);
            $update_stmt->execute();
            if ($update_stmt->error) {
                error_log("Error marking chat read for user '$current_user', case '$current_case_number': " . $update_stmt->error);
            }
            $update_stmt->close();
        } else {
            error_log("Error preparing mark chat read query: " . $connection->error);
        }


        // --- Get Assigned Engineer's Name and ID (for display and logic) ---
        $eng_sql = "SELECT u.id, u.full_name
                    FROM cases c
                    JOIN users u ON c.user_id = u.id
                    WHERE c.case_number = ?";
        $eng_stmt = $connection->prepare($eng_sql);
        if ($eng_stmt) {
            $eng_stmt->bind_param("s", $current_case_number);
            $eng_stmt->execute();
            $eng_result = $eng_stmt->get_result();
            if ($eng_row = $eng_result->fetch_assoc()) {
                $engineer_name = $eng_row['full_name'];
                $engineer_user_id = $eng_row['id']; // Store engineer's user ID
            } else {
                error_log("Could not find engineer details for case '$current_case_number'.");
                // Keep default $engineer_name
            }
            $eng_stmt->close();
        } else {
            error_log("Error fetching engineer name for case '$current_case_number': " . $connection->error);
        }

        // --- Determine Actual Other Participant (for sending messages) ---
        // This identifies the *other* identifier stored in sender/receiver columns
        $participant_sql = "SELECT sender, receiver FROM chats WHERE case_number = ? ORDER BY id ASC LIMIT 1";
        $p_stmt = $connection->prepare($participant_sql);
        if ($p_stmt) {
            $p_stmt->bind_param("s", $current_case_number);
            $p_stmt->execute();
            $p_result = $p_stmt->get_result();
            if ($p_row = $p_result->fetch_assoc()) {
                // $other_participant is the identifier used in sender/receiver columns
                $other_participant = ($p_row['sender'] === $current_user) ? $p_row['receiver'] : $p_row['sender'];
            } elseif ($engineer_name !== "[Unknown Engineer]") {
                // If chat is empty, determine who the current user should send to.
                // ASSUMPTION: $current_user identifier is comparable to $engineer_name (e.g., both are full names)
                // A more robust solution uses IDs if available in $_SESSION.
                if ($current_user !== $engineer_name) {
                    // Current user is likely the client/owner, send TO the engineer
                    $other_participant = $engineer_name; // Use the engineer's identifier (name in this case)
                } else {
                    // Current user IS the engineer. Need the client/owner identifier.
                    // This is hard without reporter_id or client_id in 'cases'.
                    // We'll have to leave $other_participant null, disabling send if chat is empty and user is engineer.
                    // Or fetch the case_owner from the 'cases' table. Let's fetch case_owner.
                    $owner_sql = "SELECT case_owner FROM cases WHERE case_number = ?";
                    $owner_stmt = $connection->prepare($owner_sql);
                    if ($owner_stmt) {
                        $owner_stmt->bind_param("s", $current_case_number);
                        $owner_stmt->execute();
                        $owner_res = $owner_stmt->get_result();
                        if ($owner_row = $owner_res->fetch_assoc()) {
                            $other_participant = $owner_row['case_owner']; // Send to case owner
                        }
                        $owner_stmt->close();
                    }
                }
            }
            $p_stmt->close();
        } else {
            error_log("Error fetching participants from first message: " . $connection->error);
        }


        // --- Set Page Title Using Engineer Name ---
        $pageTitle = "Chat with Engineer: " . htmlspecialchars($engineer_name);


        // --- Handle Message Sending (POST Request) ---
        // Ensure $other_participant is determined before allowing POST processing
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $other_participant) {
            // ... (Rest of the POST handling logic remains the same as previous version) ...
            // It correctly uses $other_participant as the recipient.
            $message_text = isset($_POST['message']) ? trim($_POST['message']) : '';
            $attachment_info = isset($_FILES['attachment']) ? $_FILES['attachment'] : null;
            $uploaded_file_db_path = null;
            $upload_error = null;

            if (empty($message_text) && (!$attachment_info || $attachment_info['error'] != UPLOAD_ERR_OK)) {
                $error_message = "Please enter a message or attach a file.";
            } else {
                if ($attachment_info && $attachment_info['error'] == UPLOAD_ERR_OK) {
                    $file_mime_type = mime_content_type($attachment_info['tmp_name']);
                    if (!array_key_exists($file_mime_type, CHAT_ATTACHMENT_ALLOWED_TYPES)) {
                        $upload_error = "Invalid file type ($file_mime_type).";
                    } elseif ($attachment_info['size'] > CHAT_ATTACHMENT_MAX_SIZE) {
                        $upload_error = "File is too large (Max: " . (CHAT_ATTACHMENT_MAX_SIZE / 1024 / 1024) . " MB).";
                    } else {
                        $file_extension = CHAT_ATTACHMENT_ALLOWED_TYPES[$file_mime_type];
                        $unique_filename = uniqid($current_case_number . '_', true) . '.' . $file_extension;
                        $destination_path = CHAT_ATTACHMENT_DIR . $unique_filename;
                        if (move_uploaded_file($attachment_info['tmp_name'], $destination_path)) {
                            $uploaded_file_db_path = $unique_filename;
                        } else {
                            $upload_error = "Failed to save uploaded file.";
                            error_log("File upload failed: move_uploaded_file() error for user '$current_user', case '$current_case_number'. Target: $destination_path");
                        }
                    }
                    if ($upload_error) {
                        $error_message = $upload_error;
                    }
                }

                if (!$error_message) { // Proceed if no validation or upload error
                    $insert_sql = "INSERT INTO chats (case_number, sender, receiver, message, attachment_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                    $insert_stmt = $connection->prepare($insert_sql);
                    if ($insert_stmt) {
                        $db_message = !empty($message_text) ? $message_text : null;
                        // Use $other_participant which was determined earlier
                        $insert_stmt->bind_param("sssss", $current_case_number, $current_user, $other_participant, $db_message, $uploaded_file_db_path);
                        if ($insert_stmt->execute()) {
                            header("Location: chat_view.php?case_number=" . urlencode($current_case_number));
                            exit();
                        } else {
                            $error_message = "Failed to send message.";
                            error_log("Error executing chat insert: " . $insert_stmt->error);
                        }
                        $insert_stmt->close();
                    } else {
                        $error_message = "Database error sending message.";
                        error_log("Error preparing chat insert: " . $connection->error);
                    }
                }
            } // End input validation check
        } // End POST handling


        // --- Fetch All Messages for Display ---
        // (This logic remains the same, fetches all messages for the case)
        $messages_sql = "SELECT id, sender, message, attachment_path, created_at FROM chats WHERE case_number = ? ORDER BY created_at ASC";
        $msg_stmt = $connection->prepare($messages_sql);
        if ($msg_stmt) {
            $msg_stmt->bind_param("s", $current_case_number);
            $msg_stmt->execute();
            $msg_result = $msg_stmt->get_result();
            while ($msg_row = $msg_result->fetch_assoc()) {
                $messages[] = $msg_row;
            }
            $msg_stmt->close();
        } else {
            if (!$error_message) {
                $error_message = "Could not load messages.";
            }
            error_log("Error fetching chat messages for case '$current_case_number': " . $connection->error);
        }
    } // End if authorized

} // End if case_number and DB connection exist

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once __DIR__ . "/../components/head.php"; ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> - i-Secure</title>
    <style>
        .chat-card-body {
            padding: 0 !important;
        }

        .chat-container {
            height: calc(100vh - 260px);
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            background-color: #fff;
        }

        .chat-message {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }

        .chat-bubble {
            max-width: 75%;
            padding: 0.6rem 1rem;
            border-radius: 1rem;
            word-wrap: break-word;
            line-height: 1.4;
        }

        .chat-message-sent {
            align-items: flex-end;
        }

        .chat-message-sent .chat-bubble {
            background-color: #4e73df;
            color: white;
            border-bottom-right-radius: 0.25rem;
        }

        .chat-message-sent .chat-attachment-link {
            color: rgba(255, 255, 255, 0.8);
        }

        .chat-message-sent .chat-attachment-link:hover {
            color: white;
        }

        .chat-message-received {
            align-items: flex-start;
        }

        .chat-message-received .chat-bubble {
            background-color: #eaecf4;
            color: #5a5c69;
            border-bottom-left-radius: 0.25rem;
        }

        .chat-message-received .chat-attachment-link {
            color: #4e73df;
        }

        .chat-message-received .chat-attachment-link:hover {
            color: #2e59d9;
        }

        .chat-meta {
            font-size: 0.7rem;
            color: #858796;
            margin-top: 0.25rem;
        }

        .chat-attachment-link {
            display: inline-block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            background-color: rgba(0, 0, 0, 0.05);
        }

        .chat-attachment-link:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .chat-attachment-link i {
            margin-right: 0.4rem;
        }

        .chat-input-form {
            border-top: 1px solid #e3e6f0;
            padding: 1rem;
            background-color: #f8f9fc;
        }

        .chat-input-form textarea {
            resize: none;
        }

        #attachment-filename {
            font-style: italic;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once __DIR__ . "/../components/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once __DIR__ . "/../components/user_topbar.php"; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                        <?php if ($current_case_number): ?>
                            <span class="text-muted small">Case: <?= htmlspecialchars($current_case_number) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($error_message && !$is_authorized): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php elseif (!$other_participant && $is_authorized && empty($messages)): // Handle case where chat is empty and recipient couldn't be determined (e.g., current user is engineer) 
                    ?>
                        <div class="alert alert-info">This chat hasn't started yet. Cannot determine recipient for the first message.</div>
                        <?php // Optionally allow engineer to initiate ONLY IF client ID could be reliably fetched from case_owner/reporter_id 
                        ?>
                    <?php elseif ($is_authorized): // Display chat if authorized 
                    ?>
                        <div class="card shadow mb-4">
                            <div class="card-body chat-card-body">
                                <?php if ($error_message): ?>
                                    <div class="alert alert-warning m-3"><?php echo htmlspecialchars($error_message); ?></div>
                                <?php endif; ?>

                                <div class="chat-container" id="chat-container">
                                    <?php if (empty($messages) && !$error_message): ?>
                                        <p class="text-center text-muted my-auto">No messages yet. Send the first message!</p>
                                    <?php else: ?>
                                        <?php foreach ($messages as $msg): ?>
                                            <?php $is_sent_by_me = ($msg['sender'] === $current_user); ?>
                                            <div class="chat-message <?php echo $is_sent_by_me ? 'chat-message-sent' : 'chat-message-received'; ?>">
                                                <div class="chat-bubble">
                                                    <?php if (!empty($msg['message'])): ?>
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($msg['attachment_path'])):
                                                        $attachment_url = CHAT_ATTACHMENT_URL_BASE . urlencode($msg['attachment_path']);
                                                        $original_filename = preg_replace('/^[a-zA-Z0-9]+_[0-9a-f]+\.[0-9]+\./', '', $msg['attachment_path']);
                                                        $original_filename = !empty($original_filename) ? $original_filename : $msg['attachment_path'];
                                                    ?>
                                                        <a href="<?php echo htmlspecialchars($attachment_url); ?>" target="_blank" class="chat-attachment-link" title="Download Attachment">
                                                            <i class="fas fa-paperclip"></i>
                                                            <?php echo htmlspecialchars($original_filename); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="chat-meta">
                                                    <?php // Optionally display sender name - could use $engineer_name here if needed 
                                                    ?>
                                                    <?php // if (!$is_sent_by_me) { echo htmlspecialchars($engineer_name); } else { echo "You"; } 
                                                    ?>
                                                    <?php echo date("M j, g:i a", strtotime($msg['created_at'])); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if ($is_authorized && $other_participant) : ?>
                                    <div class="chat-input-form">
                                        <form method="POST" action="chat_view.php?case_number=<?php echo urlencode($current_case_number); ?>" enctype="multipart/form-data" id="chat-form">
                                            <div class="input-group">
                                                <textarea class="form-control" name="message" placeholder="Type your message..." rows="1" aria-label="Message" style="overflow-y: hidden;"></textarea>
                                                <div class="input-group-append">
                                                    <label class="btn btn-light border mb-0" for="attachment-input" title="Attach File">
                                                        <i class="fas fa-paperclip"></i>
                                                        <input type="file" name="attachment" id="attachment-input" style="display: none;">
                                                    </label>
                                                    <button class="btn btn-primary" type="submit" title="Send Message">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small id="attachment-filename" class="form-text text-muted mt-1"></small>
                                        </form>
                                    </div>
                                <?php elseif ($is_authorized): ?>
                                    <div class="chat-input-form text-muted small p-3">Cannot determine recipient to send messages to in this conversation state.</div>
                                <?php endif; ?>

                            </div>
                        </div> <?php endif; // End check for fatal errors / authorization 
                                ?>

                </div>
            </div> <?php include_once __DIR__ . "/../components/footer.php"; ?>
        </div>
    </div> <a class="scroll-to-top rounded" href="#page-top"> <i class="fas fa-angle-up"></i> </a>
    <?php include_once __DIR__ . "/../modals/logout.php"; ?>
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script>
        $(document).ready(function() {
            /* JS remains the same */
            const chatContainer = document.getElementById('chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
            const attachmentInput = document.getElementById('attachment-input');
            const attachmentFilenameDisplay = document.getElementById('attachment-filename');
            if (attachmentInput && attachmentFilenameDisplay) {
                attachmentInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        attachmentFilenameDisplay.textContent = 'Selected: ' + this.files[0].name;
                    } else {
                        attachmentFilenameDisplay.textContent = '';
                    }
                });
            }
            const textarea = document.querySelector('.chat-input-form textarea');
            if (textarea) {
                function autoResize() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                }
                textarea.addEventListener('input', autoResize, false);
                autoResize.call(textarea);
                textarea.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        document.getElementById('chat-form').submit();
                    }
                });
            }
        });
    </script>

</body>

</html>