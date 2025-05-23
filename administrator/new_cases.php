<?php
session_start();
$pageTitle = "New Cases";


require_once "../fetch/new_cases.php";
require_once "../fetch/engineers.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        /* Optional: Adjust spacing for multiple buttons */
        .action-buttons .btn {
            margin-right: 5px;
        }
        #caseDetailsModal .modal-body p {
            margin-bottom: 0.5rem; /* Spacing between detail lines */
        }
         #caseDetailsModal .modal-body strong {
            display: inline-block;
            min-width: 130px; /* Adjust as needed for alignment */
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
                                                <th>Severity</th>
                                                <th>Company</th>
                                                <th>Last Modified</th>
                                                <th>Date Opened</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($newCasesTable as $row) {
                                                // Prepare data for modal attributes (use htmlspecialchars for safety)
                                                $caseId = htmlspecialchars($row["id"]);
                                                $caseNumber = htmlspecialchars($row["case_number"]);
                                                $type = htmlspecialchars($row["type"]);
                                                $subject = htmlspecialchars($row["subject"]);
                                                $productGroup = htmlspecialchars($row["product_group"]);
                                                $product = htmlspecialchars($row["product"]);
                                                $productVersion = htmlspecialchars($row["product_version"]);
                                                $severity = htmlspecialchars($row["severity"]);
                                                $caseOwner = htmlspecialchars($row["case_owner"]);
                                                $company = htmlspecialchars($row["company"]);
                                                $lastModified = htmlspecialchars(date("m/d/y", strtotime($row["last_modified"]))); // Format date here
                                                $datetimeOpened = htmlspecialchars(date("m/d/y h:i:s A", strtotime($row["datetime_opened"]))); // Format date and time here

                                                // View Details Button with all data attributes
                                                $viewDetailsButton = '<button
                                                    type="button"
                                                    class="btn btn-info btn-sm view-details-btn"
                                                    data-toggle="modal"
                                                    data-target="#caseDetailsModal"
                                                    data-id="' . $caseId . '"
                                                    data-case-number="' . $caseNumber . '"
                                                    data-type="' . $type . '"
                                                    data-subject="' . $subject . '"
                                                    data-product-group="' . $productGroup . '"
                                                    data-product="' . $product . '"
                                                    data-product-version="' . $productVersion . '"
                                                    data-severity="' . $severity . '"
                                                    data-case-owner="' . $caseOwner . '"
                                                    data-company="' . $company . '"
                                                    data-last-modified="' . $row["last_modified"] . '" 
                                                    data-datetime-opened="' . $row["datetime_opened"] . '"> 
                                                    <i class="bi bi-eye"></i> View Details
                                                </button>';

                                                // Assign Case Button (from original code)
                                                $assignButton = '<button
                                                    type="button"
                                                    class="btn btn-success btn-sm accept-button"
                                                    data-toggle="modal"
                                                    data-target="#assignCase"
                                                    data-case-id="' . $caseId . '">
                                                    <i class="bi bi-check"></i> Assign
                                                </button>';


                                                echo "<tr>";
                                                echo "<td>" . $caseNumber . "</td>"; // Display case number directly
                                                echo "<td>" . $severity . "</td>";
                                                echo "<td>" . $company . "</td>";
                                                echo "<td>" . $lastModified . "</td>"; // Display formatted date
                                                echo "<td>" . date("m/d/y", strtotime($row["datetime_opened"])) . "</td>"; // Display formatted date
                                                echo "<td class='action-buttons'>" . $viewDetailsButton . $assignButton . "</td>"; // Combine buttons
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

    <?php include_once "../modals/assign_case.php" ?>

    <div class="modal fade" id="caseDetailsModal" tabindex="-1" role="dialog" aria-labelledby="caseDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"> <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="caseDetailsModalLabel">Case Details (<span id="modalCaseNumberHeader"></span>)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Case Number:</strong> <span id="modalCaseNumber"></span></p>
                        <p><strong>Type:</strong> <span id="modalType"></span></p>
                        <p><strong>Subject:</strong> <span id="modalSubject"></span></p>
                        <p><strong>Product Group:</strong> <span id="modalProductGroup"></span></p>
                        <p><strong>Product:</strong> <span id="modalProduct"></span></p>
                        <p><strong>Product Version:</strong> <span id="modalProductVersion"></span></p>
                        <p><strong>Severity:</strong> <span id="modalSeverity"></span></p>
                        <p><strong>Case Owner:</strong> <span id="modalCaseOwner"></span></p>
                        <p><strong>Company:</strong> <span id="modalCompany"></span></p>
                        <p><strong>Last Modified:</strong> <span id="modalLastModified"></span></p>
                        <p><strong>Date & Time Opened:</strong> <span id="modalDatetimeOpened"></span></p>
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
    <script src="../js/form_validation.js"></script> <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>

    <script>
        // Initialize DataTable
        $(document).ready(function() {
             new DataTable('#table');
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- Assign Case Modal Logic (from original code) ---
            const assignCaseModal = document.getElementById("assignCase");
            if (assignCaseModal) { // Check if modal exists
                    const caseIdHidden = document.getElementById("caseId"); // Ensure this exists in assign_case.php modal

                    document.querySelectorAll('.accept-button').forEach(button => {
                        button.addEventListener('click', function(event) {
                             if(caseIdHidden) {
                                  caseIdHidden.value = this.getAttribute('data-case-id');
                             } else {
                                  console.error("Hidden input with ID 'caseId' not found in assignCase modal.");
                             }
                        });
                    });
            } else {
                    console.warn("Assign Case Modal with ID 'assignCase' not found.");
            }


            // --- Case Details Modal Logic ---
             const caseDetailsModal = document.getElementById('caseDetailsModal');
             if(caseDetailsModal) {
                 // Use jQuery's event handling for Bootstrap modals for simplicity, or vanilla JS equivalent
                 $('#caseDetailsModal').on('show.bs.modal', function (event) {
                     const button = event.relatedTarget; // Button that triggered the modal

                     // Extract data from data-* attributes
                     const caseData = {
                         id: button.getAttribute('data-id'),
                         caseNumber: button.getAttribute('data-case-number'),
                         type: button.getAttribute('data-type'),
                         subject: button.getAttribute('data-subject'),
                         productGroup: button.getAttribute('data-product-group'),
                         product: button.getAttribute('data-product'),
                         productVersion: button.getAttribute('data-product-version'),
                         severity: button.getAttribute('data-severity'),
                         caseOwner: button.getAttribute('data-case-owner'),
                         company: button.getAttribute('data-company'),
                         lastModified: button.getAttribute('data-last-modified'),
                         datetimeOpened: button.getAttribute('data-datetime-opened')
                     };

                     // Get the modal's content elements
                     const modal = $(this); // Reference to the modal itself

                     // Populate the modal elements
                     modal.find('#modalCaseNumberHeader').text(caseData.caseNumber || 'N/A');
                     modal.find('#modalCaseNumber').text(caseData.caseNumber || 'N/A');
                     modal.find('#modalType').text(caseData.type || 'N/A');
                     modal.find('#modalSubject').text(caseData.subject || 'N/A');
                     modal.find('#modalProductGroup').text(caseData.productGroup || 'N/A');
                     modal.find('#modalProduct').text(caseData.product || 'N/A');
                     modal.find('#modalProductVersion').text(caseData.productVersion || 'N/A');
                     modal.find('#modalSeverity').text(caseData.severity || 'N/A');
                     modal.find('#modalCaseOwner').text(caseData.caseOwner || 'N/A');
                     modal.find('#modalCompany').text(caseData.company || 'N/A');

                     // Format Last Modified Date
                     const lastModifiedDate = new Date(caseData.lastModified);
                     const lastModifiedFormatted = `${(lastModifiedDate.getMonth() + 1).toString().padStart(2, '0')}/${lastModifiedDate.getDate().toString().padStart(2, '0')}/${lastModifiedDate.getFullYear().toString().slice(-2)}`;
                     modal.find('#modalLastModified').text(lastModifiedFormatted || 'N/A');

                     // Format Date & Time Opened
                     const datetimeOpenedDate = new Date(caseData.datetimeOpened);
                     const datetimeOpenedFormatted = `${(datetimeOpenedDate.getMonth() + 1).toString().padStart(2, '0')}/${datetimeOpenedDate.getDate().toString().padStart(2, '0')}/${datetimeOpenedDate.getFullYear().toString().slice(-2)} ${datetimeOpenedDate.toLocaleTimeString()}`;
                     modal.find('#modalDatetimeOpened').text(datetimeOpenedFormatted || 'N/A');

                      // Populate other spans similarly...
                 });
             } else {
                     console.warn("Case Details Modal with ID 'caseDetailsModal' not found.");
             }

        });
    </script>

</body>

</html>