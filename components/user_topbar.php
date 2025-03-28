<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Search -->
    <!-- <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                aria-label="Search" aria-describedby="basic-addon2" />
            <div class="input-group-append">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form> -->

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">
        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                            aria-label="Search" aria-describedby="basic-addon2" />
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <?php include_once("../config/database.php"); ?>
        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <?php
                $notification_count = 0; // Default count
                if ($_SESSION["user_full_name"] && isset($connection)) { // Check if username and connection are valid
                    // Count ALL notifications for the user
                    $count_query = "SELECT COUNT(*) as count FROM notifications WHERE recipient_username = ?";
                    $count_stmt = $connection->prepare($count_query);
                    if ($count_stmt) {
                        $count_stmt->bind_param("s", $_SESSION["user_full_name"]);
                        $count_stmt->execute();
                        $count_result = $count_stmt->get_result();
                        if ($count_result) {
                            $count_row = $count_result->fetch_assoc();
                            $notification_count = $count_row['count'] ?? 0; // Use null coalescing operator
                        } else {
                            error_log("Error getting result for notification count: " . $connection->error);
                        }
                        $count_stmt->close();
                    } else {
                        error_log("Error preparing notification count query: " . $connection->error);
                    }
                }
                ?>
                <span class="badge badge-danger badge-counter"><?php echo $notification_count > 0 ? $notification_count : ''; ?></span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    <?php echo $_SESSION["user_full_name"] ? htmlspecialchars($_SESSION["user_full_name"]) . "'s Notifications" : 'Notifications'; ?>
                </h6>

                <?php
                $notifications_found = false; // Flag to check if any notifications were fetched
                if ($_SESSION["user_full_name"] && isset($connection)) {
                    // Fetch recent notifications (e.g., last 5) - Removed 'is_read'
                    $notifications_query = "SELECT id, message_subject, message_body, sent_at FROM notifications WHERE recipient_username = ? ORDER BY sent_at DESC LIMIT 5";
                    $notifications_stmt = $connection->prepare($notifications_query);

                    if ($notifications_stmt) {
                        $notifications_stmt->bind_param("s", $_SESSION["user_full_name"]);
                        $notifications_stmt->execute();
                        $notifications_result = $notifications_stmt->get_result();

                        if ($notifications_result && $notifications_result->num_rows > 0) {
                            $notifications_found = true;
                            while ($notification_row = $notifications_result->fetch_assoc()) {
                                $notification_id = $notification_row['id'];
                ?>
                                <a class="dropdown-item d-flex align-items-center" href="../user/notifications.php?id=<?php echo $notification_id; ?>">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500"><?php echo date("F j, Y, g:i a", strtotime($notification_row['sent_at'])); ?></div>
                                        <span class="font-weight-normal"><?php echo htmlspecialchars(substr($notification_row['message_subject'], 0, 40)); ?><?php echo strlen($notification_row['message_subject']) > 40 ? '...' : ''; ?></span>
                                    </div>
                                </a>
                <?php
                            }
                        } else {
                            // No error, just no rows found
                        }
                        $notifications_stmt->close();
                    } else {
                        error_log("Error preparing notifications list query: " . $connection->error);
                    }
                }

                if (!$notifications_found) {
                    echo '<a class="dropdown-item text-center small text-gray-500" href="#">No notifications</a>';
                }
                ?>
                <a class="dropdown-item text-center small text-gray-500" href="../user/all_notifications.php">Show All Notifications</a>
            </div>
        </li>


        <!-- Nav Item - Messages -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <?php
                $chat_count = 0; // Default count
                // Ensure session variable and connection are available before querying
                if (isset($_SESSION["user_full_name"]) && isset($connection)) {
                    $current_user = $_SESSION["user_full_name"];

                    // Count distinct conversations involving the user
                    $count_query = "SELECT COUNT(DISTINCT case_number) as count FROM chats WHERE sender = ? OR receiver = ?";
                    $count_stmt = $connection->prepare($count_query);
                    if ($count_stmt) {
                        $count_stmt->bind_param("ss", $current_user, $current_user);
                        $count_stmt->execute();
                        $count_result = $count_stmt->get_result();
                        if ($count_result) {
                            $count_row = $count_result->fetch_assoc();
                            $chat_count = $count_row['count'] ?? 0;
                        } else {
                            error_log("Error getting result for chat count: " . $connection->error);
                        }
                        $count_stmt->close();
                    } else {
                        error_log("Error preparing chat count query: " . $connection->error);
                    }
                }
                ?>
                <span class="badge badge-danger badge-counter"><?php echo $chat_count > 0 ? $chat_count : ''; ?></span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                    Message Center
                </h6>

                <?php
                $chats_found = false; // Flag to check if any chats were fetched
                if (isset($current_user) && isset($connection)) { // Use $current_user defined above
                    // Fetch the latest message ID for each conversation involving the user
                    // Using MAX(id) assumes higher IDs are newer, safer than timestamp comparison for ties
                    $latest_chats_query = "
                SELECT c.*
                FROM chats c
                INNER JOIN (
                    SELECT MAX(id) as max_id
                    FROM chats
                    WHERE sender = ? OR receiver = ?
                    GROUP BY case_number
                ) latest ON c.id = latest.max_id
                ORDER BY c.created_at DESC
                LIMIT 5";

                    $chats_stmt = $connection->prepare($latest_chats_query);

                    if ($chats_stmt) {
                        $chats_stmt->bind_param("ss", $current_user, $current_user);
                        $chats_stmt->execute();
                        $chats_result = $chats_stmt->get_result();

                        if ($chats_result && $chats_result->num_rows > 0) {
                            $chats_found = true;
                            while ($chat_row = $chats_result->fetch_assoc()) {
                                // Determine the other participant
                                $other_participant = ($chat_row['sender'] === $current_user) ? $chat_row['receiver'] : $chat_row['sender'];
                                // Generate a link to the specific chat (adjust URL as needed)
                                $chat_link = "../user/chat.php?case_number=" . urlencode($chat_row['case_number']);
                ?>
                                <a class="dropdown-item d-flex align-items-center" href="<?php echo $chat_link; ?>">
                                    <div class="dropdown-list-image mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    </div>
                                    <div class="font-weight-normal">
                                        <div class="text-truncate"><?php echo htmlspecialchars(substr($chat_row['message'], 0, 40)); ?><?php echo strlen($chat_row['message']) > 40 ? '...' : ''; ?></div>
                                        <div class="small text-gray-500"><?php echo htmlspecialchars($other_participant); ?> · <?php echo date("M j, g:i a", strtotime($chat_row['created_at'])); ?></div>
                                    </div>
                                </a>
                <?php
                            }
                        } else {
                            // No error, just no rows found
                            if (!$chats_result) { // Log error only if query failed
                                error_log("Error getting result for chats list: " . $connection->error);
                            }
                        }
                        $chats_stmt->close();
                    } else {
                        error_log("Error preparing chats list query: " . $connection->error);
                    }
                }

                if (!$chats_found) {
                    echo '<a class="dropdown-item text-center small text-gray-500" href="#">No new messages</a>';
                }
                ?>
                <a class="dropdown-item text-center small text-gray-500" href="../user/chats.php">Read All Messages</a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= $_SESSION["user_full_name"] ?></span>
                <img class="img-profile rounded-circle" src="../img/undraw_profile.svg" />
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Settings
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>