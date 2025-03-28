<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
$pageTitle = "New Cases";
require_once "../fetch/new_cases.php"; // Assuming this fetches $newCasesTable
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <style>
         /* Style for details modal labels */
        #newCaseDetailsModal .modal-body dt {
            font-weight: bold;
            color: #5a5c69;
        }
         #newCaseDetailsModal .modal-body dd {
            margin-bottom: 0.75rem;
         }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php" ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/topbar.php" ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                        </div>

                    <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> Case accepted successfully! Go to <a href="ongoing_cases.php">On-going Cases</a>.
                             <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($pageTitle) ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="newCasesTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Case Number</th>
                                            <th>Severity</th>
                                            <th>Case Owner</th> 
                                            <th>Company</th>
                                            <th>Date Opened</th>
                                            <th>Details</th>
                                            <th>Action</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($newCasesTable as $row) {
                                            // --- Prepare data for details modal attributes ---
                                            $newCaseData = [
                                                'id' => $row["id"] ?? '',
                                                'case-number' => $row["case_number"] ?? 'N/A',
                                                'type' => $row["type"] ?? 'N/A',
                                                'subject' => $row["subject"] ?? 'N/A',
                                                'product-group' => $row["product_group"] ?? 'N/A',
                                                'product' => $row["product"] ?? 'N/A',
                                                'product-version' => $row["product_version"] ?? 'N/A',
                                                'severity' => $row["severity"] ?? 'N/A',
                                                'case-owner' => $row["case_owner"] ?? 'Unassigned', // Default if not set
                                                'company' => $row["company"] ?? 'N/A',
                                                'last-modified' => $row["last_modified"] ?? 'N/A',
                                                'datetime-opened' => $row["datetime_opened"] ?? 'N/A'
                                            ];

                                            $newDataAttributes = '';
                                            foreach ($newCaseData as $key => $value) {
                                                $newDataAttributes .= ' data-' . $key . '="' . htmlspecialchars($value) . '"';
                                            }

                                            // --- Buttons ---
                                            $viewNewDetailsButton = '<button
                                                type="button"
                                                class="btn btn-info btn-sm new-view-details-btn"
                                                ' . $newDataAttributes . '
                                                data-toggle="modal"
                                                data-target="#newCaseDetailsModal"> 
                                                <i class="fas fa-eye"></i> View
                                                </button>';

                                            $acceptButton = '<button
                                                type="button"
                                                class="accept-button btn btn-success btn-sm"
                                                data-toggle="modal"
                                                data-target="#acceptCase"
                                                data-case-id="' . htmlspecialchars($newCaseData["id"]) . '">
                                                <i class="bi bi-check-lg"></i>
                                                Accept
                                                </button>';

                                            // --- Output Table Row ---
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($newCaseData["case-number"]) . "</td>"; // Display plain text
                                            echo "<td>" . htmlspecialchars($newCaseData["severity"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($newCaseData["case-owner"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($newCaseData["company"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($newCaseData["datetime-opened"]) . "</td>";
                                            echo "<td>" . $viewNewDetailsButton . "</td>"; // View Details Button column
                                            echo "<td>" . $acceptButton . "</td>"; // Action Button column
                                            echo "</tr>";
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
    <?php include_once "../modals/accept_case_confirmation.php" ?> 

    <div class="modal fade" id="newCaseDetailsModal" tabindex="-1" role="dialog" aria-labelledby="newCaseDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCaseDetailsModalLabel">New Case Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-3">Case Number:</dt>
                        <dd class="col-sm-9" id="modalNewCaseNumber"></dd>

                        <dt class="col-sm-3">Type:</dt>
                        <dd class="col-sm-9" id="modalNewType"></dd>

                        <dt class="col-sm-3">Subject:</dt>
                        <dd class="col-sm-9" id="modalNewSubject"></dd>

                        <dt class="col-sm-3">Product Group:</dt>
                        <dd class="col-sm-9" id="modalNewProductGroup"></dd>

                        <dt class="col-sm-3">Product:</dt>
                        <dd class="col-sm-9" id="modalNewProduct"></dd>

                        <dt class="col-sm-3">Product Version:</dt>
                        <dd class="col-sm-9" id="modalNewProductVersion"></dd>

                        <dt class="col-sm-3">Severity:</dt>
                        <dd class="col-sm-9" id="modalNewSeverity"></dd>

                        <dt class="col-sm-3">Case Owner:</dt>
                        <dd class="col-sm-9" id="modalNewCaseOwner"></dd>

                        <dt class="col-sm-3">Company:</dt>
                        <dd class="col-sm-9" id="modalNewCompany"></dd>

                        <dt class="col-sm-3">Last Modified:</dt>
                        <dd class="col-sm-9" id="modalNewLastModified"></dd>

                        <dt class="col-sm-3">Date Opened:</dt>
                        <dd class="col-sm-9" id="modalNewDatetimeOpened"></dd>
                    </dl>
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
        // Initialize DataTable for the new cases table
        $(document).ready(function() {
            $('#newCasesTable').DataTable({ // Use the unique table ID
                 "order": [[ 4, "desc" ]], // Optional: Default sort by Date Opened descending
                 "pageLength": 10
            });
        });

        // --- Script for Handling Modals (New Cases) ---
        $(document).ready(function() {

            // -- New Case Details Modal Population --
            // Use event delegation for buttons added by DataTables
            $('#newCasesTable tbody').on('click', '.new-view-details-btn', function() {
                var button = $(this);
                var modal = $('#newCaseDetailsModal');

                // Populate the modal using data attributes
                modal.find('#modalNewCaseNumber').text(button.data('case-number'));
                modal.find('#modalNewType').text(button.data('type'));
                modal.find('#modalNewSubject').text(button.data('subject'));
                modal.find('#modalNewProductGroup').text(button.data('product-group'));
                modal.find('#modalNewProduct').text(button.data('product'));
                modal.find('#modalNewProductVersion').text(button.data('product-version'));
                modal.find('#modalNewSeverity').text(button.data('severity'));
                modal.find('#modalNewCaseOwner').text(button.data('case-owner'));
                modal.find('#modalNewCompany').text(button.data('company'));
                modal.find('#modalNewLastModified').text(button.data('last-modified'));
                modal.find('#modalNewDatetimeOpened').text(button.data('datetime-opened'));

                // data-toggle on the button shows the modal
            });

            // -- Accept Case Modal Trigger --
            // Use event delegation for the accept button
            $('#newCasesTable tbody').on('click', '.accept-button', function() {
                // Assuming your accept_case_confirmation.php modal has an input with id="caseId"
                const caseIdInput = document.getElementById("caseId"); // Get hidden input in accept modal
                 if(caseIdInput) {
                     caseIdInput.value = $(this).data("case-id"); // Get case-id from button's data attribute
                 } else {
                     console.error("Could not find #caseId input in accept modal.");
                 }
                // data-toggle on the button shows the #acceptCase modal
            });

            /*
             // Original Vanilla JS for Accept button - Keep if preferred, but delegation is better for DataTables
             const acceptCaseModal = document.getElementById("acceptCase"); // Check ID in accept_case_confirmation.php
             const caseIdHidden = document.getElementById("caseId"); // Check ID in accept_case_confirmation.php

             if (acceptCaseModal && caseIdHidden) {
                 document.querySelectorAll('.accept-button').forEach(item => { // This won't work reliably with DataTables paging/sorting
                     item.addEventListener('click', function(event) {
                         caseIdHidden.value = this.getAttribute('data-case-id');
                     });
                 });
             }
            */

        }); // End $(document).ready()
    </script>

</body>

</html>