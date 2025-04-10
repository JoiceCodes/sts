<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
$pageTitle = "New Cases";
// Ensure this path is correct for fetching data
require_once "../fetch/new_cases_table_user.php"; // Assumes this fetches $newCasesTable
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
            min-width: 150px; /* Adjust as needed */
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

                    <div class="my-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newCase">+ New Case</button>
                    </div>

                    <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> New case submitted successfully!
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                        </div>
                    <?php endif; ?>

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
                                            <th>Severity</th>
                                            <th>Company</th>
                                            <th>Date Opened</th>
                                            <th>Actions</th> </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Assuming $newCasesTable is fetched correctly
                                        if (isset($newCasesTable) && is_array($newCasesTable)) {
                                            foreach ($newCasesTable as $row) {
                                                // Prepare data, using htmlspecialchars for safety
                                                $caseNumberLink = '<a href="#" class="case-number btn btn-link p-0" data-case-id="' . htmlspecialchars($row["id"]) . '" data-case-number="' . htmlspecialchars($row["case_number"]) . '" data-case-owner="' . htmlspecialchars($row["case_owner"] ?? '') . '">' . htmlspecialchars($row["case_number"]) . '</a>';
                                                // $contactName removed
                                                $severity = htmlspecialchars($row["severity"] ?? 'N/A');
                                                $company = htmlspecialchars($row["company"] ?? 'N/A');
                                                $dateTimeOpened = htmlspecialchars($row["datetime_opened"] ?? 'N/A');

                                                // Data for the details modal (hidden fields remain)
                                                $type = htmlspecialchars($row["type"] ?? 'N/A');
                                                $subject = htmlspecialchars($row["subject"] ?? 'N/A');
                                                $productGroup = htmlspecialchars($row["product_group"] ?? 'N/A');
                                                $product = htmlspecialchars($row["product"] ?? 'N/A');
                                                $productVersion = htmlspecialchars($row["product_version"] ?? 'N/A');
                                                $lastModified = htmlspecialchars($row["last_modified"] ?? 'N/A');
                                                // $contactName is still available in $row here if needed for modal data-* only, but we are removing it entirely

                                                echo "<tr>";
                                                echo "<td>" . $caseNumberLink . "</td>";   // Display Case Number link
                                                // Contact Name TD Removed
                                                echo "<td>" . $severity . "</td>";         // Display Severity
                                                echo "<td>" . $company . "</td>";          // Display Company
                                                echo "<td>" . $dateTimeOpened . "</td>";   // Display Date Opened

                                                // Add the View Details button
                                                echo '<td>';
                                                echo '<button type="button" class="btn btn-info btn-sm view-details-btn"
                                                        data-toggle="modal"
                                                        data-target="#detailsModal"
                                                        data-case-number="' . htmlspecialchars($row["case_number"] ?? 'N/A') . '"
                                                        data-type="' . $type . '"
                                                        data-subject="' . $subject . '"
                                                        data-product-group="' . $productGroup . '"
                                                        data-product="' . $product . '"
                                                        data-product-version="' . $productVersion . '"
                                                        data-last-modified="' . $lastModified . '">
                                                        <i class="fas fa-eye"></i> View
                                                      </button>';
                                                // Potentially add the Accept button here if needed per row
                                                // echo ' <button type="button" class="btn btn-success btn-sm accept-button" data-toggle="modal" data-target="#acceptCase" data-case-id="'.htmlspecialchars($row["id"]).'">Accept</button>';
                                                echo '</td>';

                                                echo "</tr>";
                                            }
                                        } else {
                                             // Handle case where data isn't available or is not an array
                                             echo '<tr><td colspan="5" class="text-center">No new cases found.</td></tr>'; // Adjusted colspan
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
    <?php include_once "../modals/new_case.php" ?> <?php include_once "../modals/accept_case.php" ?> <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
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
                    <p><strong>Last Modified:</strong> <span id="modal-last-modified"></span></p>
                    <hr>
                    <p><strong>Severity:</strong> <span id="modal-severity"></span></p>
                    <p><strong>Company:</strong> <span id="modal-company"></span></p>
                    <p><strong>Date & Time Opened:</strong> <span id="modal-datetime-opened"></span></p>
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
             var dataTable = new DataTable('#table', {
                 // Optional: Default order by date opened descending?
                 "order": [[ 3, "desc" ]] // Order by the 4th column (Date Opened, index 3), descending
             });

            // --- Details Modal Logic ---
            $('#table tbody').on('click', '.view-details-btn', function () {
                var button = $(this);
                var caseNumber = button.data('case-number');

                // Populate the details modal fields from data attributes
                $('#modal-case-number').text(caseNumber);
                $('#modal-type').text(button.data('type'));
                $('#modal-subject').text(button.data('subject'));
                $('#modal-product-group').text(button.data('product-group'));
                $('#modal-product').text(button.data('product'));
                $('#modal-product-version').text(button.data('product-version'));
                $('#modal-last-modified').text(button.data('last-modified'));

                // --- Get visible data directly from the table row ---
                var row = button.closest('tr');
                // Adjust column indices based on final visible table structure
                // Indices: 0=Case#, 1=Severity, 2=Company, 3=DateOpened
                // Contact Name line removed
                $('#modal-severity').text(row.find('td:eq(1)').text()); // Severity is now index 1
                $('#modal-company').text(row.find('td:eq(2)').text());  // Company is now index 2
                $('#modal-datetime-opened').text(row.find('td:eq(3)').text()); // Date Opened is now index 3

                // Modal is shown automatically by Bootstrap attributes
            });


            // --- Accept Case Modal Logic (Existing - Adapted to jQuery) ---
            const acceptCaseModal = document.getElementById("acceptCase"); // The modal itself
            const caseIdHidden = document.getElementById("caseId");         // Hidden input inside the accept modal form

            if (acceptCaseModal && caseIdHidden) {
                // Use event delegation for accept buttons, in case table is redrawn
                $('#table tbody').on('click', '.accept-button', function() {
                    const caseId = $(this).data('case-id'); // Get case ID from button's data attribute
                    caseIdHidden.value = caseId; // Set the hidden input's value
                    // No need to manually show modal if using data-toggle/data-target on the button
                });
                 console.log("Accept Case modal logic initialized.");
            } else {
                 console.warn("Accept Case modal elements (modal or hidden input #caseId) not found.");
            }


             // Auto-dismiss success alert after 5 seconds
             window.setTimeout(function() {
                 $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
                     $(this).remove();
                 });
             }, 5000); // 5000 milliseconds = 5 seconds

        }); // End $(document).ready
    </script>

</body>
</html>