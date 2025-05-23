<?php
session_start();

require_once '../config/database2.php'; // Adjust path as needed

$pageTitle = "License List"; // Neutral title as it shows both active/expired

$customerProductsTable = [];
try {
    // Fetch all licenses
    $sql = "SELECT * FROM customer_products ORDER BY end_license_date ASC";
    $stmt = $pdo->query($sql);
    $customerProductsTable = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Error fetching licenses: " . $e->getMessage();
}

// Get the current date for comparison
$currentDate = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .select2-container {
            width: 100% !important;
        }

        /* CSS for blinking text */
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.3; } /* Slightly less aggressive blink */
            100% { opacity: 1; }
        }

        .expired-license {
            color: red;
            font-weight: bold;
            animation: blink 1s step-start infinite;
        }

        /* Style for the status badge */
        .status-badge {
            margin-left: 5px; /* Space between name and badge */
            vertical-align: middle; /* Align badge nicely */
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                    </div>
                    <?php if (isset($errorMessage)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $errorMessage ?>
                        </div>
                    <?php endif; ?>
                    <!-- <div class="mb-3">
                        <input type="text" id="customerSearch" class="form-control" placeholder="Search by Customer Name...">
                    </div> -->
                    <div class="table-responsive">
                        <table class="table" id="table">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Company</th>
                                    <th>Product Name</th>
                                    <th>Duration</th>
                                    <th>Start Date</th>
                                    <th>Expiration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($customerProductsTable)) {
                                    foreach ($customerProductsTable as $row) {
                                        $endDate = $row["end_license_date"] ?? null; // Use null for easier checking
                                        $isExpired = ($endDate !== null && strtotime($endDate) < strtotime($currentDate));

                                        $statusText = $isExpired ? 'Expired' : 'Active';
                                        $badgeClass = $isExpired ? 'badge-danger' : 'badge-success';
                                        $expirationClass = $isExpired ? 'expired-license' : '';
                                        $expirationDisplayText = ($endDate !== null) ? htmlspecialchars($endDate) : 'N/A';
                                        $expirationAlertText = $isExpired ? ' (EXPIRED)' : '';


                                        echo "<tr>";
                                        // Customer Name with Status Badge
                                        echo "<td>" . htmlspecialchars($row["customer_name"] ?? '') . '<span class="badge status-badge ' . $badgeClass . '">' . $statusText . '</span>' . "</td>";
                                        // Company
                                        echo "<td>" . htmlspecialchars($row["company"] ?? '') . "</td>";
                                        // Product Name
                                        echo "<td>" . htmlspecialchars($row["product_name"] ?? '') . "</td>";
                                        // Duration
                                        echo "<td>" . htmlspecialchars($row["license_duration"] ?? '') . "</td>";
                                        // Start Date
                                        echo "<td>" . htmlspecialchars($row["license_date_start"] ?? '') . "</td>";
                                        // Expiration Date with blinking if expired
                                        echo "<td class='" . $expirationClass . "'>" . $expirationDisplayText . $expirationAlertText . "</td>";

                                        // Action button with ONLY the *other* details for the modal
                                        echo '<td>
                                                    <button type="button" class="btn btn-sm btn-info view-details-btn"
                                                        data-toggle="modal"
                                                        data-target="#viewDetailsModal"
                                                        data-serial-number="' . htmlspecialchars($row["serial_number"] ?? '') . '"
                                                        data-product-category="' . htmlspecialchars($row["product_category"] ?? '') . '"
                                                        data-product-group="' . htmlspecialchars($row["product_group"] ?? '') . '"
                                                        data-product-type="' . htmlspecialchars($row["product_type"] ?? '') . '"
                                                        data-product-version="' . htmlspecialchars($row["product_version"] ?? '') . '"
                                                        data-supported-platforms="' . htmlspecialchars($row["supported_platforms"] ?? '') . '"
                                                        data-license-type="' . htmlspecialchars($row["license_type"] ?? '') . '"
                                                        data-added-at="' . htmlspecialchars($row["created_at"] ?? '') . '"
                                                    >View Other Details</button>
                                                </td>';
                                        echo "</tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="7" class="text-center">No licenses found.</td></tr>'; // Updated colspan
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php include_once "../components/footer.php" ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <?php include_once "../modals/logout.php" ?>

    <div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDetailsModalLabel">Other Product Details</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Serial Number:</strong> <span id="modal-serial-number"></span></p>
                    <p><strong>Category:</strong> <span id="modal-product-category"></span></p>
                    <p><strong>Product Group:</strong> <span id="modal-product-group"></span></p>
                    <p><strong>Type:</strong> <span id="modal-product-type"></span></p>
                    <p><strong>Version:</strong> <span id="modal-product-version"></span></p>
                    <p><strong>Supported Platforms:</strong> <span id="modal-supported-platforms"></span></p>
                    <p><strong>License Type:</strong> <span id="modal-license-type"></span></p>
                    <p><strong>Added At:</strong> <span id="modal-added-at"></span></p>
                    </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#table').DataTable({
                columns: [
                    null, // Customer Name (with badge)
                    null, // Company
                    null, // Product Name
                    null, // Duration
                    null, // Start Date
                    null, // Expiration Date (with blinking)
                    { "orderable": false } // Actions column
                ]
            });

            // Customer Search functionality
            $('#customerSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Modal population script (updated to include details NOT in the table)
            $('#viewDetailsModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var modal = $(this);

                // Populate modal fields from data attributes (these are the 'other' details)
                modal.find('#modal-serial-number').text(button.data('serial-number'));
                modal.find('#modal-product-category').text(button.data('product-category'));
                modal.find('#modal-product-group').text(button.data('product-group'));
                modal.find('#modal-product-type').text(button.data('product-type'));
                modal.find('#modal-product-version').text(button.data('product-version'));
                modal.find('#modal-supported-platforms').text(button.data('supported-platforms'));
                modal.find('#modal-license-type').text(button.data('license-type'));
                modal.find('#modal-added-at').text(button.data('added-at'));
            });
        });
    </script>
</body>
</html>