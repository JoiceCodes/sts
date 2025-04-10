<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
$pageTitle = "Reopened Cases";
// Ensure this path is correct for fetching data
require_once "../fetch/reopened_cases_table_user.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <style>
        #detailsModal .modal-body p {
            margin-bottom: 0.5rem;
        }
        #detailsModal .modal-body strong {
            min-width: 120px; /* Adjust as needed */
            display: inline-block;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php" ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/user_topbar.php" ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                        </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($pageTitle) ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="table">
                                    <thead>
                                        <tr>
                                            <th>Case Number</th>
                                            <th>Contact Name</th>
                                            <th>Severity</th>
                                            <th>Company</th>
                                            <th>Reopened</th>
                                            <th>Actions</th> </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Assuming $reopenedCasesTable is fetched correctly
                                        if (isset($reopenedCasesTable) && is_array($reopenedCasesTable)) {
                                            foreach ($reopenedCasesTable as $row) {
                                                // Prepare data, using htmlspecialchars for safety
                                                $caseNumberLink = '<a href="#" class="case-number btn btn-link p-0" data-case-id="' . htmlspecialchars($row["id"]) . '" data-case-number="' . htmlspecialchars($row["case_number"]) . '" data-case-owner="' . htmlspecialchars($_SESSION["user_id"]) . '">' . htmlspecialchars($row["case_number"]) . '</a>';
                                                $reopenedCount = $row["reopen"] > 0 ? htmlspecialchars($row["reopen"]) . " time(s)" : "0";
                                                $contactName = htmlspecialchars($row["contact_name"]);
                                                $severity = htmlspecialchars($row["severity"]);
                                                $company = htmlspecialchars($row["company"]);

                                                // Data for the modal
                                                $type = htmlspecialchars($row["type"]);
                                                $subject = htmlspecialchars($row["subject"]);
                                                $productGroup = htmlspecialchars($row["product_group"]);
                                                $product = htmlspecialchars($row["product"]);
                                                $productVersion = htmlspecialchars($row["product_version"]);

                                                echo "<tr>";
                                                echo "<td>" . $caseNumberLink . "</td>"; // Display Case Number link
                                                echo "<td>" . $contactName . "</td>";     // Display Contact Name
                                                echo "<td>" . $severity . "</td>";        // Display Severity
                                                echo "<td>" . $company . "</td>";         // Display Company
                                                echo "<td>" . $reopenedCount . "</td>";   // Display Reopened count

                                                // Add the View Details button with data attributes
                                                echo '<td>';
                                                echo '<button type="button" class="btn btn-info btn-sm view-details-btn" 
                                                        data-toggle="modal" 
                                                        data-target="#detailsModal"
                                                        data-case-number="' . htmlspecialchars($row["case_number"]) . '"
                                                        data-type="' . $type . '"
                                                        data-subject="' . $subject . '"
                                                        data-product-group="' . $productGroup . '"
                                                        data-product="' . $product . '"
                                                        data-product-version="' . $productVersion . '">
                                                        <i class="fas fa-eye"></i> View
                                                      </button>';
                                                echo '</td>';

                                                echo "</tr>";
                                            }
                                        } else {
                                            // Handle case where data isn't available or is not an array
                                            echo '<tr><td colspan="6" class="text-center">No reopened cases found.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
    <?php include_once "../modals/mark_as_solved.php" ?>

    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"> <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Case Details (<span id="modal-case-number"></span>)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Type:</strong> <span id="modal-type"></span></p>
                    <p><strong>Subject:</strong> <span id="modal-subject"></span></p>
                    <p><strong>Product Group:</strong> <span id="modal-product-group"></span></p>
                    <p><strong>Product:</strong> <span id="modal-product"></span></p>
                    <p><strong>Product Version:</strong> <span id="modal-product-version"></span></p>
                    <hr>
                    <p><strong>Contact Name:</strong> <span id="modal-contact-name"></span></p>
                     <p><strong>Severity:</strong> <span id="modal-severity"></span></p>
                    <p><strong>Company:</strong> <span id="modal-company"></span></p>
                    <p><strong>Reopened:</strong> <span id="modal-reopened"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var dataTable = new DataTable('#table');

            // Event listener for View Details button click (using event delegation)
            $('#table tbody').on('click', '.view-details-btn', function () {
                var button = $(this);
                var caseNumber = button.data('case-number');
                var type = button.data('type');
                var subject = button.data('subject');
                var productGroup = button.data('product-group');
                var product = button.data('product');
                var productVersion = button.data('product-version');

                 // --- Optional: Get visible data directly from the table row ---
                 var row = button.closest('tr');
                 // Adjust column indices based on your final visible table structure
                 // Indices are 0-based: 0=Case#, 1=Contact, 2=Severity, 3=Company, 4=Reopened
                 var contactName = row.find('td:eq(1)').text();
                 var severity = row.find('td:eq(2)').text();
                 var company = row.find('td:eq(3)').text();
                 var reopened = row.find('td:eq(4)').text();
                 // --- End Optional ---


                // Populate the modal fields
                $('#modal-case-number').text(caseNumber);
                $('#modal-type').text(type);
                $('#modal-subject').text(subject);
                $('#modal-product-group').text(productGroup);
                $('#modal-product').text(product);
                $('#modal-product-version').text(productVersion);

                // --- Optional: Populate visible fields in modal ---
                 $('#modal-contact-name').text(contactName);
                 $('#modal-severity').text(severity);
                 $('#modal-company').text(company);
                 $('#modal-reopened').text(reopened);
                // --- End Optional ---

                // The modal will be shown automatically by Bootstrap's data-toggle/data-target
                // If you needed to show it manually: $('#detailsModal').modal('show');
            });

            // Keep your existing Mark as Solved modal script if it's needed elsewhere
            // Consider if clicking the case number link should also open the modal or do something else
            const markAsSolvedModal = document.getElementById("markAsSolved");
            const caseNumberHidden = document.getElementById("caseNumber");
            const isReopenHidden = document.getElementById("isReopen");

            if (markAsSolvedModal) { // Check if the element exists before adding listeners
                 document.querySelectorAll('.mark-as-solved-btn').forEach(item => {
                     item.addEventListener('click', function(event) {
                         caseNumberHidden.value = this.getAttribute("data-bs-case-number");
                         isReopenHidden.value = this.getAttribute("data-bs-reopen");
                     });
                 });
             }
        });
    </script>
</body>
</html>