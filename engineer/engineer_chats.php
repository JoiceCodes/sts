<?php
session_start();
require_once __DIR__ . "/../config/database.php"; // Use __DIR__ for reliability

// --- Check Login ---
if (!isset($_SESSION["user_full_name"])) {
    header("Location: /login.php"); // Adjust path as needed
    exit;
}
// Engineer's identifier (e.g., full_name or username) from session
$current_user_identifier = $_SESSION["user_full_name"];
$pageTitle = "My Assigned Case Messages"; // Title for Engineer view

// --- Fetch Engineer's User ID ---
$current_user_id = null; // Engineer's numeric ID
$error_message = null;

if (isset($connection)) {
    // Adapt WHERE clause based on what $_SESSION["user_full_name"] holds (full_name, username, etc.)
    $id_sql = "SELECT id FROM users WHERE full_name = ? LIMIT 1";
    $id_stmt = $connection->prepare($id_sql);
    if ($id_stmt) {
        $id_stmt->bind_param("s", $current_user_identifier);
        $id_stmt->execute();
        $id_res = $id_stmt->get_result();
        if ($id_row = $id_res->fetch_assoc()) {
            $current_user_id = $id_row['id'];
        } else {
            $error_message = "Cannot identify your User ID. Access denied.";
            error_log("Could not find user ID for identifier: " . $current_user_identifier);
        }
        $id_stmt->close();
    } else {
        $error_message = "Database error during user identification.";
        error_log("Error preparing user ID lookup: " . $connection->error);
    }
} else {
    $error_message = "Database connection not available.";
    error_log("Database connection failed in chats.php (Engineer list view)");
}


// --- Fetch Conversation List (Only if Engineer ID was found) ---
$conversations = [];

// Proceed only if we have the engineer's ID and no prior errors
if ($current_user_id && !$error_message && isset($connection)) {

    // *** MODIFIED QUERY: Filter by cases.user_id (Engineer ID), Select case_owner ***
    $conv_query = "
        SELECT
            c.case_number,
            c.sender,       -- Sender of the latest message
            c.receiver,     -- Receiver of the latest message
            c.message,      -- Content of the latest message
            c.attachment_path,
            c.created_at,   -- Timestamp of the latest message
            -- Subquery to check for unread messages FOR the current ENGINEER user
            (SELECT 1 FROM chats sub WHERE sub.case_number = c.case_number AND sub.receiver = ? AND sub.is_read = 0 LIMIT 1) AS has_unread_for_user,
            -- Get the case owner's identifier (Client)
            ca.case_owner AS client_identifier -- Assuming case_owner IS the identifier to display

        FROM chats c
        INNER JOIN (
            -- Find the latest message ID for cases ASSIGNED to the current engineer user
            SELECT MAX(chats.id) as max_id
            FROM chats
            INNER JOIN cases ON chats.case_number = cases.case_number
            WHERE cases.user_id = ? -- *** Filter cases by assigned engineer ID ***
            GROUP BY chats.case_number
        ) latest ON c.id = latest.max_id
        -- Join cases again just to get case_owner easily
        INNER JOIN cases ca ON c.case_number = ca.case_number
        ORDER BY
            has_unread_for_user DESC, -- Unread conversations (for the engineer) first
            c.created_at DESC;        -- Then by latest message timestamp
    ";

    $conv_stmt = $connection->prepare($conv_query);
    if ($conv_stmt) {
        // *** Bind engineer identifier (e.g., name) for unread check, and engineer ID for filtering ***
        $conv_stmt->bind_param("si", $current_user_identifier, $current_user_id); // s for name/username, i for ID
        $conv_stmt->execute();
        $conv_result = $conv_stmt->get_result();

        if ($conv_result) {
            while ($row = $conv_result->fetch_assoc()) {
                $conversations[] = $row;
            }
        } else {
            $error_message = "Could not fetch conversation list for your assigned cases.";
            error_log("Error getting result for conversation list query for engineer ID '$current_user_id': " . $connection->error);
        }
        $conv_stmt->close();
    } else {
        $error_message = "Error preparing the conversation list query.";
        error_log("Error preparing conversation list query for engineer ID '$current_user_id': " . $connection->error);
    }
}
// Note: No change to the else block for DB connection error, it's handled above ID lookup

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

        .participant-label {
            font-size: 0.75rem;
            color: #858796;
            display: block;
            margin-bottom: -0.2rem;
        }

        /* Label for client */
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once __DIR__ . "/../components/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once __DIR__ . "/../components/engineer_topbar.php"; // Topbar shows counts relevant to the engineer 
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
                            <h6 class="m-0 font-weight-bold text-primary">Conversations for Your Assigned Cases (Unread shown first)</h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($conversations) && !$error_message): ?>
                                <div class="text-center p-5">
                                    <div class="empty-state-icon mb-3"><i class="fas fa-inbox"></i></div>
                                    <p class="text-muted lead mb-0">No messages found for your assigned cases.</p>
                                </div>
                            <?php elseif (!empty($conversations)): ?>
                                <?php foreach ($conversations as $index => $conv): ?>
                                    <?php
                                    // --- Determine details for display ---
                                    $client_identifier = $conv['client_identifier']; // Fetched from case_owner
                                    // *** Display the Client's name/identifier ***
                                    $display_participant_name = htmlspecialchars($client_identifier);

                                    // Check if the engineer has unread messages in this convo
                                    $is_unread = !empty($conv['has_unread_for_user']);
                                    $item_class = $is_unread ? 'conversation-item-unread' : 'conversation-item-read';
                                    // Icon represents the client/user
                                    $icon_fa_class = $is_unread ? 'fa-user-tag' : 'fa-user';
                                    // Link to the single chat view
                                    $link_to_chat = "engineer_chat_view.php?case_number=" . urlencode($conv['case_number']); // Engineer uses same chat view

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

                                    // Determine sender label for the snippet
                                    $last_message_sender = $conv['sender'];
                                    $sender_label = ''; // Default empty
                                    if ($last_message_sender === $current_user_identifier) {
                                        $sender_label = '<i class="fas fa-reply fa-xs mr-1"></i>You: '; // Engineer sent last
                                    } elseif ($last_message_sender === $client_identifier) {
                                        $sender_label = '<i class="fas fa-user fa-xs mr-1"></i>Client: '; // Client sent last
                                    }
                                    ?>
                                    <a href="<?php echo $link_to_chat; ?>" class="conversation-item-link <?php echo $item_class; ?>">
                                        <div class="d-flex align-items-center p-3">
                                            <div class="conversation-icon mr-3"> <i class="fas <?php echo $icon_fa_class; ?>"></i> </div>
                                            <div class="conversation-content flex-grow-1 mr-3">
                                                <span class="participant-label">Client / Case Owner</span>
                                                <h6 class="mb-1 conversation-participant">
                                                    <?php echo $display_participant_name; ?>
                                                    <span class="case-number-chip">Case: <?php echo htmlspecialchars($conv['case_number']); ?></span>
                                                </h6>
                                                <p class="mb-0 small conversation-snippet">
                                                    <?php echo $sender_label; // Show "You:" or "Client:" prefix 
                                                    ?>
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