<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
$pageTitle = "Solved Cases";
require_once "../fetch/solved_cases.php"; // Assuming this fetches $solvedCasesTable
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <style>
         /* Style for details modal labels */
        #solvedDetailsModal .modal-body dt {
            font-weight: bold;
            color: #5a5c69;
        }
         #solvedDetailsModal .modal-body dd {
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
                            <i class="bi bi-check-circle-fill"></i> Case reopened successfully! Go to <a href="reopened_cases.php">Reopened Cases</a>.
                             <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($pageTitle) ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="solvedCasesTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Case Number</th>
                                            <th>Severity</th>
                                            <th>Case Owner</th>
                                            <th>Company</th>
                                            <th>Date Solved</th> 
                                            <th>Details</th>
                                            <th>Action</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($solvedCasesTable as $row) {
                                            // --- Prepare data for details modal attributes ---
                                            $solvedCaseData = [
                                                'id' => $row["id"] ?? '', // Use null coalescing operator for safety
                                                'case-number' => $row["case_number"] ?? 'N/A',
                                                'type' => $row["type"] ?? 'N/A',
                                                'subject' => $row["subject"] ?? 'N/A',
                                                'product-group' => $row["product_group"] ?? 'N/A',
                                                'product' => $row["product"] ?? 'N/A',
                                                'product-version' => $row["product_version"] ?? 'N/A',
                                                'severity' => $row["severity"] ?? 'N/A',
                                                'case-owner' => $row["case_owner"] ?? 'N/A',
                                                'company' => $row["company"] ?? 'N/A',
                                                'reopen' => $row["reopen"] ?? 'N/A', // Assuming this holds relevant reopen info/status
                                                'last-modified' => $row["last_modified"] ?? 'N/A', // Date solved
                                                'datetime-opened' => $row["datetime_opened"] ?? 'N/A'
                                            ];

                                            $solvedDataAttributes = '';
                                            foreach ($solvedCaseData as $key => $value) {
                                                $solvedDataAttributes .= ' data-' . $key . '="' . htmlspecialchars($value) . '"';
                                            }

                                            // --- Buttons ---
                                            $viewSolvedDetailsButton = '<button
                                                type="button"
                                                class="btn btn-info btn-sm solved-view-details-btn"
                                                ' . $solvedDataAttributes . '
                                                data-toggle="modal"
                                                data-target="#solvedDetailsModal">
                                                <i class="fas fa-eye"></i> View
                                                </button>';

                                            $reopenButton = '<button
                                                data-bs-case-number="' . htmlspecialchars($solvedCaseData["case-number"]) . '"
                                                type="button"
                                                class="reopen-case-btn btn btn-warning btn-sm"
                                                data-toggle="modal"
                                                data-target="#reopenCase">
                                                <i class="bi bi-folder-symlink"></i>
                                                Reopen
                                                </button>';

                                            // --- Output Table Row ---
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($solvedCaseData["case-number"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($solvedCaseData["severity"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($solvedCaseData["case-owner"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($solvedCaseData["company"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($solvedCaseData["last-modified"]) . "</td>"; // Date solved
                                            echo "<td>" . $viewSolvedDetailsButton . "</td>"; // View Details Button column
                                            echo "<td>" . $reopenButton . "</td>"; // Action Button column
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
    <?php include_once "../modals/reopen_case.php" ?> 

    <div class="modal fade" id="solvedDetailsModal" tabindex="-1" role="dialog" aria-labelledby="solvedDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="solvedDetailsModalLabel">Solved Case Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-3">Case Number:</dt>
                        <dd class="col-sm-9" id="modalSolvedCaseNumber"></dd>

                        <dt class="col-sm-3">Type:</dt>
                        <dd class="col-sm-9" id="modalSolvedType"></dd>

                        <dt class="col-sm-3">Subject:</dt>
                        <dd class="col-sm-9" id="modalSolvedSubject"></dd>

                        <dt class="col-sm-3">Product Group:</dt>
                        <dd class="col-sm-9" id="modalSolvedProductGroup"></dd>

                        <dt class="col-sm-3">Product:</dt>
                        <dd class="col-sm-9" id="modalSolvedProduct"></dd>

                        <dt class="col-sm-3">Product Version:</dt>
                        <dd class="col-sm-9" id="modalSolvedProductVersion"></dd>

                        <dt class="col-sm-3">Severity:</dt>
                        <dd class="col-sm-9" id="modalSolvedSeverity"></dd>

                        <dt class="col-sm-3">Case Owner:</dt>
                        <dd class="col-sm-9" id="modalSolvedCaseOwner"></dd>

                        <dt class="col-sm-3">Company:</dt>
                        <dd class="col-sm-9" id="modalSolvedCompany"></dd>

                        <dt class="col-sm-3">Reopened Status:</dt> 
                        <dd class="col-sm-9" id="modalSolvedReopen"></dd>

                        <dt class="col-sm-3">Date Solved:</dt> 
                        <dd class="col-sm-9" id="modalSolvedLastModified"></dd>

                        <dt class="col-sm-3">Date Opened:</dt> 
                        <dd class="col-sm-9" id="modalSolvedDatetimeOpened"></dd>
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
        // Initialize DataTable for the solved cases table
        $(document).ready(function() {
            $('#solvedCasesTable').DataTable({ // Use the unique table ID
                "order": [[ 4, "desc" ]], // Optional: Default sort by Date Solved descending
                 "pageLength": 10
            });
        });

        // --- Script for Handling Modals (Solved Cases) ---
        $(document).ready(function() {

            // -- Solved Details Modal Population --
            // Use event delegation for buttons added by DataTables
            $('#solvedCasesTable tbody').on('click', '.solved-view-details-btn', function() {
                var button = $(this); // Button that triggered the action
                var modal = $('#solvedDetailsModal'); // The details modal for solved cases

                // Extract data using the 'data-' attributes added in PHP
                modal.find('#modalSolvedCaseNumber').text(button.data('case-number'));
                modal.find('#modalSolvedType').text(button.data('type'));
                modal.find('#modalSolvedSubject').text(button.data('subject'));
                modal.find('#modalSolvedProductGroup').text(button.data('product-group'));
                modal.find('#modalSolvedProduct').text(button.data('product'));
                modal.find('#modalSolvedProductVersion').text(button.data('product-version'));
                modal.find('#modalSolvedSeverity').text(button.data('severity'));
                modal.find('#modalSolvedCaseOwner').text(button.data('case-owner'));
                modal.find('#modalSolvedCompany').text(button.data('company'));
                modal.find('#modalSolvedReopen').text(button.data('reopen'));
                modal.find('#modalSolvedLastModified').text(button.data('last-modified')); // Date Solved
                modal.find('#modalSolvedDatetimeOpened').text(button.data('datetime-opened')); // Date Opened

                 // The data-toggle="modal" on the button handles showing the modal
            });

            // -- Reopen Case Modal Trigger --
            // Use event delegation for the reopen button
             $('#solvedCasesTable tbody').on('click', '.reopen-case-btn', function() {
                 // Assuming your reopen_case.php modal has an input with id="caseNumber"
                 const caseNumberInput = document.getElementById("caseNumber"); // Get the hidden input in reopen modal
                 if(caseNumberInput) {
                     caseNumberInput.value = $(this).data("bs-case-number");
                 } else {
                     console.error("Could not find #caseNumber input in reopen modal.");
                 }
                 // The data-toggle="modal" on the button handles showing the #reopenCase modal
             });

            /*
             // Original Vanilla JS for Reopen button - Keep if preferred, but delegation is better for DataTables
             const reopenCaseModal = document.getElementById("reopenCase"); // Check ID in reopen_case.php
             const caseNumberHidden = document.getElementById("caseNumber"); // Check ID in reopen_case.php

             if(reopenCaseModal && caseNumberHidden) {
                 document.querySelectorAll('.reopen-case-btn').forEach(item => { // This won't work reliably with DataTables paging/sorting
                     item.addEventListener('click', function(event) {
                         caseNumberHidden.value = this.getAttribute("data-bs-case-number");
                     });
                 });
             }
             */

        }); // End $(document).ready()
    </script>
</body>

</html>