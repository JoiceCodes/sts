<?php
session_start();
// Use __DIR__ for reliability assuming database.php is in '../config/' relative to this file's directory
require_once __DIR__ . "/../config/database.php";

// Check if user is logged in, redirect if not
if (!isset($_SESSION["user_full_name"])) {
    // Adjust the path to your login page as needed
    header("Location: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/login.php");
    exit();
}

$username = $_SESSION["user_full_name"];
$pageTitle = "Notifications";

// Check if a specific notification ID is requested
$notification_id = null;
$notification_data = null;
$error_message = null;

if (isset($_GET['id'])) {
    $notification_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($notification_id === false || $notification_id <= 0) {
        $error_message = "Invalid notification ID specified.";
        $notification_id = null; // Reset ID if invalid
    } else {
        // Fetch the specific notification, ensuring it belongs to the current user - Removed 'is_read'
        $query = "SELECT id, case_id, recipient_username, recipient_email, message_subject, message_body, sent_at FROM notifications WHERE id = ? AND recipient_username = ? LIMIT 1";
        $stmt = $connection->prepare($query);

        if ($stmt) {
            $stmt->bind_param("is", $notification_id, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows === 1) {
                $notification_data = $result->fetch_assoc();
                // --- Marking as Read section REMOVED ---
            } else {
                // Check if the query failed or simply returned no rows
                 if (!$result) {
                    $error_message = "Error fetching notification details.";
                    error_log("Error executing specific notification query: " . $stmt->error);
                } else {
                    $error_message = "Notification not found or you do not have permission to view it.";
                }
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing the notification query.";
            error_log("Error preparing specific notification query: " . $connection->error);
        }
    }
}

// If no specific ID or an error occurred fetching it, prepare to list all notifications
$all_notifications = [];
// Only fetch all if not viewing one and no error message has been set yet
if ($notification_data === null && $error_message === null) {
    // Removed 'is_read' from SELECT
    $query_all = "SELECT id, message_subject, message_body, sent_at FROM notifications WHERE recipient_username = ? ORDER BY sent_at DESC";
    $stmt_all = $connection->prepare($query_all);
    if($stmt_all) {
        $stmt_all->bind_param("s", $username);
        $stmt_all->execute();
        $result_all = $stmt_all->get_result();
        if($result_all) {
            while ($row = $result_all->fetch_assoc()) {
                $all_notifications[] = $row;
            }
        } else {
             $error_message = "Could not fetch notification list.";
             error_log("Error getting result for all notifications: " . $connection->error);
        }
        $stmt_all->close();
    } else {
        $error_message = "Error preparing the notification list query.";
        error_log("Error preparing all notifications query: " . $connection->error);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once __DIR__ . "/../components/head.php"; ?>
    <title><?php echo htmlspecialchars($pageTitle) . (isset($notification_data['message_subject']) ? ' - ' . htmlspecialchars($notification_data['message_subject']) : ''); ?> - i-Secure</title>
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
                        <?php if ($notification_data !== null): // Show back button only when viewing single notification ?>
                            <a href="all_notifications.php" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm text-white-50"></i> Go to All Notifications</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <?php if ($notification_data !== null): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary"><?php echo htmlspecialchars($notification_data['message_subject']); ?></h6>
                             <span class="small text-gray-600">
                                Case ID: <?php echo htmlspecialchars($notification_data['case_id'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p class="small text-gray-600 mb-2">
                                Received: <?php echo date("F j, Y, g:i a", strtotime($notification_data['sent_at'])); ?>
                            </p>
                            <hr>
                            <div class="message-body">
                                <?php echo nl2br(htmlspecialchars($notification_data['message_body'])); // Use nl2br to respect newlines ?>
                            </div>
                        </div>
                         <div class="card-footer text-muted small">
                            Sent to: <?php echo htmlspecialchars($notification_data['recipient_username'] . ' (' . $notification_data['recipient_email'] . ')'); ?>
                         </div>
                    </div>

                    <?php elseif (empty($error_message)): // Only show list if no error and not viewing single ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">All Notifications</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($all_notifications)): ?>
                                    <p class="text-center text-muted">You have no notifications.</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($all_notifications as $notif):
                                            // Removed styling based on read status
                                        ?>
                                        <a href="notifications.php?id=<?php echo $notif['id']; ?>" class="list-group-item list-group-item-action flex-column align-items-start">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1 font-weight-normal"><?php echo htmlspecialchars($notif['message_subject']); ?></h5>
                                                <small class="text-muted"><?php echo date("M j, Y", strtotime($notif['sent_at'])); ?></small>
                                            </div>
                                            <p class="mb-1 small text-muted">
                                                <?php echo htmlspecialchars(substr($notif['message_body'], 0, 100)); ?><?php echo strlen($notif['message_body']) > 100 ? '...' : ''; ?>
                                            </p>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
                </div>
            <?php include_once __DIR__ . "/../components/footer.php"; ?>
            </div>
        </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include_once __DIR__ . "/../modals/logout.php"; ?>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="../js/sb-admin-2.min.js"></script>

</body>
</html>