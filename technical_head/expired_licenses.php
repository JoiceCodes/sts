<?php
session_start();

require_once '../config/database2.php'; // Adjust path as needed

$pageTitle = "Expired Licenses";

$customerProductsTable = [];
try {
    // Fetch expired licenses (end_license_date is before the current date)
    $sql = "SELECT * FROM customer_products WHERE end_license_date < CURDATE() ORDER BY end_license_date DESC";
    $stmt = $pdo->query($sql);
    $customerProductsTable = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error or handle it appropriately
    // error_log("Database error fetching expired licenses: " . $e->getMessage());
    $errorMessage = "Error fetching expired licenses.";
}
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
                    <div class="table-responsive">
                        <table class="table" id="table">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Serial Number</th>
                                    <th>Company</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($customerProductsTable)) {
                                    foreach ($customerProductsTable as $row) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row["customer_name"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["serial_number"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["company"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["product_name"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["product_category"]) . "</td>";
                                        echo '<td>
                                                <button type="button" class="btn btn-sm btn-info view-details-btn"
                                                    data-toggle="modal"
                                                    data-target="#viewDetailsModal"
                                                    data-product-group="' . htmlspecialchars($row["product_group"]) . '"
                                                    data-product-type="' . htmlspecialchars($row["product_type"]) . '"
                                                    data-product-version="' . htmlspecialchars($row["product_version"]) . '"
                                                    data-supported-platforms="' . htmlspecialchars($row["supported_platforms"]) . '"
                                                    data-license-type="' . htmlspecialchars($row["license_type"]) . '"
                                                    data-license-start="' . htmlspecialchars($row["license_date_start"]) . '"
                                                    data-license-end="' . htmlspecialchars($row["end_license_date"]) . '"
                                                    data-license-duration="' . htmlspecialchars($row["license_duration"]) . '"
                                                    data-added-at="' . htmlspecialchars($row["created_at"]) . '"
                                                >View Other Details</button>
                                            </td>';
                                        echo "</tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center">No expired licenses found.</td></tr>';
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
                    <h5 class="modal-title" id="viewDetailsModalLabel">Product Details</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Product Group:</strong> <span id="modal-product-group"></span></p>
                    <p><strong>Type:</strong> <span id="modal-product-type"></span></p>
                    <p><strong>Version:</strong> <span id="modal-product-version"></span></p>
                    <p><strong>Supported Platforms:</strong> <span id="modal-supported-platforms"></span></p>
                    <p><strong>License Type:</strong> <span id="modal-license-type"></span></p>
                    <p><strong>License Start:</strong> <span id="modal-license-start"></span></p>
                    <p><strong>License End:</strong> <span id="modal-license-end"></span></p>
                    <p><strong>Duration:</strong> <span id="modal-license-duration"></span></p>
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
    <script src="../vendor/chart.js/Chart.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').DataTable({
            // Define columns based on their index in the HTML table
            // DataTables will read the content of the <td> elements directly
            columns: [
                { }, // Column 0: Customer Name
                { }, // Column 1: Serial Number
                { }, // Column 2: Company
                { }, // Column 3: Product Name
                { }, // Column 4: Category
                { }  // Column 5: Actions
            ],
             // Optional: You can add other DataTables options here
             // 예를 들어, 정렬, 검색 등...
        });

        // Modal script remains the same
        $('#viewDetailsModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            modal.find('#modal-product-group').text(button.data('product-group'));
            modal.find('#modal-product-type').text(button.data('product-type'));
            modal.find('#modal-product-version').text(button.data('product-version'));
            modal.find('#modal-supported-platforms').text(button.data('supported-platforms'));
            modal.find('#modal-license-type').text(button.data('license-type'));
            modal.find('#modal-license-start').text(button.data('license-start'));
            modal.find('#modal-license-end').text(button.data('license-end'));
            modal.find('#modal-license-duration').text(button.data('license-duration'));
            modal.find('#modal-added-at').text(button.data('added-at'));
        });
    });
</script>
</body>
</html>