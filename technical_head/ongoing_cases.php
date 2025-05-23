<?php
session_start();
$pageTitle = "On-going Cases";

require_once "../fetch/technical_ongoing_cases.php";
// Note: Ensure your database connection ($pdo or $conn) is established,
// potentially via an included config file if not done in technical_ongoing_cases.php
// require_once "../config/database2.php"; // Example path if needed here

// Fetch engineers list is now handled via JavaScript AJAX when the modal opens.

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <style>
        /* --- Your existing styles for chat, updated rows etc. --- */
        /* Styling for chat messages */
        #chatMessages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            /* Space between messages */
            padding: 10px;
            /* Add some padding inside the chat container */
        }

        .message-sender {
            align-self: flex-end;
            text-align: right;
            margin-bottom: 10px;
        }

        .message-sender-text {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 75%;
            word-wrap: break-word;
            margin-bottom: 2px;
            display: inline-block;
        }

        .message-receiver {
            align-self: flex-start;
            text-align: left;
            margin-bottom: 10px;
        }

        .message-receiver-text {
            background-color: #f1f1f1;
            color: black;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 75%;
            word-wrap: break-word;
            margin-bottom: 2px;
            display: inline-block;
        }

        .message-meta {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 2px;
        }

        .message-sender .message-meta {
            text-align: right;
        }

        .message-receiver .message-meta {
            text-align: left;
        }

        .table-row-updated {
            background-color: #d4edda !important;
            transition: background-color 1.5s ease-out;
        }

        .table-row-updated-fade {
            background-color: transparent !important;
        }

        /* Style for the action buttons group */
        .action-btn-group {
            display: flex;
            gap: 5px;
            /* Space between buttons */
        }

        /* Style for the details modal */
        #caseDetailsModal .modal-body p {
            margin-bottom: 0.5rem;
            /* Spacing between details */
        }

        #caseDetailsModal .modal-body strong {
            min-width: 120px;
            /* Adjust as needed for alignment */
            display: inline-block;
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

                    <div id="alertPlaceholder"></div>
                    <div hidden class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> Case has been transfered!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
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
                                            <th>Date & Time Opened</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($ongoingCasesTable as $row) {
                                            // Link for chat
                                            // $caseNumberLink = '<a href="#" class="case-number btn btn-link p-0" data-case-id="' . htmlspecialchars($row["id"]) . '" data-case-number="' . htmlspecialchars($row["case_number"]) . '" data-case-owner-name="' . htmlspecialchars($row["case_owner"]) . '">' . htmlspecialchars($row["case_number"]) . '</a>';

                                            $caseNumberLink = '<a href="#" class="case-number btn btn-link p-0" data-case-id="' . htmlspecialchars($row["id"]) . '" data-case-number="' . htmlspecialchars($row["case_number"]) . '" data-case-owner-name="' . htmlspecialchars($row["case_owner"]) . '">' . htmlspecialchars($row["case_number"]) . '</a>';

                                            // Transfer Button
                                            $transferButton = '<button type="button" class="btn btn-sm btn-warning transfer-case-btn" data-case-id="' . htmlspecialchars($row["id"]) . '" data-case-number="' . htmlspecialchars($row["case_number"]) . '" title="Transfer Case"><i class="fas fa-exchange-alt"></i></button>';

                                            // View Details Button - Add ALL data-* attributes here
                                            $viewDetailsButton = '<button type="button" class="btn btn-sm btn-info view-details-btn" ' .
                                                'data-id="' . htmlspecialchars($row["id"]) . '" ' .
                                                'data-case_number="' . htmlspecialchars($row["case_number"]) . '" ' .
                                                'data-type="' . htmlspecialchars($row["type"]) . '" ' .
                                                'data-subject="' . htmlspecialchars($row["subject"]) . '" ' .
                                                'data-product_group="' . htmlspecialchars($row["product_group"]) . '" ' .
                                                'data-product="' . htmlspecialchars($row["product"]) . '" ' .
                                                'data-product_version="' . htmlspecialchars($row["product_version"]) . '" ' .
                                                'data-severity="' . htmlspecialchars($row["severity"]) . '" ' .
                                                'data-case_owner="' . htmlspecialchars($row["case_owner"]) . '" ' .
                                                'data-company="' . htmlspecialchars($row["company"]) . '" ' .
                                                'data-last_modified="' . htmlspecialchars($row["last_modified"]) . '" ' .
                                                'data-datetime_opened="' . htmlspecialchars($row["datetime_opened"]) . '" ' .
                                                'title="View Details">' .
                                                '<i class="fas fa-eye"></i>' .
                                                '</button>';

                                            echo "<tr id='case-row-" . htmlspecialchars($row["id"]) . "'>"; // Add ID to row for easier JS targeting
                                            echo "<td>" . $caseNumberLink . "</td>"; // Keep chat link on case number
                                            echo "<td>" . htmlspecialchars($row["severity"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["company"]) . "</td>";
                                            echo "<td class='last-modified-cell'>" . htmlspecialchars($row["last_modified"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["datetime_opened"]) . "</td>";
                                            // Action column with both buttons
                                            echo '<td><div class="action-btn-group">' . $viewDetailsButton . $transferButton . '</div></td>';
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

    <div class="modal fade" id="chatModal" tabindex="-1" role="dialog" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatModalLabel">Chat</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>  
                    </button>
                </div>
                <div class="modal-body">
                    <div id="chatMessages" style="height: 400px; overflow-y: scroll; border: 1px solid #ccc; margin-bottom: 10px; border-radius: 5px;">
                        <div class="text-center text-muted p-3">Loading messages...</div>
                    </div>
                    <textarea id="chatInput" class="form-control mt-2" placeholder="Type your message here..." rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="sendMessage">Send</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transferCaseModal" tabindex="-1" role="dialog" aria-labelledby="transferCaseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferCaseModalLabel">Transfer Case <span id="transferCaseNumberLabel"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="transferCaseIdInput">
                    <div class="form-group">
                        <label for="engineerList">Select Engineer:</label>
                        <select class="form-control" id="engineerList" required>
                            <option value="">Loading engineers...</option>
                        </select>
                        <div class="invalid-feedback">Please select an engineer.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmTransferBtn" disabled>Transfer</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="caseDetailsModal" tabindex="-1" role="dialog" aria-labelledby="caseDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="caseDetailsModalLabel">Case Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Case Number:</strong> <span id="detailCaseNumber"></span></p>
                    <p><strong>Type:</strong> <span id="detailType"></span></p>
                    <p><strong>Subject:</strong> <span id="detailSubject"></span></p>
                    <p><strong>Product Group:</strong> <span id="detailProductGroup"></span></p>
                    <p><strong>Product:</strong> <span id="detailProduct"></span></p>
                    <p><strong>Product Version:</strong> <span id="detailProductVersion"></span></p>
                    <p><strong>Severity:</strong> <span id="detailSeverity"></span></p>
                    <p><strong>Case Owner:</strong> <span id="detailCaseOwner"></span></p>
                    <p><strong>Company:</strong> <span id="detailCompany"></span></p>
                    <p><strong>Last Modified:</strong> <span id="detailLastModified"></span></p>
                    <p><strong>Date Opened:</strong> <span id="detailDateOpened"></span></p>
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
        // Initialize DataTable
        const dataTable = new DataTable('#table');

        document.addEventListener("DOMContentLoaded", function() {
            // --- Common Elements ---
            const loggedInUserFullName = "<?= $_SESSION["user_full_name"] ?? 'Unknown User' ?>"; // Get session variable safely

            // --- Chat Modal Elements & Variables ---
            const chatModalElement = document.getElementById('chatModal');
            const chatModal = new bootstrap.Modal(chatModalElement);
            const chatMessages = document.getElementById('chatMessages');
            const chatInput = document.getElementById('chatInput');
            const sendMessageButton = document.getElementById('sendMessage');
            let currentCaseNumberForChat = null;
            let fetchChatInterval = null;

            // --- Transfer Case Modal Elements ---
            const transferCaseModalElement = document.getElementById('transferCaseModal');
            const transferCaseModal = new bootstrap.Modal(transferCaseModalElement);
            const engineerListSelect = document.getElementById('engineerList');
            const transferCaseIdInput = document.getElementById('transferCaseIdInput');
            const confirmTransferBtn = document.getElementById('confirmTransferBtn');
            const transferCaseNumberLabel = document.getElementById('transferCaseNumberLabel');

            // --- NEW: Case Details Modal Elements ---
            const caseDetailsModalElement = document.getElementById('caseDetailsModal');
            const caseDetailsModal = new bootstrap.Modal(caseDetailsModalElement);
            // References to spans inside the details modal
            const detailSpans = {
                caseNumber: document.getElementById('detailCaseNumber'),
                type: document.getElementById('detailType'),
                subject: document.getElementById('detailSubject'),
                productGroup: document.getElementById('detailProductGroup'),
                product: document.getElementById('detailProduct'),
                productVersion: document.getElementById('detailProductVersion'),
                severity: document.getElementById('detailSeverity'),
                caseOwner: document.getElementById('detailCaseOwner'),
                company: document.getElementById('detailCompany'),
                lastModified: document.getElementById('detailLastModified'),
                dateOpened: document.getElementById('detailDateOpened')
            };


            // ==================================================
            // VIEW DETAILS FUNCTIONALITY (NEW)
            // ==================================================
            dataTable.on('click', '.view-details-btn', function(event) {
                const button = event.currentTarget;
                const caseData = button.dataset; // Access all data-* attributes

                // Populate the modal - use || '' as a fallback for missing data
                detailSpans.caseNumber.textContent = caseData.case_number || '';
                detailSpans.type.textContent = caseData.type || '';
                detailSpans.subject.textContent = caseData.subject || '';
                detailSpans.productGroup.textContent = caseData.product_group || '';
                detailSpans.product.textContent = caseData.product || '';
                detailSpans.productVersion.textContent = caseData.product_version || '';
                detailSpans.severity.textContent = caseData.severity || '';
                detailSpans.caseOwner.textContent = caseData.case_owner || '';
                detailSpans.company.textContent = caseData.company || '';
                detailSpans.lastModified.textContent = caseData.last_modified || '';
                detailSpans.dateOpened.textContent = caseData.datetime_opened || '';

                // Update modal title (optional)
                document.getElementById('caseDetailsModalLabel').textContent = `Details for Case ${caseData.case_number || 'N/A'}`;

                // Show the modal
                caseDetailsModal.show();
            });


            // ==================================================
            // CHAT FUNCTIONALITY (Existing - Keep As Is)
            // ==================================================

            // --- Event Listener for Case Number Click (Open Chat) ---
            dataTable.on('click', '.case-number', function(event) {
                event.preventDefault();
                const link = event.currentTarget;
                currentCaseNumberForChat = link.getAttribute('data-case-number');
                // currentCaseOwnerForChat = link.getAttribute("data-case-owner-name"); // May not be needed

                document.getElementById('chatModalLabel').textContent = `Chat - Case ${currentCaseNumberForChat}`;
                chatMessages.innerHTML = '<div class="text-center text-muted p-3">Loading messages...</div>';
                chatInput.value = '';

                fetchChatMessages(currentCaseNumberForChat);
                chatModal.show();

                if (fetchChatInterval) clearInterval(fetchChatInterval);
                fetchChatInterval = setInterval(() => {
                    fetchChatMessages(currentCaseNumberForChat);
                }, 5000);
            });

            // --- Function to Fetch Chat Messages ---
            function fetchChatMessages(caseNumber) {
                if (!caseNumber) return;

                fetch(`../fetch/chat_messages.php?case_number=${caseNumber}`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        const wasScrolledToBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= chatMessages.scrollTop + 1;
                        chatMessages.innerHTML = '';

                        if (!Array.isArray(data)) {
                            console.error("Received non-array data for chat messages:", data);
                            chatMessages.innerHTML = '<div class="text-center text-muted p-3">Could not load messages.</div>';
                            return;
                        }
                        if (data.length === 0) {
                            chatMessages.innerHTML = '<div class="text-center text-muted p-3">No messages yet.</div>';
                            return;
                        }

                        data.forEach(message => {
                            const messageWrapper = document.createElement('div');
                            const messageElement = document.createElement('div');
                            const metaElement = document.createElement('div');

                            messageElement.textContent = message.message;
                            metaElement.classList.add('message-meta');

                            let timeString = '';
                            try {
                                if (message.timestamp) {
                                    const date = new Date(message.timestamp);
                                    if (!isNaN(date)) {
                                        timeString = date.toLocaleString([], {
                                            dateStyle: 'short',
                                            timeStyle: 'short'
                                        });
                                    } else {
                                        timeString = 'Invalid Date';
                                    }
                                }
                            } catch (e) {
                                console.error("Error parsing date:", message.timestamp, e);
                                timeString = 'Time Error';
                            }

                            if (message.sender === loggedInUserFullName) {
                                messageWrapper.classList.add('message-sender');
                                messageElement.classList.add('message-sender-text');
                                metaElement.textContent = `You • ${timeString}`;
                            } else {
                                messageWrapper.classList.add('message-receiver');
                                messageElement.classList.add('message-receiver-text');
                                metaElement.textContent = `${message.sender} • ${timeString}`;
                            }

                            messageWrapper.appendChild(messageElement);
                            messageWrapper.appendChild(metaElement);
                            chatMessages.appendChild(messageWrapper);
                        });

                        if (wasScrolledToBottom) {
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching chat messages:', error);
                        if (!chatMessages.hasChildNodes()) {
                            chatMessages.innerHTML = '<div class="text-center text-danger p-3">Error loading messages. Please try again later.</div>';
                        }
                        if (fetchChatInterval) clearInterval(fetchChatInterval); // Stop interval on error
                    });
            }

            // --- Event Listener for Sending Chat Messages ---
            function handleSendMessage() {
                const message = chatInput.value.trim();
                if (message && currentCaseNumberForChat) {
                    sendMessageButton.disabled = true;

                    fetch('../process/send_message.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                case_number: currentCaseNumberForChat,
                                message: message
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                chatInput.value = '';
                                fetchChatMessages(currentCaseNumberForChat);
                                setTimeout(() => chatMessages.scrollTop = chatMessages.scrollHeight, 100);
                            } else {
                                showAlert('Error sending message: ' + (data.message || 'Unknown error'), 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Error sending message:', error);
                            showAlert('Failed to send message. Please check connection and try again.', 'danger');
                        })
                        .finally(() => {
                            sendMessageButton.disabled = false;
                            chatInput.focus();
                        });
                }
            }

            sendMessageButton.addEventListener('click', handleSendMessage);
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    handleSendMessage();
                }
            });

            chatModalElement.addEventListener('hidden.bs.modal', function() {
                if (fetchChatInterval) {
                    clearInterval(fetchChatInterval);
                    fetchChatInterval = null;
                }
                currentCaseNumberForChat = null;
            });


            // ==================================================
            // TRANSFER CASE FUNCTIONALITY (Existing - Keep As Is)
            // ==================================================

            // --- Event Listener for Transfer Case Button Click ---
            dataTable.on('click', '.transfer-case-btn', function(event) {
                const button = event.currentTarget;
                const caseId = button.getAttribute('data-case-id');
                const caseNumber = button.getAttribute('data-case-number');

                transferCaseIdInput.value = caseId;
                transferCaseNumberLabel.textContent = `- ${caseNumber}`;
                engineerListSelect.innerHTML = '<option value="">Loading...</option>';
                engineerListSelect.disabled = true;
                confirmTransferBtn.disabled = true;
                engineerListSelect.classList.remove('is-invalid');

                fetch('../fetch/fetch_engineers.php')
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(engineers => {
                        engineerListSelect.innerHTML = '<option value="">-- Select an Engineer --</option>';
                        if (Array.isArray(engineers) && engineers.length > 0) {
                            engineers.forEach(engineer => {
                                const option = document.createElement('option');
                                option.value = engineer.id; // Ensure value is ID
                                option.textContent = engineer.full_name;
                                engineerListSelect.appendChild(option);
                            });
                            engineerListSelect.disabled = false;
                        } else {
                            engineerListSelect.innerHTML = '<option value="">No engineers found</option>';
                            confirmTransferBtn.disabled = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching engineers:', error);
                        engineerListSelect.innerHTML = '<option value="">Error loading engineers</option>';
                        confirmTransferBtn.disabled = true;
                    });

                transferCaseModal.show();
            });

            engineerListSelect.addEventListener('change', function() {
                confirmTransferBtn.disabled = !this.value; // Enable if value is selected
                if (this.value) {
                    this.classList.remove('is-invalid');
                }
            });


            confirmTransferBtn.addEventListener('click', function() {
                const caseId = transferCaseIdInput.value;
                const selectedEngineerId = engineerListSelect.value;

                if (!selectedEngineerId) {
                    engineerListSelect.classList.add('is-invalid');
                    return;
                } else {
                    engineerListSelect.classList.remove('is-invalid');
                }

                confirmTransferBtn.disabled = true;
                confirmTransferBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Transferring...';

                fetch('../process/process_transfer_case.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            case_id: parseInt(caseId, 10),
                            engineer_id: parseInt(selectedEngineerId, 10)
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errData => {
                                throw new Error(errData.message || `HTTP error! status: ${response.status}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            transferCaseModal.hide();
                            showAlert((data.message || 'Case transferred successfully! Reloading...'), 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000); // Reload after 2 seconds
                        } else {
                            showAlert('Error transferring case: ' + (data.message || 'Unknown error'), 'danger');
                            // Reset button only on failure
                            confirmTransferBtn.disabled = false;
                            if (!engineerListSelect.value) confirmTransferBtn.disabled = true;
                            confirmTransferBtn.innerHTML = 'Transfer';
                        }
                    })
                    .catch(error => {
                        console.error('Error during case transfer fetch:', error);
                        showAlert('Failed to transfer case: ' + error.message, 'danger');
                        // Reset button on fetch error
                        confirmTransferBtn.disabled = false;
                        if (!engineerListSelect.value) confirmTransferBtn.disabled = true;
                        confirmTransferBtn.innerHTML = 'Transfer';
                    });
                // Note: .finally() removed here as success path reloads the page, making reset unnecessary there.
            });

            // --- showAlert function ---
            function showAlert(message, type = 'success') {
                const alertPlaceholder = document.getElementById('alertPlaceholder');
                if (!alertPlaceholder) {
                    console.error("Alert placeholder 'alertPlaceholder' not found!");
                    return;
                }
                const wrapper = document.createElement('div');
                wrapper.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                alertPlaceholder.innerHTML = ''; // Clear existing alerts first
                alertPlaceholder.append(wrapper);
            }

        }); // End DOMContentLoaded
    </script>

</body>

</html>