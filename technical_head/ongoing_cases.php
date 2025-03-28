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
        /* --- Your existing styles --- */
        /* Styling for chat messages */
        #chatMessages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            /* Space between messages */
            padding: 10px;
            /* Add some padding inside the chat container */
        }

        /* Sender's message (align to the right) */
        .message-sender {
            align-self: flex-end;
            /* Align to the right */
            text-align: right;
            /* Ensure the text aligns right */
            margin-bottom: 10px;
            /* Add spacing below messages */
        }

        .message-sender-text {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 75%;
            /* Adjusted max-width */
            word-wrap: break-word;
            /* Ensure long words wrap */
            margin-bottom: 2px;
            /* Reduced space */
            display: inline-block;
            /* Important for alignment */
        }

        /* Receiver's message (align to the left) */
        .message-receiver {
            align-self: flex-start;
            /* Align to the left */
            text-align: left;
            /* Ensure the text aligns left */
            margin-bottom: 10px;
            /* Add spacing below messages */
        }

        .message-receiver-text {
            background-color: #f1f1f1;
            color: black;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 75%;
            /* Adjusted max-width */
            word-wrap: break-word;
            /* Ensure long words wrap */
            margin-bottom: 2px;
            /* Reduced space */
            display: inline-block;
            /* Important for alignment */
        }

        .message-meta {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 2px;
            /* Space between text and meta */
        }

        /* Add specific styling for sender/receiver meta if needed */
        .message-sender .message-meta {
            text-align: right;
            /* Align meta right for sender */
        }

        .message-receiver .message-meta {
            text-align: left;
            /* Align meta left for receiver */
        }

        /* Style for highlighting updated rows */
        .table-row-updated {
            background-color: #d4edda !important;
            /* Light green using !important to override potential conflicts */
            transition: background-color 1.5s ease-out;
            /* Smooth transition back */
        }

        .table-row-updated-fade {
            background-color: transparent !important;
            /* Target state for transition */
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
                                            <th>Type</th>
                                            <th>Subject</th>
                                            <th>Product Group</th>
                                            <th>Product</th>
                                            <th>Product Version</th>
                                            <th>Severity</th>
                                            <th>Case Owner</th>
                                            <th>Company</th>
                                            <th>Last Modified</th>
                                            <th>Date & Time Opened</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Assuming $ongoingCasesTable is populated correctly by technical_ongoing_cases.php
                                        // Ensure 'id' is the primary key of the 'cases' table
                                        // Ensure 'user_id' in 'cases' links to 'users' table 'id'
                                        // Ensure 'case_owner' contains the user's full name (fetched via JOIN in technical_ongoing_cases.php)
                                        foreach ($ongoingCasesTable as $row) {
                                            $caseNumberLink = '<a href="#" class="case-number btn btn-link p-0" data-case-id="' . htmlspecialchars($row["id"]) . '" data-case-number="' . htmlspecialchars($row["case_number"]) . '" data-case-owner-name="' . htmlspecialchars($row["case_owner"]) . '">' . htmlspecialchars($row["case_number"]) . '</a>';

                                            // --- Action Button ---
                                            $transferButton = '<button type="button" class="btn btn-sm btn-warning transfer-case-btn" data-case-id="' . htmlspecialchars($row["id"]) . '" data-case-number="' . htmlspecialchars($row["case_number"]) . '" title="Transfer Case"><i class="fas fa-exchange-alt"></i></button>';

                                            echo "<tr id='case-row-" . htmlspecialchars($row["id"]) . "'>"; // Add ID to row for easier JS targeting
                                            echo "<td>" . $caseNumberLink . "</td>";
                                            echo "<td>" . htmlspecialchars($row["type"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["subject"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["product_group"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["product"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["product_version"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["severity"]) . "</td>";
                                            echo "<td class='case-owner-cell'>" . htmlspecialchars($row["case_owner"]) . "</td>"; // Add class for easier JS targeting
                                            echo "<td>" . htmlspecialchars($row["company"]) . "</td>";
                                            echo "<td class='last-modified-cell'>" . htmlspecialchars($row["last_modified"]) . "</td>"; // Add class for easier JS targeting
                                            echo "<td>" . htmlspecialchars($row["datetime_opened"]) . "</td>";
                                            echo "<td>" . $transferButton . "</td>"; // Action buttons in the last cell
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
            // let currentCaseOwnerForChat = null; // Removed, sender derived server-side or from session
            let fetchChatInterval = null; // To store the interval reference for chat

            // --- Transfer Case Modal Elements ---
            const transferCaseModalElement = document.getElementById('transferCaseModal');
            const transferCaseModal = new bootstrap.Modal(transferCaseModalElement);
            const engineerListSelect = document.getElementById('engineerList');
            const transferCaseIdInput = document.getElementById('transferCaseIdInput');
            const confirmTransferBtn = document.getElementById('confirmTransferBtn');
            const transferCaseNumberLabel = document.getElementById('transferCaseNumberLabel');

            // ==================================================
            // CHAT FUNCTIONALITY
            // ==================================================

            // --- Event Listener for Case Number Click (Open Chat) ---
            dataTable.on('click', '.case-number', function(event) {
                event.preventDefault();
                const link = event.currentTarget;
                currentCaseNumberForChat = link.getAttribute('data-case-number');
                // currentCaseOwnerForChat = link.getAttribute("data-case-owner-name"); // May not be needed if sender derived from session

                document.getElementById('chatModalLabel').textContent = `Chat - Case ${currentCaseNumberForChat}`;
                chatMessages.innerHTML = '<div class="text-center text-muted p-3">Loading messages...</div>'; // Show loading state
                chatInput.value = ''; // Clear input field

                fetchChatMessages(currentCaseNumberForChat); // Initial fetch
                chatModal.show();

                // Start polling for new messages
                if (fetchChatInterval) clearInterval(fetchChatInterval);
                fetchChatInterval = setInterval(() => {
                    fetchChatMessages(currentCaseNumberForChat);
                }, 5000); // Poll every 5 seconds
            });

            // --- Function to Fetch Chat Messages ---
            function fetchChatMessages(caseNumber) {
                if (!caseNumber) return; // Don't fetch if no case number

                fetch(`../fetch/chat_messages.php?case_number=${caseNumber}`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        const wasScrolledToBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= chatMessages.scrollTop + 1; // Check if user was at the bottom

                        chatMessages.innerHTML = ''; // Clear previous messages

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
                            const messageWrapper = document.createElement('div'); // Outer wrapper for alignment
                            const messageElement = document.createElement('div'); // Bubble for text
                            const metaElement = document.createElement('div'); // For sender/time

                            messageElement.textContent = message.message;
                            metaElement.classList.add('message-meta');

                            // Format timestamp nicely
                            let timeString = '';
                            try {
                                if (message.timestamp) {
                                    const date = new Date(message.timestamp);
                                    // Check if the date is valid before formatting
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


                            // Check if the message sender is the logged-in user
                            if (message.sender === loggedInUserFullName) {
                                messageWrapper.classList.add('message-sender');
                                messageElement.classList.add('message-sender-text');
                                metaElement.textContent = `You • ${timeString}`; // Indicate 'You' for sender
                            } else {
                                messageWrapper.classList.add('message-receiver');
                                messageElement.classList.add('message-receiver-text');
                                metaElement.textContent = `${message.sender} • ${timeString}`; // Show sender name
                            }

                            messageWrapper.appendChild(messageElement);
                            messageWrapper.appendChild(metaElement);
                            chatMessages.appendChild(messageWrapper);
                        });

                        // Auto-scroll to the latest message ONLY if user was already at the bottom
                        if (wasScrolledToBottom) {
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching chat messages:', error);
                        // Avoid clearing messages if fetch fails, show error instead or keep old messages?
                        if (!chatMessages.hasChildNodes()) { // Only show error if chat is empty
                            chatMessages.innerHTML = '<div class="text-center text-danger p-3">Error loading messages. Please try again later.</div>';
                        }
                        if (fetchChatInterval) clearInterval(fetchChatInterval); // Stop interval on error
                    });
            }

            // --- Event Listener for Sending Chat Messages ---
            function handleSendMessage() {
                const message = chatInput.value.trim();
                if (message && currentCaseNumberForChat) {
                    sendMessageButton.disabled = true; // Prevent double clicks

                    fetch('../process/send_message.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                case_number: currentCaseNumberForChat,
                                // Sender is determined server-side based on session
                                message: message
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                chatInput.value = '';
                                fetchChatMessages(currentCaseNumberForChat); // Fetch immediately after sending
                                // Ensure scroll to bottom after sending own message
                                setTimeout(() => chatMessages.scrollTop = chatMessages.scrollHeight, 100);
                            } else {
                                alert('Error sending message: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error sending message:', error);
                            alert('Failed to send message. Please check connection and try again.');
                        })
                        .finally(() => {
                            sendMessageButton.disabled = false; // Re-enable button
                            chatInput.focus(); // Keep focus on input
                        });
                }
            }

            sendMessageButton.addEventListener('click', handleSendMessage);

            // Allow sending message with Enter key in textarea
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) { // Send on Enter, new line on Shift+Enter
                    e.preventDefault(); // Prevent default Enter behavior (new line)
                    handleSendMessage(); // Trigger send function
                }
            });

            // --- Stop Fetching Chat Messages When Chat Modal is Closed ---
            chatModalElement.addEventListener('hidden.bs.modal', function() {
                if (fetchChatInterval) {
                    clearInterval(fetchChatInterval);
                    fetchChatInterval = null;
                }
                currentCaseNumberForChat = null; // Reset current case
                // Optional: Clear chat content when modal closes?
                // chatMessages.innerHTML = '';
                // chatInput.value = '';
            });


            // ==================================================
            // TRANSFER CASE FUNCTIONALITY
            // ==================================================

            // --- Event Listener for Transfer Case Button Click ---
            dataTable.on('click', '.transfer-case-btn', function(event) {
                const button = event.currentTarget;
                const caseId = button.getAttribute('data-case-id');
                const caseNumber = button.getAttribute('data-case-number');

                // Store case ID and update modal title
                transferCaseIdInput.value = caseId;
                transferCaseNumberLabel.textContent = `- ${caseNumber}`; // Show case number in title
                engineerListSelect.innerHTML = '<option value="">Loading...</option>'; // Reset dropdown
                engineerListSelect.disabled = true; // Disable while loading
                confirmTransferBtn.disabled = true; // Disable transfer button initially
                engineerListSelect.classList.remove('is-invalid'); // Reset validation

                // Fetch the list of engineers
                fetch('../fetch/fetch_engineers.php') // Ensure this path is correct
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(engineers => {
                        engineerListSelect.innerHTML = '<option value="">-- Select an Engineer --</option>'; // Default option
                        if (Array.isArray(engineers) && engineers.length > 0) {
                            engineers.forEach(engineer => {
                                const option = document.createElement('option');
                                // *** FIX APPLIED HERE: Set value to engineer's ID ***
                                option.value = engineer.id;
                                option.textContent = engineer.full_name; // Display name
                                engineerListSelect.appendChild(option);
                            });
                            engineerListSelect.disabled = false; // Enable dropdown
                            // Only enable transfer button if engineers are loaded
                            // confirmTransferBtn.disabled = false; // Let selection enable it below
                        } else {
                            engineerListSelect.innerHTML = '<option value="">No engineers found</option>';
                            confirmTransferBtn.disabled = true; // Keep disabled if no engineers
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching engineers:', error);
                        engineerListSelect.innerHTML = '<option value="">Error loading engineers</option>';
                        confirmTransferBtn.disabled = true; // Keep disabled on error
                    });

                // Show the modal
                transferCaseModal.show();
            });

            // Enable transfer button only when a valid engineer is selected
            engineerListSelect.addEventListener('change', function() {
                if (this.value && this.value !== "") {
                    confirmTransferBtn.disabled = false;
                    this.classList.remove('is-invalid');
                } else {
                    confirmTransferBtn.disabled = true;
                }
            });


            confirmTransferBtn.addEventListener('click', function() {
                const caseId = transferCaseIdInput.value;
                const selectedEngineerId = engineerListSelect.value;

                // Basic validation
                if (!selectedEngineerId) {
                    engineerListSelect.classList.add('is-invalid');
                    return;
                } else {
                    engineerListSelect.classList.remove('is-invalid');
                }

                confirmTransferBtn.disabled = true;
                confirmTransferBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Transferring...';

                // Send data to the server
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
                        // --- SUCCESS ---
                        transferCaseModal.hide(); // Hide the modal first

                        // *** CORRECTED: Display Bootstrap alert ***
                        const successMessage = (data.message || 'Case transferred successfully!');
                        showAlert(successMessage, 'success'); // Use the helper function

                        // *** CORRECTED: Reload the page after a short delay ***
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000); // 3000 milliseconds = 3 seconds

                    } else {
                        // --- FAILURE (as reported by PHP) ---
                        // *** CORRECTED: Display error message using Bootstrap alert ***
                        showAlert('Error transferring case: ' + (data.message || 'Unknown error'), 'danger'); // Use 'danger' type
                    }
                })
                .catch(error => {
                    // --- NETWORK OR OTHER ERRORS ---
                    console.error('Error during case transfer fetch:', error);
                    // *** CORRECTED: Display error message using Bootstrap alert ***
                    showAlert('Failed to transfer case: ' + error.message, 'danger'); // Use 'danger' type
                })
                .finally(() => {
                    // Reset button state only if an error occurred (since success causes a reload)
                    const alertPlaceholder = document.getElementById('alertPlaceholder');
                    // Check if the placeholder contains a success alert (meaning reload is pending)
                    const isSuccess = alertPlaceholder?.querySelector('.alert-success');

                    if (!isSuccess) { // Only reset if not currently showing a success message
                        confirmTransferBtn.disabled = false;
                        if (!engineerListSelect.value) confirmTransferBtn.disabled = true;
                        confirmTransferBtn.innerHTML = 'Transfer';
                    }
                    // No need to reset button on success path as page will reload
                });
            });

            // --- showAlert function (should already be present as you added it) ---
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