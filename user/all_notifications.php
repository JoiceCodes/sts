<?php
session_start();
require_once __DIR__ . "/../config/database.php";

// --- Login Check ---
if (!isset($_SESSION["user_full_name"])) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $login_path = '/login.php'; // Adjust as needed
    header("Location: {$protocol}://{$host}{$login_path}");
    exit();
}

$username = $_SESSION["user_full_name"];
$pageTitle = "All Notifications";

// --- Fetch Notifications ---
$all_notifications = [];
$error_message = null;
$has_unread = false; // Flag to check if 'Mark All Read' button should be active

if (isset($connection)) {
    $query_all = "SELECT id, message_subject, message_body, sent_at, is_read
                  FROM notifications
                  WHERE recipient_username = ?
                  ORDER BY is_read ASC, sent_at DESC";
    $stmt_all = $connection->prepare($query_all);

    if ($stmt_all) {
        $stmt_all->bind_param("s", $username);
        $stmt_all->execute();
        $result_all = $stmt_all->get_result();

        if ($result_all) {
            while ($row = $result_all->fetch_assoc()) {
                $all_notifications[] = $row;
                if ($row['is_read'] == 0) {
                    $has_unread = true; // Set flag if at least one is unread
                }
            }
        } else {
            $error_message = "Could not fetch notification list.";
            error_log("Error executing/getting result for all notifications query for user '$username': " . $connection->error);
        }
        $stmt_all->close();
    } else {
        $error_message = "Error preparing the notification list query.";
        error_log("Error preparing all notifications query for user '$username': " . $connection->error);
    }
} else {
    $error_message = "Database connection not available.";
    error_log("Database connection failed in all_notifications.php");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    if (isset($connection)) {
        $update_sql = "UPDATE notifications SET is_read = 1 WHERE recipient_username = ? AND is_read = 0";
        $update_stmt = $connection->prepare($update_sql);
        if ($update_stmt) {
            $update_stmt->bind_param("s", $username);
            $update_stmt->execute();
            $update_stmt->close();
            // Redirect back to this page to see the changes
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
             error_log("Error preparing mark all read query for user '$username': " . $connection->error);
             $error_message = "Could not mark all notifications as read.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once __DIR__ . "/../components/head.php"; ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> - i-Secure</title>
    <style>
        .notification-item-link {
            display: block;
            /* Make anchor block level */
            color: inherit;
            /* Inherit text color */
            text-decoration: none;
            /* Remove underline */
            border-left: 5px solid transparent;
            /* Default transparent border */
            transition: background-color 0.2s ease-in-out, border-left-color 0.2s ease-in-out;
            background-color: #fff;
            /* Default background */
            position: relative;
            /* For potential absolute elements later */
        }

        .notification-item-link:hover {
            background-color: #f8f9fc;
            /* Subtle hover background */
            text-decoration: none;
            color: inherit;
        }

        /* Styles for Unread items */
        .notification-item-unread {
            border-left-color: #4e73df;
            /* Primary color border */
            background-color: #f8f9fc;
            /* Optional: subtle background */
        }

        .notification-item-unread .notification-subject {
            font-weight: 600;
            /* Bold subject */
            color: #3a3b45;
            /* Darker text */
        }

        .notification-item-unread .notification-icon {
            color: #4e73df;
            /* Primary color icon */
        }

        /* Styles for Read items */
        .notification-item-read {
            border-left-color: #e3e6f0;
            /* Lighter border */
        }

        .notification-item-read .notification-subject,
        .notification-item-read .notification-content p,
        .notification-item-read .notification-meta small {
            opacity: 0.75;
            /* Slightly fade read text */
            color: #858796;
            /* Grayer text */
        }

        .notification-item-read .notification-icon {
            color: #b7b9cc;
            /* Lighter gray icon */
        }

        /* Icon Styling */
        .notification-icon {
            font-size: 1.5rem;
            /* Adjust icon size */
            width: 40px;
            /* Fixed width for alignment */
            text-align: center;
            margin-top: 0.25rem;
            /* Align icon slightly lower */
        }

        /* Content and Meta Alignment */
        .notification-content {
            flex-grow: 1;
            /* Takes up available space */
        }

        .notification-meta {
            min-width: 100px;
            /* Ensure space for date/badge */
            text-align: right;
        }

        .notification-meta .badge {
            font-size: 0.7rem;
            /* Smaller badge */
        }

        /* Separator */
        .notification-separator {
            border-top: 1px solid #e3e6f0;
            /* Light separator line */
        }

        /* Empty State */
        .empty-state-icon {
            font-size: 4rem;
            color: #e3e6f0;
        }

        /* Mark All Read Button */
        .mark-all-read-form {
            padding: 0.75rem 1.25rem;
            /* Match card header padding */
            border-bottom: 1px solid #e3e6f0;
            background-color: #f8f9fc;
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
                    </div>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Your Notifications</h6>
                            <?php if ($has_unread && !empty($all_notifications)): // Show button only if there are unread notifications 
                            ?>
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0" id="markAllReadForm">
                                    <input type="hidden" name="mark_all_read" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark all notifications as read">
                                        <i class="fas fa-check-double fa-sm mr-1"></i> Mark All Read
                                    </button>
                                    <script>
                                        document.getElementById('markAllReadForm').addEventListener('submit', function(event) {
                                            if (!confirm('Are you sure you want to mark all notifications as read?')) {
                                                event.preventDefault();
                                            }
                                        });
                                    </script>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="card-body p-0"> <?php if (empty($all_notifications) && !$error_message): ?>
                                <div class="text-center p-5">
                                    <div class="empty-state-icon mb-3">
                                        <i class="fas fa-bell-slash"></i>
                                    </div>
                                    <p class="text-muted lead mb-0">You have no notifications.</p>
                                </div>
                            <?php elseif (!empty($all_notifications)): ?>
                                <?php foreach ($all_notifications as $index => $notif): ?>
                                    <?php
                                                            // --- Determine Status and Classes ---
                                                            $is_unread = ($notif['is_read'] == 0);
                                                            $item_class = $is_unread ? 'notification-item-unread' : 'notification-item-read';
                                                            $icon_fa_class = $is_unread ? 'fa-envelope' : 'fa-envelope-open'; // Example icons
                                                            $subject_class = $is_unread ? 'notification-subject' : '';
                                                            $snippet_class = $is_unread ? '' : '';
                                    ?>
                                    <a href="notifications.php?id=<?php echo $notif['id']; ?>" class="notification-item-link <?php echo $item_class; ?>">
                                        <div class="d-flex align-items-start p-3">
                                            <div class="notification-icon mr-3">
                                                <i class="fas <?php echo $icon_fa_class; ?>"></i>
                                            </div>
                                            <div class="notification-content flex-grow-1 mr-3">
                                                <h6 class="mb-1 <?php echo $subject_class; ?>"><?php echo htmlspecialchars($notif['message_subject']); ?></h6>
                                                <p class="mb-0 small <?php echo $snippet_class; ?>">
                                                    <?php
                                                            $snippet = substr(strip_tags($notif['message_body']), 0, 120); // Slightly shorter snippet
                                                            echo htmlspecialchars($snippet);
                                                            if (strlen(strip_tags($notif['message_body'])) > 120) {
                                                                echo '...';
                                                            }
                                                    ?>
                                                </p>
                                            </div>
                                            <div class="notification-meta">
                                                <?php if ($is_unread): ?>
                                                    <span class="badge badge-primary mb-1 d-inline-block">New</span>
                                                <?php endif; ?>
                                                <small class="text-muted text-nowrap d-block mt-1"><?php echo date("M j, Y", strtotime($notif['sent_at'])); ?><br><?php echo date("g:i a", strtotime($notif['sent_at'])); ?></small>
                                            </div>
                                        </div>
                                    </a>
                                    <?php if ($index < count($all_notifications) - 1): // Add separator between items 
                                    ?>
                                        <div class="notification-separator"></div>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div> <?php include_once __DIR__ . "/../components/footer.php"; ?>
        </div>
    </div> <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include_once __DIR__ . "/../modals/logout.php"; ?>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>

</body>

</html>