<?php
session_start();
require_once __DIR__ . "/../config/database.php"; // Use __DIR__ for reliability

// --- Check Login ---
if (!isset($_SESSION["user_full_name"])) {
    header("Location: /login.php"); // Adjust path as needed
    exit;
}
// Assuming $_SESSION["user_full_name"] stores the value used in chats.sender/receiver
$current_user_identifier = $_SESSION["user_full_name"];
$pageTitle = "All Messages";

// --- Fetch Conversation List ---
$conversations = [];
$error_message = null;

if (isset($connection)) {
    // *** CORRECTED QUERY: Removed reporter_id ***
    $conv_query = "
        SELECT
            c.case_number,
            c.sender,       -- Sender of the latest message
            c.receiver,     -- Receiver of the latest message
            c.message,      -- Content of the latest message
            c.attachment_path, -- Attachment of the latest message
            c.created_at,   -- Timestamp of the latest message
            -- Subquery to check for unread messages for the current user
            (SELECT 1 FROM chats sub WHERE sub.case_number = c.case_number AND sub.receiver = ? AND sub.is_read = 0 LIMIT 1) AS has_unread_for_user,
            -- Get the assigned engineer's full name from users table via cases table
            u_eng.full_name AS engineer_full_name,
            -- Optionally get engineer's user ID if needed elsewhere
            ca.user_id AS engineer_user_id

        FROM chats c
        INNER JOIN (
            -- Find the latest message ID in conversations involving the user
            SELECT MAX(id) as max_id
            FROM chats
            WHERE sender = ? OR receiver = ? -- Conversations involving the current user
            GROUP BY case_number
        ) latest ON c.id = latest.max_id
        -- Join to get case details (specifically user_id for engineer)
        INNER JOIN cases ca ON c.case_number = ca.case_number -- Ensure case_number formats/types match!
        -- Join to get engineer details using user_id from cases table
        INNER JOIN users u_eng ON ca.user_id = u_eng.id -- Ensure user_id types match (cases.user_id -> users.id)!
        ORDER BY
            has_unread_for_user DESC, -- Unread conversations first
            c.created_at DESC;        -- Then by latest message timestamp
    ";

    $conv_stmt = $connection->prepare($conv_query);
    if ($conv_stmt) {
        // Bind current user identifier 3 times
        $conv_stmt->bind_param("sss", $current_user_identifier, $current_user_identifier, $current_user_identifier);
        $conv_stmt->execute();
        $conv_result = $conv_stmt->get_result();

        if ($conv_result) {
            while ($row = $conv_result->fetch_assoc()) {
                $conversations[] = $row;
            }
        } else {
            $error_message = "Could not fetch conversation list.";
            error_log("Error getting result for conversation list query for user '$current_user_identifier': " . $connection->error);
        }
        $conv_stmt->close();
    } else {
        $error_message = "Error preparing the conversation list query.";
        error_log("Error preparing conversation list query for user '$current_user_identifier': " . $connection->error);
    }
} else {
    $error_message = "Database connection not available.";
    error_log("Database connection failed in chats.php (list view)");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once __DIR__ . "/../components/head.php"; ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> - i-Secure</title>
    <style>
        .conversation-item-link {
            display: block;
            color: inherit;
            text-decoration: none;
            border-left: 5px solid transparent;
            transition: background-color 0.2s ease-in-out, border-left-color 0.2s ease-in-out;
            background-color: #fff;
            position: relative;
        }

        .conversation-item-link:hover {
            background-color: #f8f9fc;
            text-decoration: none;
            color: inherit;
        }

        .conversation-item-unread {
            border-left-color: #1cc88a;
            background-color: #f8f9fc;
        }

        .conversation-item-unread .conversation-participant {
            font-weight: 600;
            color: #3a3b45;
        }

        .conversation-item-unread .conversation-icon {
            color: #1cc88a;
        }

        .conversation-item-read {
            border-left-color: #e3e6f0;
        }

        .conversation-item-read .conversation-participant,
        .conversation-item-read .conversation-snippet,
        .conversation-item-read .conversation-meta small {
            opacity: 0.8;
            color: #858796;
        }

        .conversation-item-read .conversation-icon {
            color: #b7b9cc;
        }

        .conversation-icon {
            font-size: 1.7rem;
            width: 45px;
            text-align: center;
            margin-top: 0.25rem;
        }

        .conversation-content {
            flex-grow: 1;
        }

        .conversation-meta {
            min-width: 110px;
            text-align: right;
        }

        .conversation-meta .badge {
            font-size: 0.7rem;
        }

        .conversation-separator {
            border-top: 1px solid #e3e6f0;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #e3e6f0;
        }

        .case-number-chip {
            font-size: 0.7rem;
            padding: 0.1rem 0.4rem;
            background-color: #eaecf4;
            border-radius: 0.2rem;
            color: #5a5c69;
            margin-left: 0.5rem;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once __DIR__ . "/../components/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once __DIR__ . "/../components/user_topbar.php"; // Ensure topbar uses the latest unread counts 
                ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                    </div>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Your Conversations (Unread shown first)</h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($conversations) && !$error_message): ?>
                                <div class="text-center p-5">
                                    <div class="empty-state-icon mb-3"><i class="fas fa-comments"></i></div>
                                    <p class="text-muted lead mb-0">You have no messages or conversations.</p>
                                </div>
                            <?php elseif (!empty($conversations)): ?>
                                <?php foreach ($conversations as $index => $conv): ?>
                                    <?php
                                    // --- Determine details for display ---
                                    $engineer_name = $conv['engineer_full_name'];
                                    $display_participant_name = htmlspecialchars($engineer_name);

                                    // If current user IS the engineer, display the other party from the latest message
                                    // Using name comparison - using IDs ($conv['engineer_user_id']) would be better if $_SESSION stores the user ID
                                    if ($current_user_identifier === $engineer_name) {
                                        $other_party = ($conv['sender'] === $current_user_identifier) ? $conv['receiver'] : $conv['sender'];
                                        $display_participant_name = htmlspecialchars($other_party); // Assumes $other_party is the client/reporter name
                                    }

                                    $is_unread = !empty($conv['has_unread_for_user']);
                                    $item_class = $is_unread ? 'conversation-item-unread' : 'conversation-item-read';
                                    $icon_fa_class = $is_unread ? 'fa-comment-dots' : 'fa-comments';
                                    $link_to_chat = "chat_view.php?case_number=" . urlencode($conv['case_number']);

                                    // Create snippet
                                    $snippet = '';
                                    if (!empty($conv['message'])) {
                                        $snippet = substr(strip_tags($conv['message']), 0, 80);
                                        if (strlen(strip_tags($conv['message'])) > 80) {
                                            $snippet .= '...';
                                        }
                                    } elseif (!empty($conv['attachment_path'])) {
                                        $snippet = '<i class="fas fa-paperclip fa-sm"></i> Attachment';
                                    } else {
                                        $snippet = '[Empty Message]';
                                    }

                                    $last_message_sender = $conv['sender'];
                                    ?>
                                    <a href="<?php echo $link_to_chat; ?>" class="conversation-item-link <?php echo $item_class; ?>">
                                        <div class="d-flex align-items-center p-3">
                                            <div class="conversation-icon mr-3"> <i class="fas <?php echo $icon_fa_class; ?>"></i> </div>
                                            <div class="conversation-content flex-grow-1 mr-3">
                                                <h6 class="mb-1 conversation-participant">
                                                    <?php echo $display_participant_name; ?>
                                                    <span class="case-number-chip">Case: <?php echo htmlspecialchars($conv['case_number']); ?></span>
                                                </h6>
                                                <p class="mb-0 small conversation-snippet">
                                                    <?php echo ($last_message_sender === $current_user_identifier ? '<i class="fas fa-reply fa-xs mr-1"></i>You: ' : ''); ?>
                                                    <?php echo htmlspecialchars($snippet); ?>
                                                </p>
                                            </div>
                                            <div class="conversation-meta">
                                                <?php if ($is_unread): ?>
                                                    <span class="badge badge-success mb-1 d-inline-block">New</span>
                                                <?php endif; ?>
                                                <small class="text-muted text-nowrap d-block mt-1"><?php echo date("M j, Y", strtotime($conv['created_at'])); ?><br><?php echo date("g:i a", strtotime($conv['created_at'])); ?></small>
                                            </div>
                                        </div>
                                    </a>
                                    <?php if ($index < count($conversations) - 1): ?>
                                        <div class="conversation-separator"></div>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div> <?php include_once __DIR__ . "/../components/footer.php"; ?>
        </div>
    </div> <a class="scroll-to-top rounded" href="#page-top"> <i class="fas fa-angle-up"></i> </a>
    <?php include_once __DIR__ . "/../modals/logout.php"; ?>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>

</body>

</html>