<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
$pageTitle = "Reopened Cases";
require_once "../fetch/reopened_cases.php"; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <style>
        #reopenedCaseDetailsModal .modal-body dt {
            font-weight: bold;
            color: #5a5c69;
        }
         #reopenedCaseDetailsModal .modal-body dd {
            margin-bottom: 0.75rem;
         }

         #chatMessages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
            overflow-y: auto;
            max-height: 300px;
        }
        .chat-message {
            max-width: 80%;
            word-wrap: break-word;
            padding: 10px 15px;
            border-radius: 15px;
            position: relative;
        }
        .message-sender {
            align-self: flex-end;
            background-color: #007bff;
            color: white;
            text-align: left;
        }
        .message-receiver {
            align-self: flex-start;
            background-color: #f1f1f1;
            color: black;
            text-align: left;
        }
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
         /* Style for the case number link/button triggering chat */
         .case-chat-trigger {
            cursor: pointer;
         }
         /* --- End Chat CSS --- */

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

                     <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> Case solved successfully! Go to <a href="solved_cases.php">Solved Cases</a>.
                             <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    <?php endif; ?>


                    <div class="card shadow mb-4">
                        <div class="card-header py-3"> 
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($pageTitle) ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="reopenedCasesTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Case Number</th>
                                            <th>Severity</th>
                                            <th>Case Owner</th>
                                            <th>Company</th>
                                            <th>Last Modified</th>
                                            <th>Details</th> 
                                            <th>Action</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($reopenedCasesTable as $row) {
                                            // --- Prepare data for details modal attributes ---
                                            $reopenedCaseData = [
                                                'id' => $row["id"] ?? '',
                                                'case-number' => $row["case_number"] ?? 'N/A',
                                                'type' => $row["type"] ?? 'N/A',
                                                'subject' => $row["subject"] ?? 'N/A',
                                                'product-group' => $row["product_group"] ?? 'N/A',
                                                'product' => $row["product"] ?? 'N/A',
                                                'product-version' => $row["product_version"] ?? 'N/A',
                                                'severity' => $row["severity"] ?? 'N/A',
                                                'case-owner' => $row["case_owner"] ?? 'N/A',
                                                'company' => $row["company"] ?? 'N/A',
                                                'last-modified' => $row["last_modified"] ?? 'N/A',
                                                'datetime-opened' => $row["datetime_opened"] ?? 'N/A'
                                                // Add any other fields specific to reopened cases if needed
                                            ];

                                            $reopenedDataAttributes = '';
                                            foreach ($reopenedCaseData as $key => $value) {
                                                $reopenedDataAttributes .= ' data-' . $key . '="' . htmlspecialchars($value) . '"';
                                            }

                                            // --- Buttons and Triggers ---
                                            // Case Number link/button to trigger CHAT modal (similar to ongoing_cases)
                                            $caseChatTrigger = '<a href="#"
                                                                 class="case-chat-trigger"
                                                                 data-case-number="' . htmlspecialchars($reopenedCaseData["case-number"]) . '"
                                                                 data-case-owner="' . htmlspecialchars($reopenedCaseData["case-owner"]) . '"
                                                                 data-toggle="modal"
                                                                 data-target="#chatModal">'
                                                                 . htmlspecialchars($reopenedCaseData["case-number"])
                                                              . '</a>';

                                            $viewReopenedDetailsButton = '<button
                                                type="button"
                                                class="btn btn-info btn-sm reopened-view-details-btn" 
                                                ' . $reopenedDataAttributes . '
                                                data-toggle="modal"
                                                data-target="#reopenedCaseDetailsModal"> 
                                                <i class="fas fa-eye"></i> View
                                                </button>';

                                            $markSolvedButton = '<button
                                                data-bs-case-number="' . htmlspecialchars($reopenedCaseData["case-number"]) . '"
                                                data-bs-reopen="true" {/* Flag indicating it was reopened */}
                                                type="button"
                                                class="mark-as-solved-btn btn btn-primary btn-sm"
                                                data-toggle="modal"
                                                data-target="#markAsSolved">
                                                <i class="bi bi-check-square"></i>
                                                Mark Solved
                                                </button>';

                                            // --- Output Table Row ---
                                            echo "<tr>";
                                            echo "<td>" . $caseChatTrigger . "</td>"; // Chat trigger
                                            echo "<td>" . htmlspecialchars($reopenedCaseData["severity"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($reopenedCaseData["case-owner"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($reopenedCaseData["company"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($reopenedCaseData["last-modified"]) . "</td>";
                                            echo "<td>" . $viewReopenedDetailsButton . "</td>"; // View Details Button column
                                            echo "<td>" . $markSolvedButton . "</td>"; // Action Button column
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

    <div class="modal fade" id="reopenedCaseDetailsModal" tabindex="-1" role="dialog" aria-labelledby="reopenedCaseDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reopenedCaseDetailsModalLabel">Reopened Case Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-3">Case Number:</dt>
                        <dd class="col-sm-9" id="modalReopenedCaseNumber"></dd>

                        <dt class="col-sm-3">Type:</dt>
                        <dd class="col-sm-9" id="modalReopenedType"></dd>

                        <dt class="col-sm-3">Subject:</dt>
                        <dd class="col-sm-9" id="modalReopenedSubject"></dd>

                        <dt class="col-sm-3">Product Group:</dt>
                        <dd class="col-sm-9" id="modalReopenedProductGroup"></dd>

                        <dt class="col-sm-3">Product:</dt>
                        <dd class="col-sm-9" id="modalReopenedProduct"></dd>

                        <dt class="col-sm-3">Product Version:</dt>
                        <dd class="col-sm-9" id="modalReopenedProductVersion"></dd>

                        <dt class="col-sm-3">Severity:</dt>
                        <dd class="col-sm-9" id="modalReopenedSeverity"></dd>

                        <dt class="col-sm-3">Case Owner:</dt>
                        <dd class="col-sm-9" id="modalReopenedCaseOwner"></dd>

                        <dt class="col-sm-3">Company:</dt>
                        <dd class="col-sm-9" id="modalReopenedCompany"></dd>

                        <dt class="col-sm-3">Last Modified:</dt>
                        <dd class="col-sm-9" id="modalReopenedLastModified"></dd>

                        <dt class="col-sm-3">Date Opened:</dt>
                        <dd class="col-sm-9" id="modalReopenedDatetimeOpened"></dd>
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
        // Initialize DataTable for the reopened cases table
        $(document).ready(function() {
            $('#reopenedCasesTable').DataTable({ // Use the unique table ID
                 "order": [[ 4, "desc" ]], // Optional: Default sort by Last Modified descending
                 "pageLength": 10
            });
        });

        // --- Script for Handling Modals (Reopened Cases) ---
        $(document).ready(function() {
            const currentUserId = <?= json_encode($_SESSION['user_id']) ?>;
            const currentUserFullName = <?= json_encode($_SESSION['user_full_name']) ?>;
            let fetchInterval = null; // For chat polling

            // -- Reopened Case Details Modal Population --
            $('#reopenedCasesTable tbody').on('click', '.reopened-view-details-btn', function() {
                var button = $(this);
                var modal = $('#reopenedCaseDetailsModal');

                // Populate the modal using data attributes
                modal.find('#modalReopenedCaseNumber').text(button.data('case-number'));
                modal.find('#modalReopenedType').text(button.data('type'));
                modal.find('#modalReopenedSubject').text(button.data('subject'));
                modal.find('#modalReopenedProductGroup').text(button.data('product-group'));
                modal.find('#modalReopenedProduct').text(button.data('product'));
                modal.find('#modalReopenedProductVersion').text(button.data('product-version'));
                modal.find('#modalReopenedSeverity').text(button.data('severity'));
                modal.find('#modalReopenedCaseOwner').text(button.data('case-owner'));
                modal.find('#modalReopenedCompany').text(button.data('company'));
                modal.find('#modalReopenedLastModified').text(button.data('last-modified'));
                modal.find('#modalReopenedDatetimeOpened').text(button.data('datetime-opened'));

                // data-toggle on the button shows the modal
            });

            // -- Mark As Solved Modal Trigger --
            $('#reopenedCasesTable tbody').on('click', '.mark-as-solved-btn', function() {
                 // Assuming your mark_as_solved.php modal has inputs with id="caseNumber" and id="isReopen"
                 const caseNumberInput = document.getElementById("caseNumber");
                 const isReopenInput = document.getElementById("isReopen");

                 if(caseNumberInput && isReopenInput) {
                     caseNumberInput.value = $(this).data("bs-case-number");
                     isReopenInput.value = $(this).data("bs-reopen"); // Should be 'true' here
                 } else {
                     console.error("Could not find #caseNumber or #isReopen input in mark as solved modal.");
                 }
                 // data-toggle on the button shows the #markAsSolved modal
             });

            // --- Chat Modal Logic (Copied from ongoing_cases) ---
             $('#reopenedCasesTable tbody').on('click', '.case-chat-trigger', function(event) {
                event.preventDefault();

                var caseNumber = $(this).data('case-number');
                var caseOwner = $(this).data('case-owner');

                $('#chatCaseNumberDisplay').text(caseNumber);
                $('#chatCurrentCaseNumber').val(caseNumber);
                $('#chatCurrentCaseOwner').val(caseOwner);
                $('#chatMessages').html('<p class="text-center text-muted">Loading messages...</p>');

                fetchChatMessages(caseNumber);

                if (fetchInterval) clearInterval(fetchInterval);
                fetchInterval = setInterval(() => {
                    fetchChatMessages(caseNumber);
                }, 5000);
            });

            function fetchChatMessages(caseNumber) {
                 if (!caseNumber) return;
                $.ajax({
                    url: '../fetch/chat_messages.php', // Ensure this path is correct
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

            $('#sendMessage').on('click', function() {
                 var message = $('#chatInput').val().trim();
                var caseNumber = $('#chatCurrentCaseNumber').val();
                var caseOwner = $('#chatCurrentCaseOwner').val();

                if (message && caseNumber) {
                    $.ajax({
                        url: '../process/send_message.php', // Ensure this path is correct
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
                                fetchChatMessages(caseNumber);
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

            $('#chatInput').on('keypress', function(e) {
                if (e.which == 13 && !e.shiftKey) {
                    e.preventDefault();
                    $('#sendMessage').click();
                }
            });

            $('#chatModal').on('hidden.bs.modal', function () {
                if (fetchInterval) {
                    clearInterval(fetchInterval);
                    fetchInterval = null;
                }
            });
            // --- End Chat Modal Logic ---


            /*
             // Original Vanilla JS for Mark Solved button - Keep if preferred, but delegation is better for DataTables
             const markAsSolvedModal = document.getElementById("markAsSolved"); // Check ID in mark_as_solved.php
             const caseNumberHidden = document.getElementById("caseNumber"); // Check ID
             const isReopenHidden = document.getElementById("isReopen"); // Check ID

             if (markAsSolvedModal && caseNumberHidden && isReopenHidden) {
                 document.querySelectorAll('.mark-as-solved-btn').forEach(item => { // Won't work reliably with DataTables
                     item.addEventListener('click', function(event) {
                         caseNumberHidden.value = this.getAttribute("data-bs-case-number");
                         isReopenHidden.value = this.getAttribute("data-bs-reopen");
                     });
                 });
             }
            */

        }); // End $(document).ready()
    </script>

</body>

</html>