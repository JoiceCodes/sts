<?php
// Start session (if not already started) - Good practice at the very top
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Configuration & Includes ---
$pageTitle = "New Cases";

// Ensure these paths are correct relative to the current file's location
require_once "../fetch/new_cases.php"; // Fetches $newCasesTable
require_once "../fetch/engineers.php"; // Fetches $engineers (used in assign_case.php modal)

// --- Authentication/Authorization Check (Example - Adapt as needed) ---
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
//     header("Location: /login.php"); // Redirect to login if not logged in or wrong role
//     exit;
// }

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    // Standard head elements (meta, title, base CSS)
    // Ensure this path is correct
    include_once "../components/head.php";
    ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.min.css">
    <style>
        /* Optional: Add some spacing for buttons if needed */
        .table td .btn {
            margin-right: 5px;
            /* Adjust as necessary */
        }

        .table td .btn:last-child {
            margin-right: 0;
        }

        /* Ensure modal content doesn't get cut off */
        .modal-body p {
            margin-bottom: 0.8rem;
            word-wrap: break-word;
            /* Prevent long strings from breaking layout */
        }
        /* Style for subject text */
        #modalSubject {
             white-space: pre-wrap; /* Allows line breaks and wraps text */
             word-break: break-word; /* Breaks long words if necessary */
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">

        <?php
        // Ensure this path is correct
        include_once "../components/sidebar.php";
        ?>
        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <?php
                // Ensure this path is correct
                include_once "../components/administrator_topbar.php";
                ?>
                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                    </div>

                    <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> Case assigned successfully! Go to <a href="ongoing_cases.php" class="alert-link">On-going Cases</a>.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET["error"])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> An error occurred: <?= htmlspecialchars(urldecode($_GET["error"])) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>


                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Available Cases</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Case Number</th>
                                            <th>Type</th>
                                            <th>Severity</th>
                                            <th>Company</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        // Check if $newCasesTable is set and is an array
                                        if (isset($newCasesTable) && is_array($newCasesTable)) {
                                            foreach ($newCasesTable as $row) {
                                                // Ensure required keys exist, provide defaults if necessary
                                                $case_id = $row["id"] ?? 'N/A';
                                                $case_number = $row["case_number"] ?? 'N/A';
                                                $type = $row["type"] ?? 'N/A';
                                                $subject = $row["subject"] ?? 'N/A';
                                                $product_group = $row["product_group"] ?? 'N/A';
                                                $product = $row["product"] ?? 'N/A';
                                                $product_version = $row["product_version"] ?? 'N/A';
                                                $severity = $row["severity"] ?? 'N/A';
                                                $case_owner = $row["case_owner"] ?? 'N/A';
                                                $company = $row["company"] ?? 'N/A';

                                                // --- Create ONLY the View Details Button for the table row ---
                                                $viewDetailsButton = '<button
                                                                        type="button"
                                                                        class="view-details-btn btn btn-info btn-sm"
                                                                        data-toggle="modal"
                                                                        data-target="#caseDetailsModal"
                                                                        data-case-number="' . htmlspecialchars($case_number, ENT_QUOTES) . '"
                                                                        data-type="' . htmlspecialchars($type, ENT_QUOTES) . '"
                                                                        data-subject="' . htmlspecialchars($subject, ENT_QUOTES) . '"
                                                                        data-product-group="' . htmlspecialchars($product_group, ENT_QUOTES) . '"
                                                                        data-product="' . htmlspecialchars($product, ENT_QUOTES) . '"
                                                                        data-product-version="' . htmlspecialchars($product_version, ENT_QUOTES) . '"
                                                                        data-severity="' . htmlspecialchars($severity, ENT_QUOTES) . '"
                                                                        data-case-owner="' . htmlspecialchars($case_owner, ENT_QUOTES) . '"
                                                                        data-company="' . htmlspecialchars($company, ENT_QUOTES) . '"
                                                                        data-case-id="' . htmlspecialchars($case_id, ENT_QUOTES) . '"
                                                                        title="View Details">
                                                                            <i class="fas fa-eye"></i> View
                                                                        </button>';

                                                // --- Assign button is removed from here ---
                                                // $assignCaseButton = '...'; // No longer needed here

                                                // --- Set actions to ONLY the view button ---
                                                $actions = $viewDetailsButton; // Only the view button

                                                // --- Output table row ---
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($case_number, ENT_QUOTES) . "</td>"; // Display Case Number
                                                echo "<td>" . htmlspecialchars($type, ENT_QUOTES) . "</td>";       // Display Type
                                                echo "<td>" . htmlspecialchars($severity, ENT_QUOTES) . "</td>";   // Display Severity
                                                echo "<td>" . htmlspecialchars($company, ENT_QUOTES) . "</td>";     // Display Company
                                                echo "<td class='text-nowrap'>" . $actions . "</td>"; // Display action button(s), text-nowrap prevents wrapping
                                                echo "</tr>";
                                            }
                                        } else {
                                            // Handle case where $newCasesTable isn't set or is empty
                                            echo '<tr><td colspan="5" class="text-center">No new cases found.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php
            // Ensure this path is correct
            include_once "../components/footer.php";
            ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php
    // Ensure this path is correct
    include_once "../modals/logout.php";
    ?>

    <?php
    // Ensure this path is correct and it contains the necessary form elements including '<input type="hidden" name="case_id" id="caseId">'
    // This modal will now be triggered FROM the Case Details modal
    include_once "../modals/assign_case.php";
    ?>

    <div class="modal fade" id="caseDetailsModal" tabindex="-1" role="dialog" aria-labelledby="caseDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="caseDetailsModalLabel">Case Details (<span id="modalCaseNumberHeader"></span>)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Case Number:</strong> <span id="modalCaseNumber"></span></p>
                            <p><strong>Type:</strong> <span id="modalType"></span></p>
                            <p><strong>Severity:</strong> <span id="modalSeverity"></span></p>

                        </div>
                        <div class="col-md-6">
                            <p><strong>Company:</strong> <span id="modalCompany"></span></p>
                            <p><strong>Case Owner:</strong> <span id="modalCaseOwner"></span></p>
                             <p><strong>Product Version:</strong> <span id="modalProductVersion"></span></p>
                        </div>
                    </div>
                     <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Product Group:</strong> <span id="modalProductGroup"></span></p>
                        </div>
                         <div class="col-md-6">
                             <p><strong>Product:</strong> <span id="modalProduct"></span></p>
                         </div>
                    </div>
                    <hr>
                    <p><strong>Subject:</strong></p>
                    <p><span id="modalSubject"></span></p> </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button"
                            class="accept-button btn btn-success"
                            data-toggle="modal"
                            data-target="#assignCase"
                            data-case-id=""  
                            title="Assign Case">
                        <i class="fas fa-user-plus"></i> Assign
                    </button>
                    </div>
            </div>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <?php
    // Conditionally include form validation if needed for other forms on the page
    // <script src="../js/form_validation.js"></script>
    ?>

    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.min.js"></script>


    <script>
        $(document).ready(function() { // Use jQuery's ready function for consistency
            // Initialize DataTable
            $('#dataTable').DataTable({
                "pageLength": 10, // Example: Set default page length
                "order": [
                    [0, "desc"]
                ] // Example: Default sort by Case Number descending
            });

            // --- Assign Case Modal Logic ---
            // This handles setting the hidden input field when the "Assign" button
            // (which is now ONLY inside the #caseDetailsModal footer) is clicked.
            const caseIdHidden = document.getElementById("caseId"); // Get the hidden input once

            // Use event delegation on the *details modal* to catch clicks on its accept button
            $('#caseDetailsModal').on('click', '.accept-button', function() {
                const caseId = $(this).data('case-id'); // Get case ID from the button clicked INSIDE details modal
                if (caseIdHidden) {
                    caseIdHidden.value = caseId;
                } else {
                    console.error("Element with ID 'caseId' not found in the assign case modal.");
                    alert("Error: Cannot set case ID for assignment."); // User feedback
                }
                 // No need to manually show the #assignCase modal here,
                 // as data-toggle="modal" and data-target="#assignCase" on the button handle it.

                 // OPTIONAL: Hide the details modal when assign modal opens
                 // $('#caseDetailsModal').modal('hide');
            });


            // --- Case Details Modal Logic ---
            // This populates the details modal when a "View" button in the table is clicked.
            const detailsModal = $('#caseDetailsModal'); // Use jQuery selector

            // Use event delegation for the details button in the table
            $('#dataTable tbody').on('click', '.view-details-btn', function() {
                const buttonData = $(this).data(); // Get all data-* attributes as an object

                // Populate modal fields using jQuery
                detailsModal.find('#modalCaseNumberHeader').text(buttonData.caseNumber);
                detailsModal.find('#modalCaseNumber').text(buttonData.caseNumber);
                detailsModal.find('#modalType').text(buttonData.type);
                detailsModal.find('#modalSeverity').text(buttonData.severity);
                detailsModal.find('#modalCompany').text(buttonData.company);
                detailsModal.find('#modalCaseOwner').text(buttonData.caseOwner);
                detailsModal.find('#modalProductGroup').text(buttonData.productGroup);
                detailsModal.find('#modalProduct').text(buttonData.product);
                detailsModal.find('#modalProductVersion').text(buttonData.productVersion);
                detailsModal.find('#modalSubject').text(buttonData.subject); // Display subject text

                // Set the case ID on the "Assign" button INSIDE the modal footer
                detailsModal.find('.accept-button').data('case-id', buttonData.caseId); // Use .data() to set it

                 // No need to manually show the details modal if using data-toggle="modal" data-target="..." on the table button
                 // detailsModal.modal('show'); // This would be redundant
            });

        });
    </script>

</body>

</html>