<?php
session_start();
// Use __DIR__ for reliability assuming database.php is in '../config/' relative to this file's directory
require_once __DIR__ . "/../config/database.php";

// Check if user is logged in, redirect if not
if (!isset($_SESSION["user_full_name"])) {
    // Adjust the path to your login page as needed
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    // Assuming login is at the root, adjust if it's elsewhere (e.g., /auth/login.php)
    $login_path = '/login.php';
    header("Location: {$protocol}://{$host}{$login_path}");
    exit();
}

$username = $_SESSION["user_full_name"];
$pageTitle = "All Notifications";

// Fetch ALL notifications for the user
$all_notifications = [];
$error_message = null;

if (isset($connection)) {
    // Select necessary columns for the list view
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
} else {
    $error_message = "Database connection not available.";
    error_log("Database connection failed in all_notifications.php");
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once __DIR__ . "/../components/head.php"; ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> - i-Secure</title>
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
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Your Notifications</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($all_notifications) && !$error_message): ?>
                                <p class="text-center text-muted mt-3">You have no notifications.</p>
                            <?php elseif (!empty($all_notifications)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($all_notifications as $notif): ?>
                                    <a href="notifications.php?id=<?php echo $notif['id']; ?>" class="list-group-item list-group-item-action flex-column align-items-start">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1 font-weight-normal"><?php echo htmlspecialchars($notif['message_subject']); ?></h5>
                                            <small class="text-muted"><?php echo date("M j, Y, g:i a", strtotime($notif['sent_at'])); ?></small>
                                        </div>
                                        <p class="mb-1 small text-muted">
                                            <?php
                                            // Show a snippet of the message body
                                            $snippet = substr($notif['message_body'], 0, 150);
                                            echo htmlspecialchars($snippet);
                                            if (strlen($notif['message_body']) > 150) {
                                                echo '...';
                                            }
                                            ?>
                                        </p>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                        </div>
                    </div>

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