<?php
session_start();
require_once '../config/database.php'; // Adjust path if needed
// Add admin authentication check here
// if (!is_admin()) { header('Location: login.php'); exit; }

$all_notifications = [];
$error_message = '';

// --- Pagination Logic ---
$records_per_page = 15; // Number of notifications per page
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}
$offset = ($current_page - 1) * $records_per_page;
$total_records = 0;
$total_pages = 1;

if (isset($connection) && $connection instanceof mysqli) {
    // Get total number of notifications
    $count_result = $connection->query("SELECT COUNT(*) FROM admin_notifications");
    if ($count_result) {
        $total_records = $count_result->fetch_row()[0];
        $total_pages = ceil($total_records / $records_per_page);
        if ($current_page > $total_pages && $total_records > 0) {
            // Redirect to last page if requested page is out of bounds
            header('Location: all_notifications.php?page=' . $total_pages);
            exit;
        }
    } else {
        $error_message = "Error counting notifications: " . $connection->error;
        error_log($error_message);
    }

    // Fetch notifications for the current page
    $query_all_notif = "SELECT id, case_number, notification_subject, sent_at
                        FROM admin_notifications
                        ORDER BY sent_at DESC
                        LIMIT ? OFFSET ?";
    $stmt_all_notif = $connection->prepare($query_all_notif);

    if ($stmt_all_notif) {
        $stmt_all_notif->bind_param("ii", $records_per_page, $offset);
        $stmt_all_notif->execute();
        $result_all_notif = $stmt_all_notif->get_result();
        $all_notifications = $result_all_notif->fetch_all(MYSQLI_ASSOC);
        $stmt_all_notif->close();
    } else {
        $error_message = "Error fetching notifications: " . $connection->error;
        error_log($error_message);
    }
} else {
    $error_message = "Database connection error.";
    error_log($error_message);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>All Notifications - Admin</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
    <?php include_once "../components/sidebar.php" ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
            <?php include_once "../components/administrator_topbar.php" ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">All Notifications</h1>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Notification List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Date / Time</th>
                                            <th>Subject</th>
                                            <th>Related Case</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($all_notifications)): ?>
                                            <?php foreach ($all_notifications as $notif): ?>
                                                <tr>
                                                    <td><?= date("Y-m-d H:i:s", strtotime($notif['sent_at'])) ?></td>
                                                    <td><?= htmlspecialchars($notif['notification_subject']) ?></td>
                                                    <td><?= htmlspecialchars($notif['case_number']) ?></td>
                                                    <td>
                                                        <a href="view_case.php?case_number=<?= urlencode($notif['case_number']) ?>" class="btn btn-sm btn-info" title="View Case">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No notifications found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $current_page - 1 ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>

                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $current_page + 1 ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div> <?php include_once "../components/footer.php" ?>

        </div>
    </div> <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
</body>

</html>