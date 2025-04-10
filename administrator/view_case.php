<?php
session_start();
require_once '../config/database.php'; // Adjust path if needed
// Add admin authentication check here if not handled in header
// if (!is_admin()) { header('Location: login.php'); exit; }

$case_data = null;
$case_owner_details = null;
$error_message = '';
$case_number_from_url = '';

// --- Get and Validate Case Number ---
if (isset($_GET['case_number']) && !empty(trim($_GET['case_number']))) {
    // Basic validation (e.g., check if it looks like your case number format)
    $case_number_from_url = trim($_GET['case_number']);
    if (!preg_match('/^\d{8}$/', $case_number_from_url)) { // Example: 8 digits
        $error_message = "Invalid case number format.";
    } else {
        // --- Fetch Case Data ---
        if (isset($connection) && $connection instanceof mysqli) {
            $query_case = "SELECT c.case_number, c.type, c.subject, c.severity, c.product_group,
                                  c.product, c.company, c.product_version, c.case_owner, c.attachment,
                                  u.full_name as owner_name, u.email as owner_email
                           FROM cases c
                           LEFT JOIN users u ON c.case_owner = u.id
                           WHERE c.case_number = ?";
            $stmt_case = $connection->prepare($query_case);

            if ($stmt_case) {
                $stmt_case->bind_param("s", $case_number_from_url);
                $stmt_case->execute();
                $result_case = $stmt_case->get_result();

                if ($result_case->num_rows === 1) {
                    $case_data = $result_case->fetch_assoc();
                } else {
                    $error_message = "Case not found.";
                }
                $stmt_case->close();
            } else {
                $error_message = "Error preparing to fetch case data: " . $connection->error;
                error_log($error_message);
            }
        } else {
            $error_message = "Database connection error.";
            error_log($error_message);
        }
    }
} else {
    $error_message = "No case number provided.";
}

// Determine uploads directory path (relative to this script)
$uploads_dir_relative = '../uploads/'; // Adjust if your file structure is different

?>
<!DOCTYPE html>
<html lang="en">

<head>
<?php include_once "../components/head.php"; ?>
    <title>View Case <?= htmlspecialchars($case_number_from_url) ?> - Admin</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .details-label {
            font-weight: bold;
            color: #5a5c69;
            min-width: 150px;
            /* Adjust as needed */
            display: inline-block;
        }

        .details-value {
            color: #333;
        }

        .details-section {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e3e6f0;
        }

        .details-section:last-child {
            border-bottom: none;
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
                    <h1 class="h3 mb-4 text-gray-800">Case Details: <?= htmlspecialchars($case_number_from_url) ?></h1>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php elseif ($case_data): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Subject: <?= htmlspecialchars($case_data['subject']) ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="details-section">
                                    <span class="details-label">Case Number:</span>
                                    <span class="details-value"><?= htmlspecialchars($case_data['case_number']) ?></span>
                                </div>
                                <div class="details-section">
                                    <span class="details-label">Type:</span>
                                    <span class="details-value"><?= htmlspecialchars($case_data['type']) ?></span>
                                </div>
                                <div class="details-section">
                                    <span class="details-label">Severity:</span>
                                    <span class="details-value"><?= htmlspecialchars($case_data['severity']) ?></span>
                                </div>

                                <div class="details-section">
                                    <span class="details-label">Product Group:</span>
                                    <span class="details-value"><?= htmlspecialchars($case_data['product_group']) ?></span>
                                </div>
                                <div class="details-section">
                                    <span class="details-label">Product Name:</span>
                                    <span class="details-value"><?= htmlspecialchars($case_data['product']) ?></span>
                                </div>
                                <div class="details-section">
                                    <span class="details-label">Product Version:</span>
                                    <span class="details-value"><?= htmlspecialchars($case_data['product_version']) ?></span>
                                </div>
                                <div class="details-section">
                                    <span class="details-label">Company:</span>
                                    <span class="details-value"><?= htmlspecialchars($case_data['company']) ?></span>
                                </div>
                                <div class="details-section">
                                    <span class="details-label">Submitted By:</span>
                                    <span class="details-value">
                                        <?= htmlspecialchars($case_data['owner_name'] ?? 'N/A') ?>
                                        (<?= htmlspecialchars($case_data['owner_email'] ?? 'N/A') ?>)
                                    </span>
                                </div>
                                <div class="details-section">
                                    <span class="details-label">Attachment:</span>
                                    <span class="details-value">
                                        <?php if (!empty($case_data['attachment']) && file_exists($uploads_dir_relative . $case_data['attachment'])): ?>
                                            <a href="<?= $uploads_dir_relative . htmlspecialchars($case_data['attachment']) ?>" target="_blank">
                                                <?= htmlspecialchars($case_data['attachment']) ?>
                                            </a>
                                        <?php elseif (!empty($case_data['attachment'])): ?>
                                            <?= htmlspecialchars($case_data['attachment']) ?> (File not found)
                                        <?php else: ?>
                                            None
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php include_once "../components/footer.php" ?>
                </div>
            </div> <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>

            <?php // include_once 'includes/logout_modal.php'; 
            ?>

            <script src="../vendor/jquery/jquery.min.js"></script>
            <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
            <script src="../js/sb-admin-2.min.js"></script>

</body>

</html>