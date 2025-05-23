<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "On-going Cases";
// Assume this file fetches $ongoingCasesTable array including 'id', 'priority', 'severity', 'is_escalated', etc.
// Ensure this path is correct and the file fetches all necessary columns.
require_once "../fetch/ongoing_cases.php";

$currentUserFullName = $_SESSION['user_full_name'] ?? 'Engineer';
$currentUserId = $_SESSION['user_id'];

// Helper function to get severity text based on priority number
function getSeverityTextFromPriority($priority) {
    switch ($priority) {
        case 1: return 'Production System Down';
        case 2: return 'Restricted Operations';
        case 3: return 'System Impaired';
        case 4: return 'General Guidance';
        default: return 'Unknown Priority'; // Fallback text
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <style>
        /* Chat Messages Container Styling */
        #chatMessages { display: flex; flex-direction: column; gap: 10px; padding: 10px; overflow-y: auto; max-height: 300px; background-color: #f8f9fa; border: 1px solid #ccc; border-radius: 5px; }
        /* Common styling for all chat messages */
        .chat-message { max-width: 80%; word-wrap: break-word; padding: 10px 15px; border-radius: 15px; position: relative; line-height: 1.4; }
        /* Sender's message bubble */
        .message-sender { align-self: flex-end; background-color: #007bff; color: white; text-align: left; }
        /* Receiver's message bubble */
        .message-receiver { align-self: flex-start; background-color: #e9ecef; color: black; text-align: left; }
        /* Timestamp styling */
        .message-time { font-size: 11px; color: rgba(255, 255, 255, 0.75); text-align: right; margin-top: 5px; display: block; }
        .message-receiver .message-time { color: rgba(0, 0, 0, 0.6); }
        /* Style for definition terms (dt) */
        #detailsModal .modal-body dt { font-weight: bold; color: #5a5c69; }
        /* Style for definition descriptions (dd) */
        #detailsModal .modal-body dd { margin-bottom: 0.75rem; }
        /* Style for chat trigger link */
        .case-chat-trigger { cursor: pointer; }
        /* Style for action buttons in table */
        #casesTable .btn { margin-right: 5px; }
        #casesTable .btn:last-child { margin-right: 0; }
        /* Align modal footer buttons */
        #detailsModal .modal-footer { justify-content: space-between; }
        /* Style for Escalated Badge */
        .badge-escalated { background-color: #e74a3b; color: white; font-size: 0.75em; padding: 0.3em 0.5em; vertical-align: middle;}
        /* Styles for Case History section within the details modal */
        #detailsModal #ongoing-case-history-content { /* Container for loaded history */ }
        /* Styling for history list */
        .dl-history dt { font-weight: normal; color: #6c757d; padding-right: 5px;} /* History labels lighter */
        .dl-history dd { margin-left: 0; padding-left: 0; margin-bottom: 0.5rem; font-size: 0.9em; color: #5a5c69;} /* History values */
        .list-group-history { font-size: 0.85rem; }
        .list-group-history .list-group-item { padding: 0.4rem 0.5rem; border: none; background-color: transparent; }
        .list-group-history strong { color: #4e73df; } /* Action type color */
        .list-group-history em { color: #858796; font-size: 0.9em; } /* User/Time color */

        #detailsModal #history-loading-ongoing,
        #detailsModal #history-error-ongoing { text-align: center; font-style: italic; color: #858796; margin: 1rem 0; }
        #detailsModal #history-error-ongoing { color: #e74a3b; }

        /* Align checkbox in header/cells */
         #casesTable th:first-child, #casesTable td:first-child { text-align: center; vertical-align: middle; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php" ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/engineer_topbar.php" ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                    </div>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($_SESSION['success_message']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php elseif (isset($_SESSION['warning_message'])): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_SESSION['warning_message']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <?php unset($_SESSION['warning_message']); ?>
                    <?php elseif (isset($_SESSION['error_message'])): ?>
                         <div class="alert alert-danger alert-dismissible fade show" role="alert">
                             <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_SESSION['error_message']); ?>
                             <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                         </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php else: // Fallback to GET parameters if no session message is set ?>
                         <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                             <div class="alert alert-success alert-dismissible fade show" role="alert">
                                 <i class="bi bi-check-circle-fill"></i> Case solved successfully! Go to <a href="solved_cases.php" class="alert-link">Solved Cases</a>.
                                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                             </div>
                         <?php elseif (isset($_GET["escalate_status"]) && $_GET["escalate_status"] === "1"): ?>
                             <div class="alert alert-success alert-dismissible fade show" role="alert">
                                 <i class="bi bi-check-circle-fill"></i> Case priority updated successfully!
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                             </div>
                          <?php elseif (isset($_GET["escalate_status"]) && $_GET["escalate_status"] === "nochange"): ?>
                             <div class="alert alert-info alert-dismissible fade show" role="alert">
                                 <i class="bi bi-info-circle-fill"></i> Priority not changed as the selected level was the same as the current level.
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                             </div>
                         <?php elseif (isset($_GET["error"])): ?>
                              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                  <i class="bi bi-exclamation-triangle-fill"></i> An error occurred (Code: <?= htmlspecialchars($_GET["error"]) ?>). Please check details or contact support.
                                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                             </div>
                         <?php endif; ?>
                    <?php endif; ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($pageTitle) ?> Table</h6>
                        </div>
                        <div class="card-body">
                             <div class="mb-3">
                                 <button id="bulkMarkSolvedBtn" class="btn btn-success btn-sm" disabled>
                                     <i class="bi bi-check-lg"></i> Mark Selected as Solved
                                 </button>
                             </div>
                             <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="casesTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                             <th style="width: 30px;">
                                                 <input type="checkbox" id="selectAllCheckbox" title="Select/Deselect All on Page">
                                             </th>
                                             <th>Case Number</th>
                                            <th>Severity</th>
                                            <th>Case Owner</th>
                                            <th>Company</th>
                                            <th>Details & Actions</th>
                                            <th>Last Modified</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Ensure the fetched data variable exists and is an array
                                        if (isset($ongoingCasesTable) && is_array($ongoingCasesTable)) {
                                            // Check if there are any cases
                                            if (count($ongoingCasesTable) > 0) {
                                                foreach ($ongoingCasesTable as $row) {
                                                    // --- Get current data ---
                                                    $caseId = $row["id"] ?? null; // Get Case ID for checkbox value
                                                    $priority = $row["priority"] ?? null;
                                                    $isEscalated = $row['is_escalated'] ?? 0;
                                                    $caseNumber = $row["case_number"] ?? 'N/A';
                                                    $caseOwner = $row["case_owner"] ?? 'N/A';
                                                    $company = $row["company"] ?? 'N/A';
                                                    $type = $row["type"] ?? 'N/A';
                                                    $subject = $row["subject"] ?? 'N/A';
                                                    $productGroup = $row["product_group"] ?? 'N/A';
                                                    $product = $row["product"] ?? 'N/A';
                                                    $productVersion = $row["product_version"] ?? 'N/A';

                                                    // --- Derive Severity Text from Current Priority ---
                                                    $severityText = getSeverityTextFromPriority($priority);
                                                    $displaySeverity = '';
                                                    if ($priority !== null && is_numeric($priority)) {
                                                        $displaySeverity = "P" . htmlspecialchars($priority) . " - " . htmlspecialchars($severityText);
                                                    } else {
                                                        $originalSeverityText = $row["severity"] ?? 'N/A'; // Use original DB value only as fallback
                                                        $displaySeverity = "P? - " . htmlspecialchars($originalSeverityText);
                                                    }

                                                    // --- Format Date ---
                                                    $displayLastModified = isset($row["last_modified"]) ? date("m/d/Y", strtotime($row["last_modified"])) : 'N/A';

                                                    // --- Prepare data for modal/button attributes ---
                                                    $caseData = [
                                                        'id' => $caseId,
                                                        'case-number' => $caseNumber,
                                                        'type' => $type,
                                                        'subject' => $subject,
                                                        'product-group' => $productGroup,
                                                        'product' => $product,
                                                        'product-version' => $productVersion,
                                                        'severity-text' => $severityText,      // Text based on current priority
                                                        'severity-display' => $displaySeverity, // Px - Text based on current priority
                                                        'priority' => $priority,              // Current numeric priority
                                                        'is-escalated' => $isEscalated,
                                                        'case-owner' => $caseOwner,
                                                        'company' => $company,
                                                        'last-modified' => $displayLastModified
                                                    ];
                                                    $dataAttributes = '';
                                                     foreach ($caseData as $key => $value) {
                                                        if (is_scalar($value) || is_null($value)) {
                                                            $dataAttributes .= ' data-' . $key . '="' . htmlspecialchars((string)$value) . '"';
                                                        }
                                                     }


                                                    // --- Buttons ---
                                                    $caseChatTrigger = '<a href="#" class="case-chat-trigger font-weight-bold" data-case-number="'.htmlspecialchars($caseNumber).'" data-case-owner="'.htmlspecialchars($caseOwner).'" data-toggle="modal" data-target="#chatModal">' . htmlspecialchars($caseNumber) . '</a>';
                                                    $viewDetailsActionsButton = '<button type="button" class="btn btn-info btn-sm view-details-btn" '.$dataAttributes.' data-toggle="modal" data-target="#detailsModal"><i class="fas fa-folder-open"></i> View / Actions</button>';


                                                    // --- Output Row ---
                                                    echo "<tr>";
                                                    // Add Checkbox Cell
                                                    echo '<td class="text-center align-middle"><input type="checkbox" class="case-checkbox" value="' . htmlspecialchars($caseId) . '"></td>';
                                                    // End Checkbox Cell
                                                    echo "<td class='align-middle'>" . $caseChatTrigger . "</td>";
                                                    echo "<td class='align-middle'>";
                                                    echo htmlspecialchars($displaySeverity);
                                                    if ($isEscalated) {
                                                        echo ' <span class="badge badge-escalated ml-1">Escalated</span>';
                                                    }
                                                    echo "</td>";
                                                    echo "<td class='align-middle'>" . htmlspecialchars($caseOwner) . "</td>";
                                                    echo "<td class='align-middle'>" . htmlspecialchars($company) . "</td>";
                                                    echo "<td class='align-middle'>" . $viewDetailsActionsButton . "</td>";
                                                    echo "<td class='align-middle'>" . $displayLastModified . "</td>";
                                                    echo "</tr>";
                                                } // End foreach
                                            } else {
                                                // No cases found
                                                $colspan = 7; // Adjusted colspan
                                                echo '<tr><td colspan="' . $colspan . '" class="text-center">No on-going cases found.</td></tr>';
                                            }
                                        } else {
                                            // Error fetching cases
                                            $colspan = 7; // Adjusted colspan
                                            echo '<tr><td colspan="' . $colspan . '" class="text-center text-danger">Error loading case data.</td></tr>';
                                            error_log("Error: ongoingCasesTable variable is not set or not an array in ongoing_cases.php");
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> </div> <?php include_once "../components/footer.php" ?>
        </div> </div> <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include_once "../modals/logout.php" ?>
    <?php include_once "../modals/mark_as_solved.php" ?>
    <?php include_once "../modals/escalate_severity.php" ?>

    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Case Details & Actions</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6>Case Information</h6>
                    <dl class="row mb-4">
                        <dt class="col-sm-3">Case Number:</dt><dd class="col-sm-9" id="modalCaseNumber"></dd>
                        <dt class="col-sm-3">Type:</dt><dd class="col-sm-9" id="modalType"></dd>
                        <dt class="col-sm-3">Subject:</dt><dd class="col-sm-9" id="modalSubject"></dd>
                        <dt class="col-sm-3">Product Group:</dt><dd class="col-sm-9" id="modalProductGroup"></dd>
                        <dt class="col-sm-3">Product:</dt><dd class="col-sm-9" id="modalProduct"></dd>
                        <dt class="col-sm-3">Product Version:</dt><dd class="col-sm-9" id="modalProductVersion"></dd>
                        <dt class="col-sm-3">Severity:</dt><dd class="col-sm-9" id="modalSeverity"></dd> <dt class="col-sm-3">Case Owner:</dt><dd class="col-sm-9" id="modalCaseOwner"></dd>
                        <dt class="col-sm-3">Company:</dt><dd class="col-sm-9" id="modalCompany"></dd>
                        <dt class="col-sm-3">Last Modified:</dt><dd class="col-sm-9" id="modalLastModified"></dd>
                    </dl>
                    <hr>
                    <h6 class="mt-3">Case History</h6>
                    <div id="ongoing-case-history-content" style="max-height: 250px; overflow-y: auto; border: 1px solid #eee; padding: 10px; border-radius: 4px; background-color: #f8f9fc;">
                         </div>
                    <p id="history-loading-ongoing" class="text-center text-muted mt-2" style="display: none;"><i class="fas fa-spinner fa-spin"></i> Loading history...</p>
                    <p id="history-error-ongoing" class="text-danger text-center mt-2" style="display: none;"></p>
                </div>
                <div class="modal-footer justify-content-between">
                     <div> <button type="button" class="btn btn-primary btn-sm mark-as-solved-btn-modal">
                             <i class="bi bi-check-lg"></i> Mark Solved
                         </button>
                         <button type="button" class="btn btn-warning btn-sm escalate-severity-btn-modal ml-1">
                             <i class="bi bi-graph-up"></i> Escalate Priority
                         </button>
                     </div>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chatModal" tabindex="-1" role="dialog" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatModalLabel">Chat for Case #<span id="chatCaseNumberDisplay"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="chatMessages" style="height: 350px; overflow-y: scroll; border: 1px solid #ccc; margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
                        </div>
                    <textarea id="chatInput" class="form-control" rows="3" placeholder="Type your message here..."></textarea>
                    <input type="hidden" id="chatCurrentCaseNumber" value="">
                    <input type="hidden" id="chatCurrentCaseOwner" value=""> </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="sendMessage">Send</button>
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
             var table = $('#casesTable').DataTable({ // Store reference to table
                "order": [[ 1, "desc" ]], // Sort by Case Number (second column now) descending
                "pageLength": 10,
                "language": {
                    "zeroRecords": "No matching cases found",
                    "emptyTable": "No on-going cases available"
                },
                "columnDefs": [
                    { "orderable": false, "targets": 0 }, // Disable ordering on checkbox column
                    { "orderable": false, "targets": 5 }  // Disable ordering on details/actions column
                ]
            });

            // Auto-dismiss alerts after 7 seconds
            window.setTimeout(function() {
                 $(".alert.alert-dismissible.fade.show").fadeTo(500, 0).slideUp(500, function() {
                     $(this).remove();
                     // Clean URL parameters if they were used for messages (optional)
                     // if (window.history.replaceState) {
                     //     const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                     //     window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
                     // }
                 });
             }, 7000); // 7 seconds

            // --- Global Scope Variables ---
            const currentUserId = <?= json_encode($_SESSION['user_id'] ?? null) ?>;
            const currentUserFullName = <?= json_encode($_SESSION['user_full_name'] ?? 'Engineer') ?>;
            let chatPollingInterval = null;
            let currentCaseIdForDetailsModal = null;

            // --- Date Formatting Function (mm/dd/yyyy HH:MM AM/PM) ---
            const formatHistoryDateTime = (dateTimeStr) => {
                // (Keep existing formatHistoryDateTime function - unchanged)
                 if (!dateTimeStr || dateTimeStr === 'N/A' || dateTimeStr === '0000-00-00 00:00:00') return 'N/A';
                 try {
                     const date = new Date(dateTimeStr.replace(' ', 'T') + 'Z'); // Treat as UTC
                     if (isNaN(date.getTime())) { return dateTimeStr; }
                     return date.toLocaleString(undefined, { month: '2-digit', day: '2-digit', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
                 } catch (e) { console.error("Date formatting error:", e); return dateTimeStr; }
            };

            // Helper to safely display text content (prevents XSS)
            const safeText = (text) => {
                // (Keep existing safeText function - unchanged)
                if (text === null || typeof text === 'undefined') return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };


            // === View Details Modal Trigger & History Load ===
            // Using event delegation for dynamically added/updated rows by DataTable
            $('#casesTable tbody').on('click', '.view-details-btn', function() {
                 var button = $(this);
                 var modal = $('#detailsModal');
                 currentCaseIdForDetailsModal = button.data('id'); // Store the ID

                 // Store data needed by modal buttons
                 modal.data('case-id', currentCaseIdForDetailsModal);
                 modal.data('case-number', button.data('case-number'));
                 modal.data('severity-display', button.data('severity-display'));
                 modal.data('severity-text', button.data('severity-text'));
                 modal.data('priority', button.data('priority'));
                 modal.data('is-escalated', button.data('is-escalated'));

                 // Populate main modal fields
                 modal.find('#modalCaseNumber').text(button.data('case-number'));
                 modal.find('#modalType').text(button.data('type'));
                 modal.find('#modalSubject').text(button.data('subject'));
                 modal.find('#modalProductGroup').text(button.data('product-group'));
                 modal.find('#modalProduct').text(button.data('product'));
                 modal.find('#modalProductVersion').text(button.data('product-version'));

                 // Populate Severity with Escalated Badge if needed
                 let severityHtml = safeText(button.data('severity-display'));
                 const isEscalated = button.data('is-escalated');
                 if (isEscalated == 1) {
                     severityHtml += ' <span class="badge badge-escalated ml-1">Escalated</span>';
                 }
                 modal.find('#modalSeverity').html(severityHtml); // Use .html() to render badge

                 modal.find('#modalCaseOwner').text(button.data('case-owner'));
                 modal.find('#modalCompany').text(button.data('company'));
                 modal.find('#modalLastModified').text(button.data('last-modified'));

                 // Fetch and Display Case History
                var historyContainer = modal.find('#ongoing-case-history-content');
                var historyLoading = modal.find('#history-loading-ongoing');
                var historyError = modal.find('#history-error-ongoing');

                historyContainer.html(''); // Clear previous
                historyError.hide();
                historyLoading.show();

                 if (currentCaseIdForDetailsModal && !isNaN(currentCaseIdForDetailsModal)) {
                    // (Keep existing AJAX call for fetch_case_history.php - unchanged)
                    $.ajax({
                         url: '../fetch/fetch_case_history.php', method: 'GET', data: { case_id: currentCaseIdForDetailsModal }, dataType: 'json', timeout: 10000,
                         success: function(response) {
                             historyLoading.hide();
                             if (response && response.success && Array.isArray(response.history_events)) {
                                 const events = response.history_events;
                                 let historyHtml = '';
                                 if (events.length === 0) {
                                     historyHtml = '<p class="text-muted text-center">No history events recorded.</p>';
                                 } else {
                                     historyHtml = '<ul class="list-group list-group-flush list-group-history">';
                                     events.forEach(event => {
                                         const eventTime = formatHistoryDateTime(event.timestamp || null);
                                         const userName = safeText(event.user_name || event.user_id || 'System');
                                         const actionTypeDisplay = safeText(event.action_type || 'EVENT').toUpperCase();
                                         let detailsText = event.details ? safeText(event.details) : '';
                                         let fullDetails = `<strong>${actionTypeDisplay}:</strong> ${detailsText}`;
                                         let changeInfo = ''; let detailsLower = detailsText.toLowerCase();
                                         if( (event.old_value || event.new_value) && detailsLower.indexOf('changed from') === -1 && detailsLower.indexOf('priority changed from') === -1 && detailsLower.indexOf('status changed to') === -1 ) {
                                             changeInfo = ` (Changed from ${safeText(event.old_value || 'N/A')} to ${safeText(event.new_value || 'N/A')})`; fullDetails += changeInfo;
                                         }
                                         historyHtml += `<li class="list-group-item">${fullDetails} <br><em>by ${userName} on ${eventTime}</em></li>`;
                                     });
                                     historyHtml += '</ul>';
                                 }
                                 historyContainer.html(historyHtml);
                             } else { console.warn("History fetch unsuccessful or data format incorrect:", response); historyError.text(response && response.message ? response.message : 'Could not retrieve case history events.').show(); }
                         },
                         error: function(jqXHR, textStatus, errorThrown) {
                             historyLoading.hide(); console.error("AJAX Error fetching history:", textStatus, errorThrown, jqXHR.responseText);
                             let errorMsg = 'Error loading case history. '; if (textStatus === 'timeout') { errorMsg += 'Request timed out.'; } else if (jqXHR.status === 404) { errorMsg += 'History endpoint not found.'; } else if (jqXHR.status >= 500) { errorMsg += 'Server error processing history request.'; } else { errorMsg += 'Please check network connection.'; }
                             historyError.text(errorMsg).show();
                         }
                     });
                 } else { historyLoading.hide(); historyError.text('Cannot load history: Invalid Case ID provided.').show(); }
            });


             // === Action Button Handlers (inside Details Modal) ===
             // Using event delegation on the modal itself
             $('#detailsModal').on('click', '.mark-as-solved-btn-modal', function() {
                 // (Keep existing logic to populate and show #markAsSolved modal - unchanged)
                  var caseNumber = $('#detailsModal').data('case-number'); var caseId = $('#detailsModal').data('case-id');
                  if (caseNumber && caseId) { $('#markAsSolved #markSolvedCaseId').val(caseId); $('#markAsSolved #markSolvedCaseNumberDisplay').text(caseNumber); $('#markAsSolved #isReopen').val('false'); $('#markAsSolved #solution').val(''); $('#detailsModal').modal('hide'); $('#markAsSolved').modal('show'); } else { console.error('DetailsModal missing case-id or case-number data.'); alert('Error: Could not get Case details for solving.'); }
             });

             $('#detailsModal').on('click', '.escalate-severity-btn-modal', function() {
                 // (Keep existing logic to populate and show #escalateSeverity modal - unchanged)
                  var caseId = $('#detailsModal').data('case-id'); var caseNumber = $('#detailsModal').data('case-number'); var currentPriority = $('#detailsModal').data('priority'); var currentSeverityDisplay = $('#detailsModal').data('severity-display');
                  if (caseId && caseNumber) { const escalateModal = $('#escalateSeverity'); escalateModal.find('#escalateCaseId').val(caseId); escalateModal.find('#escalateCurrentPriorityNum').val(currentPriority); escalateModal.find('#escalateCaseNumberDisplay').text(caseNumber); escalateModal.find('#escalateCurrentSeverityDisplay').text(currentSeverityDisplay || 'N/A'); escalateModal.find('#escalation_reason').val(''); escalateModal.find('#priorityWarning').hide(); const priorityDropdown = escalateModal.find('#new_priority'); priorityDropdown.find('option').prop('disabled', false); if (currentPriority !== '' && currentPriority !== null && !isNaN(currentPriority)) { priorityDropdown.find('option[value="' + currentPriority + '"]').prop('disabled', true); } priorityDropdown.val(''); $('#detailsModal').modal('hide'); escalateModal.modal('show'); } else { console.error('DetailsModal missing data for escalation.'); alert('Error: Could not get Case details for escalation.'); }
             });

             // Client-side validation for escalation modal priority change
             $('#escalateSeverity').on('change', '#new_priority', function() {
                 // (Keep existing validation logic for #new_priority change - unchanged)
                  var newP = $(this).val(); var currentP = $('#escalateSeverity #escalateCurrentPriorityNum').val(); var warning = $('#escalateSeverity #priorityWarning'); var submitBtn = $('#escalateSeverity #confirmEscalateBtn');
                  if (!newP) { warning.text('Please select a new priority.').show(); submitBtn.prop('disabled', true); } else if (currentP !== '' && currentP !== null && newP == currentP) { warning.text('New priority cannot be the same as the current one.').show(); submitBtn.prop('disabled', true); } else { warning.hide(); submitBtn.prop('disabled', false); }
             });
             // Ensure button is disabled initially when modal shows
             $('#escalateSeverity').on('show.bs.modal', function () {
                 // (Keep existing logic to reset state on modal show - unchanged)
                 const escalateModal = $(this); escalateModal.find('#confirmEscalateBtn').prop('disabled', true); escalateModal.find('#priorityWarning').hide(); escalateModal.find('#new_priority').val(''); var currentPriority = escalateModal.find('#escalateCurrentPriorityNum').val(); const priorityDropdown = escalateModal.find('#new_priority'); priorityDropdown.find('option').prop('disabled', false); if (currentPriority !== '' && currentPriority !== null && !isNaN(currentPriority)) { priorityDropdown.find('option[value="' + currentPriority + '"]').prop('disabled', true); }
             });


             // === Chat Modal Logic ===
             // Using event delegation for chat trigger
             $('#casesTable tbody').on('click', '.case-chat-trigger', function(event) {
                 // (Keep existing logic for chat trigger click - unchanged)
                  event.preventDefault(); var caseNumber = $(this).data('case-number'); var caseOwnerName = $(this).data('case-owner'); $('#chatCaseNumberDisplay').text(caseNumber); $('#chatCurrentCaseNumber').val(caseNumber); $('#chatCurrentCaseOwner').val(caseOwnerName); $('#chatMessages').html('<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading messages...</p>'); fetchChatMessages(caseNumber); if (chatPollingInterval) clearInterval(chatPollingInterval); chatPollingInterval = setInterval(() => { fetchChatMessages(caseNumber); }, 5000); console.log("Chat polling started for:", caseNumber);
             });

             function fetchChatMessages(caseNumber) {
                 // (Keep existing fetchChatMessages function - unchanged)
                  if (!caseNumber) return; $.ajax({ url: '../fetch/chat_messages.php', type: 'GET', data: { case_number: caseNumber }, dataType: 'json', success: function(data) { var chatMessagesHtml = ''; var chatBox = $('#chatMessages'); var isScrolledToBottom = chatBox[0].scrollHeight - chatBox.innerHeight() <= chatBox.scrollTop() + 30; if (data && Array.isArray(data)) { if(data.length === 0){ chatMessagesHtml = '<p class="text-center text-muted">No messages yet.</p>'; } else { data.forEach(message => { var messageClass = (message.sender === currentUserFullName) ? 'message-sender' : 'message-receiver'; const messageTime = message.created_at ? formatHistoryDateTime(message.created_at) : ''; const senderDisplay = message.sender ? safeText(message.sender) : 'Unknown'; const messageText = message.message ? safeText(message.message).replace(/\n/g, '<br>') : ''; chatMessagesHtml += `<div class="chat-message ${messageClass}"><strong>${senderDisplay}</strong><br>${messageText}<span class="message-time">${messageTime}</span></div>`; }); } } else { chatMessagesHtml = '<p class="text-center text-danger">Error loading messages format.</p>'; } var currentHtml = chatBox.html(); if (currentHtml !== chatMessagesHtml) { chatBox.html(chatMessagesHtml); if(isScrolledToBottom || currentHtml.includes('Loading') || currentHtml.includes('No messages')) { chatBox.scrollTop(chatBox[0].scrollHeight); } } }, error: function(xhr, status, error) { console.error('Error fetching chat:', status, error, xhr.responseText); if (!$('#chatMessages').find('.text-danger').length) { $('#chatMessages').html('<p class="text-center text-danger">Error fetching messages.</p>'); } if (chatPollingInterval) { clearInterval(chatPollingInterval); chatPollingInterval = null; console.log("Chat polling stopped due to error.");} } });
             }

             // Send message button click
             $('#sendMessage').on('click', function() {
                // (Keep existing send message logic - unchanged)
                  var message = $('#chatInput').val().trim(); var caseNumber = $('#chatCurrentCaseNumber').val(); var caseOwnerName = $('#chatCurrentCaseOwner').val();
                  if (message && caseNumber && currentUserId) { $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...'); $.ajax({ url: '../process/send_message.php', type: 'POST', contentType: 'application/json', data: JSON.stringify({ case_number: caseNumber, case_owner: caseOwnerName, message: message, sender_id: currentUserId, sender_name: currentUserFullName }), dataType: 'json', success: function(data) { if (data.success) { $('#chatInput').val(''); fetchChatMessages(caseNumber); } else { console.error("Send message failed:", data.error); alert('Error: ' + (data.error || 'Could not send message.')); } }, error: function(xhr, status, error) { console.error("Send message AJAX error:", status, error, xhr.responseText); alert('Failed to send message. Check network or server logs.'); }, complete: function() { $('#sendMessage').prop('disabled', false).html('Send'); $('#chatInput').focus(); } }); } else if (!message) { $('#chatInput').focus(); } else if (!currentUserId) { alert('User session error. Please log in again.'); }
             });

             // Send message on Enter key press
             $('#chatInput').on('keypress', function(e) {
                // (Keep existing Enter key logic - unchanged)
                  if (e.which == 13 && !e.shiftKey) { e.preventDefault(); $('#sendMessage').click(); }
             });

             // Stop polling when chat modal is closed
             $('#chatModal').on('hidden.bs.modal', function () {
                // (Keep existing polling stop logic - unchanged)
                  if (chatPollingInterval) { clearInterval(chatPollingInterval); chatPollingInterval = null; console.log("Chat polling stopped on modal close."); } $('#chatMessages').html(''); $('#chatCurrentCaseNumber').val('');
             });

            // === NEW: Bulk Action Checkbox Logic ===
            const $selectAllCheckbox = $('#selectAllCheckbox');
            const $bulkMarkSolvedBtn = $('#bulkMarkSolvedBtn');
            // No need for $caseCheckboxes variable here as we use delegated events

            // Function to update "Select All" checkbox state and bulk button
            function updateBulkActionState() {
                 // Use DataTables API to get nodes of visible rows on the current page
                const $visibleCheckboxes = $(table.rows({ page: 'current' }).nodes()).find('.case-checkbox');
                const numVisible = $visibleCheckboxes.length;
                const numChecked = $visibleCheckboxes.filter(':checked').length;

                // Update "Select All" checkbox
                if (numVisible > 0 && numChecked === numVisible) {
                    $selectAllCheckbox.prop('checked', true);
                    $selectAllCheckbox.prop('indeterminate', false);
                } else if (numChecked > 0) {
                    $selectAllCheckbox.prop('checked', false);
                    $selectAllCheckbox.prop('indeterminate', true); // Partially selected
                } else {
                    $selectAllCheckbox.prop('checked', false);
                    $selectAllCheckbox.prop('indeterminate', false);
                }

                // Enable/disable bulk action button
                $bulkMarkSolvedBtn.prop('disabled', numChecked === 0);
            }

            // "Select All" checkbox click handler
            $selectAllCheckbox.on('click', function() {
                // Check/uncheck checkboxes ONLY in the rows of the current page
                 $(table.rows({ page: 'current' }).nodes()).find('.case-checkbox').prop('checked', this.checked);
                updateBulkActionState();
            });

            // Individual checkbox click handler (using delegation on table body)
            $('#casesTable tbody').on('click', '.case-checkbox', function() {
                updateBulkActionState();
            });

            // Update state when DataTables redraws (e.g., pagination, search)
             table.on('draw.dt', function() {
                // Update state after table is drawn, ensures we check correct visible rows
                updateBulkActionState();
            });

            // Initialize state on page load after table is ready
            updateBulkActionState();


             // === NEW: Bulk Mark Solved Button Click Handler ===
             $bulkMarkSolvedBtn.on('click', function() {
                 const selectedCaseIds = [];
                 // Get checked checkboxes from ALL pages using DataTables API selector
                 // Important: This selects based on the current filtering/search state
                 $(table.rows({ search: 'applied' }).nodes()).find('.case-checkbox:checked').each(function() {
                    selectedCaseIds.push($(this).val());
                 });


                 if (selectedCaseIds.length === 0) {
                     alert('Please select at least one case to mark as solved.');
                     return;
                 }

                 // Confirmation dialog
                 if (confirm('Are you sure you want to mark the ' + selectedCaseIds.length + ' selected case(s) as solved?')) {
                     console.log('Marking cases as solved:', selectedCaseIds);

                     // Disable button while processing
                     $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                     // AJAX request to server-side script
                     $.ajax({
                         url: '../process/bulk_mark_solved.php', // *** Ensure this path is correct ***
                         method: 'POST',
                         contentType: 'application/json',
                         data: JSON.stringify({ case_ids: selectedCaseIds }),
                         dataType: 'json',
                         success: function(response) {
                             if (response && response.success) {
                                 // Reload page to show session message and updated table
                                 window.location.reload();
                             } else {
                                 alert('Error: ' + (response && response.message ? response.message : 'Could not mark cases as solved.'));
                                  $bulkMarkSolvedBtn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Mark Selected as Solved'); // Re-enable on failure
                             }
                         },
                         error: function(jqXHR, textStatus, errorThrown) {
                             console.error("Bulk Mark Solved AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                             alert('An error occurred while communicating with the server. Please try again.');
                             $bulkMarkSolvedBtn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Mark Selected as Solved'); // Re-enable on error
                         }
                     });
                 }
             });
             // === End NEW Bulk Action Logic ===

        }); // End $(document).ready()
    </script>

</body>
</html>