<?php
session_start();
// Use __DIR__ for reliable path resolution
require_once __DIR__ . "/../config/database.php";

// --- Configuration --- (Keep as before)
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
if (!is_dir(CHAT_ATTACHMENT_DIR)) {
    if (!mkdir(CHAT_ATTACHMENT_DIR, 0775, true)) {
        error_log("CRITICAL ERROR: Failed to create chat attachment directory: " . CHAT_ATTACHMENT_DIR);
    }
}
if (!is_writable(CHAT_ATTACHMENT_DIR)) {
    error_log("CRITICAL ERROR: Chat attachment directory is not writable: " . CHAT_ATTACHMENT_DIR);
}

// --- User Authentication & ID Lookup ---
if (!isset($_SESSION["user_full_name"])) {
    header("Location: /login.php");
    exit;
}
$current_user_name = $_SESSION["user_full_name"]; // Engineer's name/identifier (used in chats.sender)
$current_user_id = null; // Engineer's numeric ID
$error_message = null;

// --- Database Connection Check ---
if (!isset($connection)) {
    $error_message = "Database connection not available.";
    error_log("Database connection failed in engineer_chat_view.php");
} else {
    // --- Get Current User's ID (Engineer's ID) ---
    $id_sql = "SELECT id FROM users WHERE full_name = ? LIMIT 1"; // Assuming session has full_name
    $id_stmt = $connection->prepare($id_sql);
    if ($id_stmt) {
        $id_stmt->bind_param("s", $current_user_name);
        $id_stmt->execute();
        $id_res = $id_stmt->get_result();
        if ($id_row = $id_res->fetch_assoc()) {
            $current_user_id = $id_row['id'];
        } else {
            $error_message = "Cannot identify your user ID. Access denied.";
            error_log("Could not find user ID for engineer name: " . $current_user_name);
        }
        $id_stmt->close();
    } else {
        $error_message = "Database error during user identification.";
        error_log("Error preparing user ID lookup: " . $connection->error);
    }
}

// --- Get and Validate Case Number ---
$current_case_number = null;
$client_identifier_from_case = null; // Identifier stored in cases.case_owner
$client_full_name = null; // Full name of the client for saving in chats.receiver
$messages = [];
$pageTitle = "Chat";
$is_authorized = false;

if (!$error_message && isset($_GET['case_number'])) {
    $current_case_number = trim(preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['case_number']));
    if (empty($current_case_number)) {
        $error_message = "Invalid Case Number format.";
        $current_case_number = null;
    }
} elseif (!$error_message) {
    $error_message = "No chat conversation specified.";
}

// --- Process only if we have case number, user ID, and no prior errors ---
if ($current_case_number && $current_user_id && !$error_message && isset($connection)) {
    $auth_sql = "SELECT ca.case_owner, u_client.full_name AS client_full_name
                 FROM cases ca
                 LEFT JOIN users u_client ON ca.case_owner = u_client.username
                 WHERE ca.case_number = ? AND ca.user_id = ?
                 LIMIT 1";
    $auth_stmt = $connection->prepare($auth_sql);
    if ($auth_stmt) {
        $auth_stmt->bind_param("si", $current_case_number, $current_user_id);
        $auth_stmt->execute();
        $auth_result = $auth_stmt->get_result();
        if ($auth_row = $auth_result->fetch_assoc()) {
            $is_authorized = true;
            $client_identifier_from_case = $auth_row['case_owner'];
            $client_full_name = $auth_row['client_full_name'] ?? $client_identifier_from_case; // Use fetched name, fallback to identifier
        } else {
            $error_message = "You are not assigned to this case, or the case does not exist.";
        }
        $auth_stmt->close();
    } else {
        $error_message = "Error verifying engineer assignment.";
        error_log("Error preparing engineer auth check: " . $connection->error);
    }


    // --- Proceed if Authorized ---
    if ($is_authorized) {

        // --- Set Page Title ---
        // Use the client's full name (or identifier if name wasn't found)
        $pageTitle = "Chat with Client: " . htmlspecialchars($client_full_name);

        // --- Mark Messages Received by Engineer as Read ---
        // Uses $current_user_name (engineer's identifier stored in chats.receiver)
        $update_sql = "UPDATE chats SET is_read = 1 WHERE case_number = ? AND receiver = ? AND is_read = 0";
        $update_stmt = $connection->prepare($update_sql);
        if ($update_stmt) {
            $update_stmt->bind_param("ss", $current_case_number, $current_user_name);
            $update_stmt->execute();
            if ($update_stmt->error) {
                error_log("Error marking chat read for engineer '$current_user_name', case '$current_case_number': " . $update_stmt->error);
            }
            $update_stmt->close();
        } else {
            error_log("Error preparing mark chat read query: " . $connection->error);
        }


        // --- Handle Message Sending (POST Request) ---
        // Recipient is always the client for the engineer
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $client_full_name) { // Check if we have the client name to save
            $message_text = isset($_POST['message']) ? trim($_POST['message']) : '';
            $attachment_info = isset($_FILES['attachment']) ? $_FILES['attachment'] : null;
            $uploaded_file_db_path = null;
            $upload_error = null;

            if (empty($message_text) && (!$attachment_info || $attachment_info['error'] != UPLOAD_ERR_OK)) {
                $error_message = "Please enter a message or attach a file.";
            } else {
                // --- Handle File Upload --- (Identical logic)
                if ($attachment_info && $attachment_info['error'] == UPLOAD_ERR_OK) {
                    $file_mime_type = mime_content_type($attachment_info['tmp_name']);
                    if (!array_key_exists($file_mime_type, CHAT_ATTACHMENT_ALLOWED_TYPES)) {
                        $upload_error = "Invalid file type ($file_mime_type).";
                    } elseif ($attachment_info['size'] > CHAT_ATTACHMENT_MAX_SIZE) {
                        $upload_error = "File is too large (Max: " . (CHAT_ATTACHMENT_MAX_SIZE / 1024 / 1024) . " MB).";
                    } else {
                        $file_extension = CHAT_ATTACHMENT_ALLOWED_TYPES[$file_mime_type];
                        $unique_filename = uniqid($current_case_number . '_eng' . $current_user_id . '_', true) . '.' . $file_extension;
                        $destination_path = CHAT_ATTACHMENT_DIR . $unique_filename;
                        if (move_uploaded_file($attachment_info['tmp_name'], $destination_path)) {
                            $uploaded_file_db_path = $unique_filename;
                        } else {
                            $upload_error = "Failed to save uploaded file.";
                            error_log("File upload failed (Engineer): move_uploaded_file() error for user '$current_user_name' ($current_user_id), case '$current_case_number'. Target: $destination_path");
                        }
                    }
                    if ($upload_error) {
                        $error_message = $upload_error;
                    }
                }

                // --- Insert into Database ---
                if (!$error_message) {
                    $insert_sql = "INSERT INTO chats (case_number, sender, receiver, message, attachment_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                    $insert_stmt = $connection->prepare($insert_sql);
                    if ($insert_stmt) {
                        $db_message = !empty($message_text) ? $message_text : null;
                        // *** MODIFIED BINDING: Save client's FULL NAME as receiver ***
                        $insert_stmt->bind_param("sssss", $current_case_number, $current_user_name, $client_full_name, $db_message, $uploaded_file_db_path);
                        if ($insert_stmt->execute()) {
                            // PRG Redirect
                            header("Location: engineer_chat_view.php?case_number=" . urlencode($current_case_number));
                            exit();
                        } else {
                            $error_message = "Failed to send message.";
                            error_log("Error executing chat insert (Engineer): " . $insert_stmt->error);
                        }
                        $insert_stmt->close();
                    } else {
                        $error_message = "Database error sending message.";
                        error_log("Error preparing chat insert (Engineer): " . $connection->error);
                    }
                }
            } // End input validation check
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$client_full_name) {
            $error_message = "Cannot send message: Client details could not be determined.";
            error_log("Attempted POST send by engineer {$current_user_name} for case {$current_case_number} but client_full_name was not resolved.");
        } // End POST handling


        // --- Fetch All Messages for Display (Query remains the same) ---
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
            error_log("Error fetching chat messages for case '$current_case_number' (Engineer view): " . $connection->error);
        }
    } // End if authorized

} // End if case_number, user ID, DB connection exist

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

        /* Engineer's messages */
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

        /* Client's messages */
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
                <?php include_once __DIR__ . "/../components/engineer_topbar.php"; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                        <?php if ($current_case_number): ?>
                            <span class="text-muted small">Case: <?= htmlspecialchars($current_case_number) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($error_message && !$is_authorized): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php elseif (!$client_full_name && $is_authorized): // Handle case where client couldn't be identified 
                    ?>
                        <div class="alert alert-warning">Could not identify the client for this case. Cannot send messages. Case Owner: <?php echo htmlspecialchars($client_identifier_from_case ?? 'Not Found'); ?></div>
                    <?php elseif ($is_authorized): // Display chat if authorized 
                    ?>
                        <div class="card shadow mb-4">
                            <div class="card-body chat-card-body">
                                <?php if ($error_message): ?>
                                    <div class="alert alert-warning m-3"><?php echo htmlspecialchars($error_message); ?></div>
                                <?php endif; ?>

                                <div class="chat-container" id="chat-container">
                                    <?php if (empty($messages) && !$error_message): ?>
                                        <p class="text-center text-muted my-auto">No messages yet. Send the first message to <?php echo htmlspecialchars($client_full_name); ?>!</p>
                                    <?php else: ?>
                                        <?php foreach ($messages as $msg): ?>
                                            <?php // Determine if message was sent by the current engineer user
                                            $is_sent_by_me = ($msg['sender'] === $current_user_name); // Compare against engineer's name/identifier
                                            ?>
                                            <div class="chat-message <?php echo $is_sent_by_me ? 'chat-message-sent' : 'chat-message-received'; ?>">
                                                <div class="chat-bubble">
                                                    <?php if (!empty($msg['message'])): ?>
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($msg['attachment_path'])):
                                                        $attachment_url = CHAT_ATTACHMENT_URL_BASE . urlencode($msg['attachment_path']);
                                                        $original_filename = preg_replace('/^[a-zA-Z0-9]+_[a-zA-Z0-9]+_[0-9a-f]+\.[0-9]+\./', '', $msg['attachment_path']);
                                                        $original_filename = !empty($original_filename) ? $original_filename : $msg['attachment_path'];
                                                    ?>
                                                        <a href="<?php echo htmlspecialchars($attachment_url); ?>" target="_blank" class="chat-attachment-link" title="Download Attachment">
                                                            <i class="fas fa-paperclip"></i>
                                                            <?php echo htmlspecialchars($original_filename); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="chat-meta">
                                                    <?php // Optionally add sender identifier below bubble if needed for clarity 
                                                    ?>
                                                    <?php // echo $is_sent_by_me ? "You (Engineer)" : htmlspecialchars($client_full_name); 
                                                    ?>
                                                    <?php echo date("M j, g:i a", strtotime($msg['created_at'])); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if ($is_authorized && $client_full_name) : // Ensure client name resolved 
                                ?>
                                    <div class="chat-input-form">
                                        <form method="POST" action="engineer_chat_view.php?case_number=<?php echo urlencode($current_case_number); ?>" enctype="multipart/form-data" id="chat-form">
                                            <div class="input-group">
                                                <textarea class="form-control" name="message" placeholder="Type your message to <?php echo htmlspecialchars($client_full_name); ?>..." rows="1" aria-label="Message" style="overflow-y: hidden;"></textarea>
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
                                    <div class="chat-input-form text-muted small p-3">Could not determine the client's details to send messages to. Case Owner identifier: <?php echo htmlspecialchars($client_identifier_from_case ?? 'Not found'); ?></div>
                                <?php endif; ?>

                            </div>
                        </div> <?php endif; // End check for authorization errors / display chat 
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