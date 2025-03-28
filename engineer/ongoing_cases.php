<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
$pageTitle = "On-going Cases";
require_once "../fetch/ongoing_cases.php"; // Assuming this fetches the $ongoingCasesTable array
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">

    <style>
        /* Chat Messages Container */
        #chatMessages {
            display: flex;
            flex-direction: column;
            gap: 10px; /* Space between messages */
            padding: 10px;
            overflow-y: auto;
            max-height: 300px;
        }

        /* Common message styles */
        .chat-message {
            max-width: 80%;
            word-wrap: break-word;
            padding: 10px 15px;
            border-radius: 15px;
            position: relative;
        }

        /* Sender's message (align to the right) */
        .message-sender {
            align-self: flex-end;
            background-color: #007bff;
            color: white;
            text-align: left;
        }

        /* Receiver's message (align to the left) */
        .message-receiver {
            align-self: flex-start;
            background-color: #f1f1f1;
            color: black;
            text-align: left;
        }

        /* Timestamp styling */
        .message-time {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            text-align: right;
            margin-top: 5px;
            display: block;
        }

        .message-receiver .message-time {
            color: rgba(0, 0, 0, 0.6);
        }

        /* Style for details modal labels */
        #detailsModal .modal-body dt {
            font-weight: bold;
            color: #5a5c69;
        }
         #detailsModal .modal-body dd {
            margin-bottom: 0.75rem;
         }

         /* Style for the case number link/button triggering chat */
         .case-chat-trigger {
            cursor: pointer;
            /* Optional: Add styles to make it look like a link */
            /* color: #007bff;
            text-decoration: underline; */
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
                            <i class="bi bi-check-circle-fill"></i> Case solved successfully! Go to <a href="solved_cases.php">Solved Cases</a>.
                             <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    <?php elseif (isset($_GET["escalate_severity"]) && $_GET["escalate_severity"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> Case severity escalated successfully!
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($pageTitle) ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="casesTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Case Number</th> 
                                            <th>Severity</th>
                                            <th>Case Owner</th>
                                            <th>Company</th>
                                            <th>Details</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($ongoingCasesTable as $row) {
                                            // --- Prepare data for attributes (use htmlspecialchars for safety) ---
                                            $caseData = [
                                                'id' => $row["id"],
                                                'case-number' => $row["case_number"],
                                                'type' => $row["type"],
                                                'subject' => $row["subject"],
                                                'product-group' => $row["product_group"],
                                                'product' => $row["product"],
                                                'product-version' => $row["product_version"],
                                                'severity' => $row["severity"],
                                                'case-owner' => $row["case_owner"],
                                                'company' => $row["company"],
                                                'last-modified' => $row["last_modified"]
                                            ];

                                            $dataAttributes = '';
                                            foreach ($caseData as $key => $value) {
                                                // Only add attributes needed for the details modal button
                                                $dataAttributes .= ' data-' . $key . '="' . htmlspecialchars($value) . '"';
                                            }

                                            // --- Buttons ---
                                            // Case Number link/button to trigger CHAT modal
                                            $caseChatTrigger = '<a href="#"
                                                                 class="case-chat-trigger"
                                                                 data-case-number="' . htmlspecialchars($row["case_number"]) . '"
                                                                 data-case-owner="' . htmlspecialchars($row["case_owner"]) . '"
                                                                 data-toggle="modal"
                                                                 data-target="#chatModal">'
                                                                 . htmlspecialchars($row["case_number"])
                                                              . '</a>';
                                            // Alternative using button styled as link:
                                            // $caseChatTrigger = '<button type="button"
                                            //                     class="btn btn-link case-chat-trigger p-0"
                                            //                     data-case-number="' . htmlspecialchars($row["case_number"]) . '"
                                            //                     data-case-owner="' . htmlspecialchars($row["case_owner"]) . '"
                                            //                     data-toggle="modal"
                                            //                     data-target="#chatModal">'
                                            //                     . htmlspecialchars($row["case_number"])
                                            //                   . '</button>';


                                            $viewDetailsButton = '<button
                                                type="button"
                                                class="btn btn-info btn-sm view-details-btn"
                                                ' . $dataAttributes . '
                                                data-toggle="modal"
                                                data-target="#detailsModal">
                                                <i class="fas fa-eye"></i> View
                                                </button>';

                                             $markAsSolvedButton = '<button
                                                data-bs-case-number="' . htmlspecialchars($row["case_number"]) . '"
                                                data-bs-reopen="false"
                                                type="button"
                                                class="mark-as-solved-btn btn btn-primary btn-sm"
                                                data-toggle="modal"
                                                data-target="#markAsSolved">
                                                <i class="bi bi-check"></i> Mark Solved
                                                </button>';

                                             $escalateSeverityButton = '<button
                                                data-bs-case-id="' . htmlspecialchars($row["id"]) . '"
                                                data-bs-severity="' . htmlspecialchars($row["severity"]) . '"
                                                data-bs-reopen="false"
                                                type="button"
                                                class="escalate-severity-btn btn btn-warning btn-sm ml-1"
                                                data-toggle="modal"
                                                data-target="#escalateSeverity">
                                                <i class="bi bi-exclamation"></i> Escalate
                                                </button>';

                                            // --- Output Table Row ---
                                            echo "<tr>";
                                            echo "<td>" . $caseChatTrigger . "</td>"; // Use the chat trigger element here
                                            echo "<td>" . htmlspecialchars($row["severity"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["case_owner"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["company"]) . "</td>";
                                            echo "<td>" . $viewDetailsButton . "</td>";
                                            echo "<td>" . $markAsSolvedButton . $escalateSeverityButton . "</td>";
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
    <?php include_once "../modals/mark_as_solved.php" ?>
    <?php include_once "../modals/escalate_severity.php" ?>

    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Case Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-3">Case Number:</dt>
                        <dd class="col-sm-9" id="modalCaseNumber"></dd>

                        <dt class="col-sm-3">Type:</dt>
                        <dd class="col-sm-9" id="modalType"></dd>

                        <dt class="col-sm-3">Subject:</dt>
                        <dd class="col-sm-9" id="modalSubject"></dd>

                        <dt class="col-sm-3">Product Group:</dt>
                        <dd class="col-sm-9" id="modalProductGroup"></dd>

                        <dt class="col-sm-3">Product:</dt>
                        <dd class="col-sm-9" id="modalProduct"></dd>

                        <dt class="col-sm-3">Product Version:</dt>
                        <dd class="col-sm-9" id="modalProductVersion"></dd>

                        <dt class="col-sm-3">Severity:</dt>
                        <dd class="col-sm-9" id="modalSeverity"></dd>

                        <dt class="col-sm-3">Case Owner:</dt>
                        <dd class="col-sm-9" id="modalCaseOwner"></dd>

                        <dt class="col-sm-3">Company:</dt>
                        <dd class="col-sm-9" id="modalCompany"></dd>

                        <dt class="col-sm-3">Last Modified:</dt>
                        <dd class="col-sm-9" id="modalLastModified"></dd>
                    </dl>
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

     <div class="modal fade" id="chatModal" tabindex="-1" role="dialog" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatModalLabel">Chat for Case #<span id="chatCaseNumberDisplay"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="chatMessages" style="height: 300px; overflow-y: scroll; border: 1px solid #ccc; margin-bottom: 10px; padding: 10px; background-color: #f8f9fa;">
                        <p class="text-center text-muted">Loading messages...</p>
                    </div>
                    <textarea id="chatInput" class="form-control" rows="3" placeholder="Type your message here..."></textarea>
                     <input type="hidden" id="chatCurrentCaseNumber" value="">
                     <input type="hidden" id="chatCurrentCaseOwner" value="">
                </div>
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
        // Initialize DataTable
        $(document).ready(function() {
            $('#casesTable').DataTable({
                "order": [],
                 "pageLength": 10
            });
        });

        // --- Script for Handling Modals ---
        $(document).ready(function() {
            const currentUserId = <?= json_encode($_SESSION['user_id']) ?>;
            const currentUserFullName = <?= json_encode($_SESSION['user_full_name']) ?>;
            let fetchInterval = null; // For chat polling

            // -- Details Modal Population --
            // Uses event delegation for buttons added by DataTables after initialization
            $('#casesTable tbody').on('click', '.view-details-btn', function() {
                var button = $(this); // Button that triggered the action
                var modal = $('#detailsModal'); // The modal itself

                // Extract data from data-* attributes
                modal.find('#modalCaseNumber').text(button.data('case-number'));
                modal.find('#modalType').text(button.data('type'));
                modal.find('#modalSubject').text(button.data('subject'));
                modal.find('#modalProductGroup').text(button.data('product-group'));
                modal.find('#modalProduct').text(button.data('product'));
                modal.find('#modalProductVersion').text(button.data('product-version'));
                modal.find('#modalSeverity').text(button.data('severity'));
                modal.find('#modalCaseOwner').text(button.data('case-owner'));
                modal.find('#modalCompany').text(button.data('company'));
                modal.find('#modalLastModified').text(button.data('last-modified'));

                // Note: No need to handle chat button data here anymore
            });

             // Correct event listener attachment for dynamically added buttons (like in DataTables)
             $('#casesTable tbody').on('click', '.mark-as-solved-btn', function() {
                 $('#caseNumber').val($(this).data('bs-case-number'));
                 $('#isReopen').val($(this).data('bs-reopen'));
                 // The data-toggle="modal" will show the #markAsSolved modal
             });

             $('#casesTable tbody').on('click', '.escalate-severity-btn', function() {
                 $('#caseId').val($(this).data('bs-case-id'));
                 var currentSeverity = $(this).data('bs-severity');
                 // Optional: Add logic based on currentSeverity if needed
                 // The data-toggle="modal" will show the #escalateSeverity modal
             });


            // --- Chat Modal Logic (Triggered from Table) ---

            // Event listener for the case number link/button in the table
            // Use event delegation on the table body to handle elements added by DataTables
            $('#casesTable tbody').on('click', '.case-chat-trigger', function(event) {
                event.preventDefault(); // Prevent default link behavior if using <a>

                var caseNumber = $(this).data('case-number');
                var caseOwner = $(this).data('case-owner');

                // Set values for the chat modal
                $('#chatCaseNumberDisplay').text(caseNumber);
                $('#chatCurrentCaseNumber').val(caseNumber);
                $('#chatCurrentCaseOwner').val(caseOwner);

                // Clear previous messages and show loading state
                $('#chatMessages').html('<p class="text-center text-muted">Loading messages...</p>');

                // Fetch initial messages
                fetchChatMessages(caseNumber);

                // Start polling for new messages
                if (fetchInterval) clearInterval(fetchInterval); // Clear previous interval if any
                fetchInterval = setInterval(() => {
                    fetchChatMessages(caseNumber);
                }, 5000); // Poll every 5 seconds

                // The data-toggle="modal" data-target="#chatModal" on the link/button handles showing the modal
            });


            // Function to fetch chat messages (Remains the same)
            function fetchChatMessages(caseNumber) {
                 if (!caseNumber) return;
                $.ajax({
                    url: '../fetch/chat_messages.php',
                    type: 'GET',
                    data: { case_number: caseNumber },
                    dataType: 'json',
                    success: function(data) {
                        var chatMessagesHtml = '';
                         if (data.length === 0) {
                             chatMessagesHtml = '<p class="text-center text-muted">No messages yet.</p>';
                         } else {
                             data.forEach(message => {
                                var messageClass = (message.sender === currentUserFullName) ? 'message-sender' : 'message-receiver';
                                chatMessagesHtml += `
                                    <div class="chat-message ${messageClass}">
                                        <strong>${$('<div>').text(message.sender).html()}</strong><br>
                                        ${$('<div>').text(message.message).html()}
                                        <span class="message-time">${$('<div>').text(message.created_at).html()}</span>
                                    </div>
                                `;
                            });
                         }
                        var currentHtml = $('#chatMessages').html();
                         if (currentHtml !== chatMessagesHtml) {
                           var shouldScroll = ($('#chatMessages').scrollTop() + $('#chatMessages').innerHeight() >= $('#chatMessages')[0].scrollHeight - 50);
                            $('#chatMessages').html(chatMessagesHtml);
                             if(shouldScroll || currentHtml.includes('Loading messages') || currentHtml.includes('No messages yet')) {
                                 $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                             }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching chat messages:', error);
                         $('#chatMessages').html('<p class="text-center text-danger">Error loading messages.</p>');
                    }
                });
            }

            // Send Message Button Click (Remains the same)
            $('#sendMessage').on('click', function() {
                 var message = $('#chatInput').val().trim();
                var caseNumber = $('#chatCurrentCaseNumber').val();
                var caseOwner = $('#chatCurrentCaseOwner').val();

                if (message && caseNumber) {
                    $.ajax({
                        url: '../process/send_message.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            case_number: caseNumber,
                            case_owner: caseOwner,
                            message: message
                        }),
                        dataType: 'json',
                        success: function(data) {
                            if (data.success) {
                                $('#chatInput').val('');
                                fetchChatMessages(caseNumber); // Refresh immediately
                            } else {
                                alert('Error sending message: ' + (data.error || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error sending message:', error);
                            alert('Failed to send message. Please try again.');
                        }
                    });
                }
            });

            // Allow sending message with Enter key (Remains the same)
            $('#chatInput').on('keypress', function(e) {
                if (e.which == 13 && !e.shiftKey) {
                    e.preventDefault();
                    $('#sendMessage').click();
                }
            });

            // Stop polling when chat modal is closed (Remains the same)
             $('#chatModal').on('hidden.bs.modal', function () {
                if (fetchInterval) {
                    clearInterval(fetchInterval);
                    fetchInterval = null;
                }
            });

             // IMPORTANT: Use event delegation for modal triggers if using DataTables
             // This ensures buttons work even after sorting/paging changes the DOM
             // Example for details modal trigger: (Already adjusted above)
             // $('#casesTable tbody').on('click', '.view-details-btn', function() { ... });
             // Example for chat modal trigger: (Already adjusted above)
             // $('#casesTable tbody').on('click', '.case-chat-trigger', function(event) { ... });
             // Example for mark solved trigger: (Already adjusted above)
             // $('#casesTable tbody').on('click', '.mark-as-solved-btn', function() { ... });
             // Example for escalate severity trigger: (Already adjusted above)
             // $('#casesTable tbody').on('click', '.escalate-severity-btn', function() { ... });

             // Remove older direct bindings if they existed, as delegation handles it now
             // $('.view-details-btn').off('click'); // etc.

        }); // End $(document).ready()

    </script>

</body>
</html>