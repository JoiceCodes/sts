<?php
// Ensure session is started (if not done in a parent script)
// session_start();

// Include your database configuration
require_once __DIR__ . "/../config/database.php"; // Adjusted include path

// --- Define variables early ---
$unread_notification_count = 0;
$unread_chat_conversation_count = 0;
$current_user = isset($_SESSION["user_full_name"]) ? $_SESSION["user_full_name"] : null;
$notifications_list_found = false;
$chats_list_found = false;

?>
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <ul class="navbar-nav ml-auto">

        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <?php
                // --- Calculate UNREAD Notification Count (No changes here) ---
                if ($current_user && isset($connection)) {
                    $count_query_notif = "SELECT COUNT(*) as count FROM notifications WHERE recipient_username = ? AND is_read = 0";
                    $stmt_notif = $connection->prepare($count_query_notif);
                    if ($stmt_notif) {
                        $stmt_notif->bind_param("s", $current_user);
                        $stmt_notif->execute();
                        $res_notif = $stmt_notif->get_result();
                        if ($res_notif && $row_notif = $res_notif->fetch_assoc()) {
                            $unread_notification_count = $row_notif['count'] ?? 0;
                        } elseif ($connection->error) {
                            error_log("Error getting notification count result: " . $stmt_notif->error);
                        }
                        $stmt_notif->close();
                    } else {
                        error_log("Error preparing notification count query: " . $connection->error);
                    }
                }
                ?>
                <?php if ($unread_notification_count > 0): ?>
                    <span class="badge badge-danger badge-counter"><?php echo $unread_notification_count; ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    <?php echo $current_user ? htmlspecialchars($current_user) . "'s Notifications" : 'Notifications Center'; ?>
                </h6>
                <?php
                if ($current_user && isset($connection)) {
                    $notifications_query = "SELECT id, message_subject, sent_at, is_read FROM notifications WHERE recipient_username = ? ORDER BY is_read ASC, sent_at DESC LIMIT 5";
                    $notifications_stmt = $connection->prepare($notifications_query);
                    if ($notifications_stmt) {
                        $notifications_stmt->bind_param("s", $current_user);
                        $notifications_stmt->execute();
                        $notifications_result = $notifications_stmt->get_result();
                        if ($notifications_result && $notifications_result->num_rows > 0) {
                            $notifications_list_found = true;
                            while ($notification_row = $notifications_result->fetch_assoc()) {
                                $notification_id = $notification_row['id'];
                                $is_unread_notif = ($notification_row['is_read'] == 0);
                                $item_class_notif = $is_unread_notif ? 'font-weight-bold' : 'text-gray-600';
                                $icon_bg_class_notif = $is_unread_notif ? 'bg-primary' : 'bg-secondary';
                                $icon_fa_class_notif = $is_unread_notif ? 'fa-envelope' : 'fa-envelope-open';
                ?>
                                <a class="dropdown-item d-flex align-items-center" href="../user/notifications.php?id=<?php echo $notification_id; ?>">
                                    <div class="mr-3">
                                        <div class="icon-circle <?php echo $icon_bg_class_notif; ?>"><i class="fas <?php echo $icon_fa_class_notif; ?> text-white"></i></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small text-gray-500"><?php echo date("F j, Y, g:i a", strtotime($notification_row['sent_at'])); ?></div>
                                        <span class="<?php echo $item_class_notif; ?>"><?php echo htmlspecialchars(substr($notification_row['message_subject'], 0, 35)); ?><?php echo strlen($notification_row['message_subject']) > 35 ? '...' : ''; ?></span>
                                    </div>
                                    <?php if ($is_unread_notif): ?><span class="badge badge-danger ml-2 small align-self-center">New</span><?php endif; ?>
                                </a>
                <?php
                            }
                        } elseif ($connection->error) {
                            error_log("Error getting result for notifications list: " . $notifications_stmt->error);
                        }
                        $notifications_stmt->close();
                    } else {
                        error_log("Error preparing notifications list query: " . $connection->error);
                    }
                }
                if (!$notifications_list_found) {
                    echo '<a class="dropdown-item text-center small text-gray-500" href="#">No recent notifications</a>';
                }
                ?>
                <a class="dropdown-item text-center small text-gray-500" href="../engineer/all_notifications.php">Show All Notifications</a>
            </div>
        </li>


        <!-- <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <?php
                // --- Calculate UNREAD CHAT CONVERSATION count (No changes here) ---
                if ($current_user && isset($connection)) {
                    $count_query_chat = "SELECT COUNT(DISTINCT case_number) as count FROM chats WHERE receiver = ? AND is_read = 0";
                    $count_stmt_chat = $connection->prepare($count_query_chat);
                    if ($count_stmt_chat) {
                        $count_stmt_chat->bind_param("s", $current_user);
                        $count_stmt_chat->execute();
                        $count_result_chat = $count_stmt_chat->get_result();
                        if ($count_result_chat && $count_row_chat = $count_result_chat->fetch_assoc()) {
                            $unread_chat_conversation_count = $count_row_chat['count'] ?? 0;
                        } elseif ($connection->error) {
                            error_log("Error getting unread chat conversation count result: " . $count_stmt_chat->error);
                        }
                        $count_stmt_chat->close();
                    } else {
                        error_log("Error preparing unread chat conversation count query: " . $connection->error);
                    }
                }
                ?>
                <?php if ($unread_chat_conversation_count > 0): ?>
                    <span class="badge badge-danger badge-counter"><?php echo $unread_chat_conversation_count; ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header"> Message Center </h6>
                <?php
                // --- Fetch recent conversations, ORDERED BY LATEST MESSAGE TIMESTAMP ---
                if ($current_user && isset($connection)) {
                    $latest_chats_query = "
                     SELECT
                         c.*,
                         (SELECT 1 FROM chats sub WHERE sub.case_number = c.case_number AND sub.receiver = ? AND sub.is_read = 0 LIMIT 1) AS has_unread_for_user
                     FROM chats c
                     INNER JOIN (
                         SELECT MAX(id) as max_id
                         FROM chats
                         WHERE sender = ? OR receiver = ?
                         GROUP BY case_number
                     ) latest ON c.id = latest.max_id
                     ORDER BY
                         c.created_at DESC -- *** MODIFIED: Order ONLY by latest message timestamp ***
                     LIMIT 5"; // Still limit for dropdown
                    $chats_stmt = $connection->prepare($latest_chats_query);
                    if ($chats_stmt) {
                        $chats_stmt->bind_param("sss", $current_user, $current_user, $current_user);
                        $chats_stmt->execute();
                        $chats_result = $chats_stmt->get_result();
                        if ($chats_result && $chats_result->num_rows > 0) {
                            $chats_list_found = true;
                            while ($chat_row = $chats_result->fetch_assoc()) {
                                $other_participant = ($chat_row['sender'] === $current_user) ? $chat_row['receiver'] : $chat_row['sender'];
                                // Ensure the link points to the correct single chat view page
                                $chat_link = "../engineer/engineer_chat_view.php?case_number=" . urlencode($chat_row['case_number']);
                                $conversation_is_unread = !empty($chat_row['has_unread_for_user']);
                                $chat_item_class = $conversation_is_unread ? 'font-weight-bold' : 'text-gray-600';
                                $icon_chat_bg = $conversation_is_unread ? 'bg-success' : 'bg-primary';
                ?>
                                <a class="dropdown-item d-flex align-items-center" href="<?php echo $chat_link; ?>">
                                    <div class="dropdown-list-image mr-3">
                                        <div class="icon-circle <?php echo $icon_chat_bg; ?>">
                                            <i class="fas fa-comment text-white"></i>
                                        </div>
                                    </div>
                                    <div class="<?php echo $chat_item_class; ?> flex-grow-1">
                                        <div class="text-truncate"><?php echo htmlspecialchars(substr($chat_row['message'], 0, 30)); ?><?php echo strlen($chat_row['message']) > 30 ? '...' : ''; ?></div>
                                        <div class="small text-gray-500"><?php echo htmlspecialchars($other_participant); ?> · <?php echo date("M j, g:i a", strtotime($chat_row['created_at'])); ?></div>
                                    </div>
                                    <?php if ($conversation_is_unread): ?>
                                        <span class="badge badge-success ml-2 small align-self-center">New</span>
                                    <?php endif; ?>
                                </a>
                <?php
                            } // end while
                        } elseif ($connection->error) {
                            error_log("Error getting result for chats list: " . $chats_stmt->error);
                        }
                        $chats_stmt->close();
                    } else {
                        error_log("Error preparing chats list query: " . $connection->error);
                    }
                } // end if ($current_user && $connection)

                // Display message if no chats found for the list
                if (!$chats_list_found) {
                    echo '<a class="dropdown-item text-center small text-gray-500" href="#">No recent messages</a>';
                }
                ?>
                <a class="dropdown-item text-center small text-gray-500" href="../engineer/engineer_chats.php">Read All Messages</a>
            </div>
        </li> -->

        <div class="topbar-divider d-none d-sm-block"></div>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?php echo $current_user ? htmlspecialchars($current_user) : 'Guest'; ?>
                </span>
                <img class="img-profile rounded-circle" src="../img/undraw_profile.svg" />
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <!-- <a class="dropdown-item" href="#"> <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile </a> -->
                <a class="dropdown-item" href="../engineer/settings.php"> <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Gmail Settings </a>
                <!-- <a class="dropdown-item" href="#"> <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i> Activity Log </a> -->
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal"> <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout </a>
            </div>
        </li>

    </ul>
</nav>