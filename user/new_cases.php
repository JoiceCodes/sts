<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php"); // Redirect to login page if not logged in
    exit;
}

$pageTitle = "New Cases"; // Title for the page

// Fetch the data for the table - Ensure this path is correct!
// This script should connect to the DB and populate $newCasesTable array
// It needs to fetch columns like: id, case_number, case_owner, severity, company, datetime_opened,
// type, subject, product_group, product, product_version, last_modified
// CRITICAL: Make sure this script successfully fetches the 'id' column for each case.
require_once "../fetch/new_cases_table_user.php"; // << MAKE SURE THIS SCRIPT WORKS and includes 'id'

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" // << Includes meta tags, base CSS ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <?php // Consider adding FontAwesome locally ?>

    <style>
        /* Style for the details modal */
        #detailsModal .modal-body p,
        #detailsModal .modal-body div { /* Apply to divs too */
            margin-bottom: 0.5rem;
        }
        #detailsModal .modal-body strong {
            min-width: 150px; /* Adjust label width as needed */
            display: inline-block;
        }
        #detailsModal .modal-body #case-history-content strong {
             min-width: auto; /* Reset min-width for history section if needed */
             display: inline; /* Or block if preferred */
        }
        #detailsModal .modal-body #modal-history-actions,
        #detailsModal .modal-body #modal-history-mom {
             padding-left: 1.5rem; /* Indent multiline history items */
             margin-top: -0.5rem; /* Adjust spacing */
             white-space: pre-wrap; /* Allow wrapping and preserve newlines */
        }

        /* Style for validation feedback icons in the new case modal (Optional but good UX) */
        /* Using Bootstrap 4's default validation styles - no extra icons needed unless desired */
        .needs-validation .form-control:valid {}
        .needs-validation .form-control:invalid {}
        .needs-validation select.form-control:valid {}
        .needs-validation select.form-control:invalid {}
        .was-validated .form-control:invalid ~ .invalid-feedback,
        .was-validated .form-control:invalid ~ .invalid-tooltip { display: block; }
        .was-validated select.form-control:invalid ~ .invalid-feedback,
        .was-validated select.form-control:invalid ~ .invalid-tooltip { display: block; }
        /* Spinner alignment */
        #createCaseBtn .spinner-border-sm { vertical-align: text-bottom; }

        /* Ensure table actions don't wrap unnecessarily */
         #table td:last-child { white-space: nowrap; }

    </style>
</head>

<body id="page-top">
    <div id="wrapper">

        <?php include_once "../components/sidebar.php" // << Your navigation sidebar ?>

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <?php include_once "../components/user_topbar.php" // << Your top navigation bar ?>

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                    </div>

                    <div class="my-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newCase">
                            <i class="fas fa-plus fa-sm text-white-50"></i> New Case
                        </button>
                    </div>

                    <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> New case submitted successfully!
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET["error"])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> Error: <?= htmlspecialchars(urldecode($_GET["error"])) ?>
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
                                <table class="table table-bordered table-hover" id="table" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Case Number</th>
                                            <th>Severity</th>
                                            <th>Company</th>
                                            <th>Date Opened</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Check if $newCasesTable is set, is an array, and not empty
                                        // $newCasesTable should be populated by "../fetch/new_cases_table_user.php"
                                        if (isset($newCasesTable) && is_array($newCasesTable) && count($newCasesTable) > 0) {
                                            foreach ($newCasesTable as $row) {
                                                // --- Sanitize data before outputting ---
                                                // CRITICAL: Ensure 'id' is fetched and available in $row
                                                $caseId = htmlspecialchars($row["id"] ?? ''); // << Ensure you fetch the primary ID
                                                if (empty($caseId)) {
                                                     // Optionally log an error or skip row if ID is missing
                                                     error_log("Missing case ID in row data for table population.");
                                                     continue; // Skip this row if ID is essential and missing
                                                }

                                                $caseNum = htmlspecialchars($row["case_number"] ?? 'N/A');
                                                $caseOwner = htmlspecialchars($row["case_owner"] ?? ''); // Potentially needed later
                                                $severity = htmlspecialchars($row["severity"] ?? 'N/A');
                                                $company = htmlspecialchars($row["company"] ?? 'N/A');
                                                $dateTimeOpenedRaw = $row["datetime_opened"] ?? ''; // Keep raw for potential use
                                                $lastModifiedRaw = $row["last_modified"] ?? ''; // Keep raw for potential use

                                                // --- Prepare data for the details modal from the current row ---
                                                $type = htmlspecialchars($row["type"] ?? 'N/A');
                                                $subject = htmlspecialchars($row["subject"] ?? 'N/A');
                                                $productGroup = htmlspecialchars($row["product_group"] ?? 'N/A');
                                                $product = htmlspecialchars($row["product"] ?? 'N/A');
                                                $productVersion = htmlspecialchars($row["product_version"] ?? 'N/A');
                                                $type = htmlspecialchars($row["created_at"] ?? 'N/A');

                                                // --- Format dates for display ---
                                                // Using "M d, Y H:i" format. Change if needed.
                                                $displayDateTimeOpened = 'N/A';
                                                if (!empty($dateTimeOpenedRaw) && $dateTimeOpenedRaw !== 'N/A') {
                                                    $timestamp = strtotime($dateTimeOpenedRaw);
                                                    if ($timestamp) {
                                                        $displayDateTimeOpened = date("M d, Y H:i", $timestamp);
                                                    }
                                                }

                                                $displayLastModified = 'N/A';
                                                 if (!empty($lastModifiedRaw) && $lastModifiedRaw !== 'N/A') {
                                                    $timestamp = strtotime($lastModifiedRaw);
                                                    if ($timestamp) {
                                                        $displayLastModified = date("M d, Y H:i", $timestamp);
                                                    }
                                                }

                                                // --- Output table row ---
                                                echo "<tr>";
                                                // Case Number (Link-like style)
                                                echo '<td><span class="font-weight-bold text-primary">' . $caseNum . '</span></td>';
                                                echo "<td>" . $severity . "</td>";
                                                echo "<td>" . $company . "</td>";
                                                echo "<td>" . $displayDateTimeOpened . "</td>"; // Use formatted date

                                                // Action Buttons
                                                echo '<td class="text-center">'; // Center align actions

                                                // View Details Button - Pass all necessary data
                                                // Ensure data-case-id has a valid value
                                                echo '<button type="button" class="btn btn-info btn-sm view-details-btn mx-1"
                                                        data-toggle="modal"
                                                        data-target="#detailsModal"
                                                        data-case-id="' . $caseId . '"
                                                        data-case-number="' . $caseNum . '"
                                                        data-type="' . $type . '"
                                                        data-subject="' . $subject . '"
                                                        data-product-group="' . $productGroup . '"
                                                        data-product="' . $product . '"
                                                        data-product-version="' . $productVersion . '"
                                                        data-last-modified="' . $displayLastModified . '"
                                                        data-severity="' . $severity . '"
                                                        data-company="' . $company . '"
                                                        data-datetime-opened="' . $displayDateTimeOpened . '">
                                                        <i class="fas fa-eye"></i> <span class="d-none d-sm-inline">View</span>
                                                    </button>';

                                                // Placeholder for Accept Button (if needed for this user role)
                                                // echo ' <button type="button" class="btn btn-success btn-sm accept-button mx-1" data-toggle="modal" data-target="#acceptCase" data-case-id="'.$caseId.'"><i class="fas fa-check"></i> Accept</button>';

                                                echo '</td>'; // End Actions column
                                                echo "</tr>"; // End table row
                                            } // End foreach loop
                                        } else {
                                            // Display a message if no cases are found
                                            echo '<tr><td colspan="5" class="text-center">No new cases found matching your criteria.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div> </div> </div> </div> </div> <?php include_once "../components/footer.php" // << Your footer content ?>

        </div> </div> <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include_once "../modals/logout.php" // << Logout confirmation modal ?>
    <?php include_once "../modals/new_case.php" // << Modal with the form for creating a new case ?>
    <?php // include_once "../modals/accept_case.php" // << Include if you have an Accept Case modal ?>


    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Case Details (<span id="modal-case-number"></span>)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Severity:</strong> <span id="modal-severity"></span></p>
                    <p><strong>Company:</strong> <span id="modal-company"></span></p>
                    <p><strong>Date & Time Opened:</strong> <span id="modal-datetime-opened"></span></p>
                    <hr>
                    <p><strong>Type:</strong> <span id="modal-type"></span></p>
                    <p><strong>Subject:</strong> <span id="modal-subject"></span></p>
                    <p><strong>Product Group:</strong> <span id="modal-product-group"></span></p>
                    <p><strong>Product:</strong> <span id="modal-product"></span></p>
                    <p><strong>Product Version:</strong> <span id="modal-product-version"></span></p>
                    <p><strong>Last Modified:</strong> <span id="modal-last-modified"></span></p>
                    <hr>
                    <h5 class="mt-3">Case History</h5>
                    <div id="case-history-content">
                        <p id="history-loading" style="display: none;"><i class="fas fa-spinner fa-spin"></i> Loading history...</p>
                        <p id="history-error" class="text-danger" style="display: none;">Could not load case history.</p>

                        <p><strong>Created time:</strong> <span id="modal-history-created-time">N/A</span> by ...</p>                        <p><strong>Assigned By:</strong> <span id="modal-history-assigned-by">N/A</span></p>
                        <p><strong>Reassigned By:</strong> <span id="modal-history-reassigned-by">N/A</span></p>
                        <p><strong>Escalated By:</strong> <span id="modal-history-escalated-by">N/A</span></p>
                        <div><strong>Action Taken:</strong> <div id="modal-history-actions">N/A</div></div>
                        <div><strong>Minutes of Meeting (Response):</strong> <div id="modal-history-mom">N/A</div></div>
                        <p><strong>Reopened:</strong> <span id="modal-history-reopened">N/A</span></p>
                         </div>
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
        // Encapsulate all custom JS within jQuery's document ready
        $(document).ready(function() {

            // --- Initialize DataTable ---
            var dataTable = new DataTable('#table', {
                "order": [
                    [3, "desc"] // Order by the 4th column (Date Opened, index 3), descending
                ],
                 // Customize language if needed (especially for empty table message)
                "language": {
                     "emptyTable": "No new cases found matching your criteria.", // Message when table is empty after filtering/fetch
                     "zeroRecords": "No matching cases found" // Message when filtering yields no results
                },
                // "responsive": true // Enable if you need responsive collapsing on smaller screens
            });

            // --- Details Modal Logic ---
            $('#table tbody').on('click', '.view-details-btn', function() {
                var button = $(this);
                var caseId = button.data('case-id'); // Get the case ID

                 // Simple validation: Check if caseId is present
                if (!caseId) {
                    console.error("View Details Error: Case ID is missing from the button's data attribute.");
                    alert("Cannot load details: Case ID is missing.");
                    return; // Stop execution if no ID
                }

                // Populate the basic details modal fields directly from data attributes
                $('#modal-case-number').text(button.data('case-number') || 'N/A');
                $('#modal-type').text(button.data('type') || 'N/A');
                $('#modal-subject').text(button.data('subject') || 'N/A');
                $('#modal-product-group').text(button.data('product-group') || 'N/A');
                $('#modal-product').text(button.data('product') || 'N/A');
                $('#modal-product-version').text(button.data('product-version') || 'N/A');
                $('#modal-last-modified').text(button.data('last-modified') || 'N/A');
                $('#modal-severity').text(button.data('severity') || 'N/A');
                $('#modal-company').text(button.data('company') || 'N/A');
                $('#modal-datetime-opened').text(button.data('datetime-opened') || 'N/A');

                // --- Fetch and Display Case History ---
                var historyContent = $('#case-history-content');
                var historyLoading = $('#history-loading');
                var historyError = $('#history-error');

                // Reset history section before fetching
                historyContent.find('span').text('N/A'); // Reset all spans in history
                $('#modal-history-actions').html('N/A'); // Use html() to clear potential old HTML
                $('#modal-history-mom').html('N/A');
                // Ensure all history fields are reset
// Inside the AJAX success function:
$('#modal-history-created-time').text(formatDateTime(history.created_time));                $('#modal-history-created-by').text('N/A');
                $('#modal-history-assigned-by').text('N/A');
                $('#modal-history-reassigned-by').text('N/A');
                $('#modal-history-escalated-by').text('N/A');
                $('#modal-history-reopened').text('N/A');

                historyError.hide();
                historyLoading.show(); // Show loading indicator

                 // Helper function to format date/time - adjust format as needed
                 const formatDateTime = (dateTimeStr) => {
                     // Check for null, undefined, 'N/A', or invalid default SQL date values
                     if (!dateTimeStr || dateTimeStr === 'N/A' || dateTimeStr === '0000-00-00 00:00:00' || dateTimeStr.startsWith('0001-')) {
                         return 'N/A';
                     }
                     try {
                         const date = new Date(dateTimeStr.replace(' ', 'T') + 'Z'); // Attempt to parse as UTC if no timezone specified
                         // Check if the date is valid after parsing
                         if (isNaN(date.getTime())) {
                             // Try parsing without forcing UTC if the first attempt failed
                             const dateLocal = new Date(dateTimeStr);
                             if (isNaN(dateLocal.getTime())){
                                 console.warn("Could not parse date/time for formatting:", dateTimeStr);
                                 return 'Invalid Date'; // Indicate parsing failure
                             }
                              // Use local time if UTC parse failed but local succeeded
                             return dateLocal.toLocaleString('en-US', { // Using en-US locale example
                                 year: 'numeric', month: 'short', day: 'numeric',
                                 hour: 'numeric', minute: '2-digit', hour12: true
                             });
                         }
                         // Use UTC parsed date if valid
                         return date.toLocaleString('en-US', { // Using en-US locale example
                             year: 'numeric', month: 'short', day: 'numeric',
                             hour: 'numeric', minute: '2-digit', hour12: true //, timeZone: 'UTC' // Optionally display as UTC
                         });
                     } catch (e) {
                         console.error("Date formatting error:", e, "Input:", dateTimeStr);
                         return dateTimeStr; // Return original if error during formatting
                     }
                 };


                // *** AJAX Call to fetch history ***
                console.log("Fetching history for case ID:", caseId); // Debugging
                $.ajax({
                    url: '../fetch/fetch_case_history.php', // <<< ENSURE THIS FILE EXISTS AND IS CORRECT
                    method: 'GET',
                    data: { case_id: caseId },
                    dataType: 'json',
                    success: function(response) {
                        historyLoading.hide(); // Hide loading
                        console.log("Raw AJAX Response:", response); // <<< ADD THIS FOR DEBUGGING

                        if (response && response.success && response.history) {
                            const history = response.history;
                            console.log("History data object received:", history); // Debugging: Check the received object structure

                            // --- Populate history fields from the AJAX response ---
                            $('#modal-history-created-time').text(formatDateTime(history.created_time)); // Use formatter
                            $('#modal-history-created-by').text(history.created_by || 'N/A');
                            $('#modal-history-assigned-by').text(history.assigned_by || 'N/A');
                            $('#modal-history-reassigned-by').text(history.reassigned_by || 'N/A');
                            $('#modal-history-escalated-by').text(history.escalated_by || 'N/A');
                            $('#modal-history-actions').html(history.actions_taken || 'N/A');
                            $('#modal-history-mom').html(history.mom_response || 'N/A');
                            $('#modal-history-reopened').text(history.reopened_details || 'N/A');

                        } else {
                            // Handle case where AJAX succeeded but backend reported failure or no data
                            console.warn("Failed to get valid history data:", response ? response.message : 'No response object');
                            historyError.text(response && response.message ? response.message : 'No history data found for this case.').show();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        historyLoading.hide(); // Hide loading
                        console.error("AJAX Error fetching history:", textStatus, errorThrown, jqXHR.responseText); // Log response text for more detail
                        historyError.text('Error loading case history. Status: ' + textStatus + '. Please check network tab or server logs.').show(); // Show error message
                    }
                });

                // Modal is shown automatically by Bootstrap's data-toggle/data-target attributes
            });


            // --- Accept Case Modal Logic (Placeholder - uncomment/adapt if needed) ---
            /*
            $('#table tbody').on('click', '.accept-button', function() {
                var caseId = $(this).data('case-id');
                $('#acceptCaseIdInput').val(caseId); // Assuming an input field in your #acceptCase modal
            });
            */


            // --- New Case Modal Logic ---
            const newCaseModal = $("#newCase");
            if (newCaseModal.length) {
                 const newCaseForm = $("#newCaseForm"); // Assuming form ID is newCaseForm
                 const createCaseBtn = $("#createCaseBtn"); // Assuming submit button ID

                 if (!newCaseForm.length ) {
                     console.error("One or more required elements within the #newCase modal form are missing.");
                     if(createCaseBtn.length) createCaseBtn.prop('disabled', true).text('Form Error');
                 } else {
                     // --- Your specific JS logic for the New Case modal would go here ---
                     // console.log("New Case Modal JS Initialized");
                 }
            }


            // --- Auto-dismiss success/error alert ---
            window.setTimeout(function() {
                $(".alert-success, .alert-danger").fadeTo(500, 0).slideUp(500, function() {
                    $(this).remove();
                    // Optional: Clean URL query string after dismissing alert
                    if (window.history.replaceState) {
                        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
                    }
                });
            }, 5000); // 5 seconds timeout


        }); // --- End $(document).ready ---
    </script>

</body>

</html>