<?php
// --- Fetch Admin Notifications ---
// Ensure $connection is available (e.g., require_once 'path/to/config/database.php';)
// Ensure session is started (session_start();)

$notifications = [];
$notification_count = 0;

// Check if $connection variable exists and is a valid mysqli connection
if (isset($connection) && $connection instanceof mysqli && $connection->ping()) {
    // Fetch the 5 most recent notifications
    $query_notif = "SELECT id, case_number, notification_subject, notification_body, sent_at
                    FROM admin_notifications
                    ORDER BY sent_at DESC
                    LIMIT 5";

    $result_notif = $connection->query($query_notif);

    if ($result_notif) {
        // Fetch all results into an array
        $notifications = $result_notif->fetch_all(MYSQLI_ASSOC);
        // Get the count of fetched notifications (max 5)
        $notification_count = $result_notif->num_rows;
        // Optional: Add another query here to count *all* unread notifications if you implement an 'is_read' status later
    } else {
        // Log error if query fails
        error_log("Error fetching admin notifications: " . $connection->error);
    }
} else {
     // Log error if connection is not available
     error_log("Database connection not available or invalid for fetching notifications.");
}

?>

<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
            </a>
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

        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <?php if ($notification_count > 0): ?>
                    <span class="badge badge-danger badge-counter">
                        <?= $notification_count ?>
                        <?php // You could use logic like ($real_unread_count > 5 ? '5+' : $real_unread_count) if you fetch a separate total unread count ?>
                    </span>
                <?php endif; ?>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">Notifications Center</h6>

                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                            // Format date
                            $display_date = date("M j, Y g:i A", strtotime($notification['sent_at'])); // Short month format

                            // Prepare preview text (truncate plain text body)
                            $preview_body = htmlspecialchars(trim($notification['notification_body'])); // Trim whitespace
                            $max_len = 60; // Max characters for preview
                            if (mb_strlen($preview_body) > $max_len) { // Use mb_strlen for multi-byte safety
                                $preview_body = mb_substr($preview_body, 0, $max_len) . '...';
                            }
                             // Replace potential multiple newlines/spaces for cleaner preview
                            $preview_body = preg_replace('/\s+/', ' ', $preview_body);


                            // Set link target (adjust path and parameters as needed)
                            $notification_link = "view_case.php?case_number=" . urlencode($notification['case_number']);
                        ?>
                        <a class="dropdown-item d-flex align-items-center" href="<?= $notification_link ?>">
                            <div class="mr-3">
                                <div class="icon-circle bg-primary">
                                    <i class="fas fa-file-alt text-white"></i> </div>
                            </div>
                            <div>
                                <div class="small text-gray-500"><?= $display_date ?></div>
                                <span class="font-weight-bold"><?= htmlspecialchars($notification['notification_subject']) ?></span>
                                <div class="small text-gray-700"><?= $preview_body ?></div> </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="dropdown-item text-center small text-gray-500 py-3">No new notifications</div>
                <?php endif; ?>

                <a class="dropdown-item text-center small text-gray-500" href="all_notifications.php">Show All Notifications</a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?= isset($_SESSION["user_full_name"]) ? htmlspecialchars($_SESSION["user_full_name"]) : 'Admin User' ?>
                </span>
                <img class="img-profile rounded-circle" src="../img/undraw_profile.svg" />
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <!-- <a class="dropdown-item" href="#"> <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile </a> -->
                <a class="dropdown-item" href="../administrator/settings.php"> <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Gmail Settings </a>
                <!-- <a class="dropdown-item" href="#"> <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i> Activity Log </a> -->
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>