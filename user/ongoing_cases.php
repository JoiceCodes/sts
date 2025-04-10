<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
$pageTitle = "On-going Cases";
// Ensure this path is correct for fetching data
require_once "../fetch/ongoing_cases_table_user.php";

// Get current user's full name for chat identification
$currentUserFullName = isset($_SESSION['user_full_name']) ? $_SESSION['user_full_name'] : 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        /* --- Enhanced Chat Modal Styles --- */

        #chatModal .modal-body {
            display: flex;
            flex-direction: column;
            padding: 0;
            /* Remove default body padding */
            height: 500px;
            /* Or adjust as needed */
        }

        /* Chat Messages Container */
        #chatMessages {
            flex-grow: 1;
            /* Takes available space */
            display: flex;
            flex-direction: column;
            gap: 12px;
            /* Space between messages */
            padding: 15px;
            overflow-y: auto;
            background-color: #f8f9fa;
            /* Light background for chat area */
            border-bottom: 1px solid #dee2e6;
            /* Separator */
        }

        #chatMessages .loading-placeholder,
        #chatMessages .no-messages-placeholder {
            text-align: center;
            color: #6c757d;
            margin-top: 20px;
            font-style: italic;
        }

        /* Common message styles */
        .chat-message {
            max-width: 75%;
            /* Slightly narrower */
            word-wrap: break-word;
            padding: 10px 15px;
            border-radius: 18px;
            /* More rounded */
            position: relative;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            line-height: 1.4;
        }

        .chat-message strong {
            display: block;
            font-size: 0.8em;
            margin-bottom: 4px;
            color: inherit;
            /* Inherit color */
            opacity: 0.8;
        }

        /* Sender's message (align to the right) */
        .message-sender {
            align-self: flex-end;
            background-color: #007bff;
            color: white;
            text-align: left;
            border-bottom-right-radius: 5px;
            /* Bubble tail effect */
        }

        /* Receiver's message (align to the left) */
        .message-receiver {
            align-self: flex-start;
            background-color: #ffffff;
            /* White background */
            color: #333;
            /* Darker text */
            border: 1px solid #e9ecef;
            border-bottom-left-radius: 5px;
            /* Bubble tail effect */
        }

        /* Timestamp styling */
        .message-time {
            font-size: 11px;
            /* Smaller timestamp */
            color: rgba(255, 255, 255, 0.8);
            /* Lighter for sender */
            text-align: right;
            margin-top: 5px;
            display: block;
            clear: both;
            /* Ensure it doesn't wrap weirdly */
        }

        .message-receiver .message-time {
            color: rgba(0, 0, 0, 0.5);
            /* Darker for receiver */
        }

        /* Attachment link style */
        .message-attachment a {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 10px;
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.9em;
            color: inherit;
            /* Match bubble text color */
            opacity: 0.9;
        }

        .message-attachment a:hover {
            background-color: rgba(0, 0, 0, 0.2);
            opacity: 1;
        }

        .message-sender .message-attachment a {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .message-sender .message-attachment a:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .message-attachment i {
            margin-right: 5px;
        }


        /* Chat Input Area */
        .chat-input-area {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            /* Align items vertically */
            gap: 10px;
            background-color: #ffffff;
        }

        #chatInput {
            flex-grow: 1;
            /* Textarea takes most space */
            resize: none;
            /* Prevent manual resizing */
            height: auto;
            /* Start small */
            min-height: 40px;
            /* Minimum height */
            max-height: 100px;
            /* Maximum height before scroll */
            overflow-y: auto;
        }

        .attachment-btn {
            background: none;
            border: none;
            color: #6c757d;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
        }

        .attachment-btn:hover {
            color: #007bff;
        }

        #sendMessage {
            flex-shrink: 0;
            /* Prevent send button shrinking */
        }

        /* Attachment Preview Area */
        #attachmentPreview {
            padding: 5px 15px 0px 15px;
            /* Padding above input */
            font-size: 0.85em;
            color: #6c757d;
            display: none;
            /* Hidden by default */
            background-color: #ffffff;
            /* Match input area background */
        }

        #attachmentPreview span {
            background-color: #e9ecef;
            padding: 3px 8px;
            border-radius: 10px;
            margin-right: 5px;
        }

        #attachmentPreview .remove-attachment {
            color: #dc3545;
            cursor: pointer;
            margin-left: 5px;
            font-weight: bold;
        }


        /* --- End Enhanced Chat Modal Styles --- */

        /* Optional: Styling for details modal content */
        #detailsModal .modal-body p {
            margin-bottom: 0.5rem;
        }

        #detailsModal .modal-body strong {
            min-width: 130px;
            /* Adjust as needed */
            display: inline-block;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php" ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/user_topbar.php" ?>
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
                                            <th>Contact Name</th>
                                            <th>Severity</th>
                                            <th>Company</th>
                                            <th>Last Modified</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Assuming $ongoingCasesTable is fetched correctly
                                        if (isset($ongoingCasesTable) && is_array($ongoingCasesTable)) {
                                            foreach ($ongoingCasesTable as $row) {
                                                // Prepare data, using htmlspecialchars for safety
                                                // Case number link still triggers chat modal
                                                $caseNumberLink = '<a href="#" class="case-number btn btn-link p-0" data-case-id="' . htmlspecialchars($row["id"]) . '" data-case-number="' . htmlspecialchars($row["case_number"]) . '" data-case-owner="' . htmlspecialchars($_SESSION["user_id"]) . '">' . htmlspecialchars($row["case_number"]) . '</a>';
                                                $contactName = htmlspecialchars($row["contact_name"]);
                                                $severity = htmlspecialchars($row["severity"]);
                                                $company = htmlspecialchars($row["company"]);
                                                $lastModified = htmlspecialchars($row["last_modified"]); // Assuming this column exists

                                                // Data for the details modal
                                                $type = htmlspecialchars($row["type"]);
                                                $subject = htmlspecialchars($row["subject"]);
                                                $productGroup = htmlspecialchars($row["product_group"]);
                                                $product = htmlspecialchars($row["product"]);
                                                $productVersion = htmlspecialchars($row["product_version"]);
                                                $dateTimeOpened = htmlspecialchars($row["datetime_opened"]); // Assuming this column exists

                                                echo "<tr>";
                                                echo "<td>" . $caseNumberLink . "</td>";   // Display Case Number (link for chat)
                                                echo "<td>" . $contactName . "</td>";      // Display Contact Name
                                                echo "<td>" . $severity . "</td>";         // Display Severity
                                                echo "<td>" . $company . "</td>";          // Display Company
                                                echo "<td>" . $lastModified . "</td>";     // Display Last Modified

                                                // Add the View Details button
                                                echo '<td>';
                                                echo '<button type="button" class="btn btn-info btn-sm view-details-btn"
                                                            data-toggle="modal"
                                                            data-target="#detailsModal"
                                                            data-case-number="' . htmlspecialchars($row["case_number"]) . '"
                                                            data-type="' . $type . '"
                                                            data-subject="' . $subject . '"
                                                            data-product-group="' . $productGroup . '"
                                                            data-product="' . $product . '"
                                                            data-product-version="' . $productVersion . '"
                                                            data-datetime-opened="' . $dateTimeOpened . '">
                                                            <i class="fas fa-eye"></i> View
                                                          </button>';
                                                echo '</td>';

                                                echo "</tr>";
                                            }
                                        } else {
                                            // Handle case where data isn't available or is not an array
                                            echo '<tr><td colspan="6" class="text-center">No ongoing cases found.</td></tr>';
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

    <div class="modal fade" id="chatModal" tabindex="-1" role="dialog" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatModalLabel">Chat for Case #<span id="chat-modal-case-number"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="chatMessages">
                        <div class="loading-placeholder">
                            <i class="fas fa-spinner fa-spin"></i> Loading messages...
                        </div>
                    </div>

                    <div id="attachmentPreview"></div>

                    <div class="chat-input-area">
                        <input type="file" id="chatAttachment" style="display: none;">
                        <label for="chatAttachment" class="attachment-btn mb-0" title="Attach file">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <textarea id="chatInput" class="form-control" rows="1" placeholder="Type your message..."></textarea> <button type="button" class="btn btn-primary" id="sendMessage"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                    <p><strong>Type:</strong> <span id="modal-type"></span></p>
                    <p><strong>Subject:</strong> <span id="modal-subject"></span></p>
                    <p><strong>Product Group:</strong> <span id="modal-product-group"></span></p>
                    <p><strong>Product:</strong> <span id="modal-product"></span></p>
                    <p><strong>Product Version:</strong> <span id="modal-product-version"></span></p>
                    <p><strong>Date & Time Opened:</strong> <span id="modal-datetime-opened"></span></p>
                    <hr>
                    <p><strong>Contact Name:</strong> <span id="modal-contact-name"></span></p>
                    <p><strong>Severity:</strong> <span id="modal-severity"></span></p>
                    <p><strong>Company:</strong> <span id="modal-company"></span></p>
                    <p><strong>Last Modified:</strong> <span id="modal-last-modified"></span></p>
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
        $(document).ready(function() {
            // Initialize DataTable
            var dataTable = new DataTable('#table');

            // --- Details Modal Logic (Unchanged) ---
            $('#table tbody').on('click', '.view-details-btn', function() {
                var button = $(this);
                var caseNumber = button.data('case-number');
                $('#modal-case-number').text(caseNumber);
                $('#modal-type').text(button.data('type'));
                $('#modal-subject').text(button.data('subject'));
                $('#modal-product-group').text(button.data('product-group'));
                $('#modal-product').text(button.data('product'));
                $('#modal-product-version').text(button.data('product-version'));
                $('#modal-datetime-opened').text(button.data('datetime-opened'));
                var row = button.closest('tr');
                $('#modal-contact-name').text(row.find('td:eq(1)').text());
                $('#modal-severity').text(row.find('td:eq(2)').text());
                $('#modal-company').text(row.find('td:eq(3)').text());
                $('#modal-last-modified').text(row.find('td:eq(4)').text());
            });

            // --- Mark as Solved Modal Logic (Unchanged) ---
            const markAsSolvedModal = document.getElementById("markAsSolved");
            const caseNumberHidden = document.getElementById("caseNumber");
            const isReopenHidden = document.getElementById("isReopen");
            if (markAsSolvedModal && caseNumberHidden && isReopenHidden) {
                $(document).on('click', '.mark-as-solved-btn', function(event) {
                    caseNumberHidden.value = this.getAttribute("data-bs-case-number");
                    isReopenHidden.value = this.getAttribute("data-bs-reopen");
                });
            } else {
                console.warn("Mark as Solved modal elements not found.");
            }

            // --- Enhanced Chat Modal Logic ---
            const chatModalElement = document.getElementById('chatModal');
            const chatModal = new bootstrap.Modal(chatModalElement);
            const chatMessagesContainer = $('#chatMessages');
            const chatInput = $('#chatInput');
            const sendMessageButton = $('#sendMessage');
            const chatModalCaseNumSpan = $('#chat-modal-case-number');
            const chatAttachmentInput = $('#chatAttachment');
            const attachmentPreviewArea = $('#attachmentPreview');
            let currentCaseNumberForChat = null;
            let currentCaseOwnerForChat = null;
            let fetchInterval = null;
            let selectedFile = null; // Variable to hold the selected file object

            // Current User Info (from PHP session via JS variable)
            const currentUserFullName = "<?= addslashes($currentUserFullName) ?>";

            // Auto-resize textarea
            chatInput.on('input', function() {
                this.style.height = 'auto'; // Reset height
                this.style.height = (this.scrollHeight) + 'px'; // Set to scroll height
            });

            // Handle file selection
            chatAttachmentInput.on('change', function(event) {
                if (event.target.files && event.target.files[0]) {
                    selectedFile = event.target.files[0];
                    // Display file name and remove button
                    attachmentPreviewArea.html(`
                        <span><i class="fas fa-paperclip"></i> ${$('<div>').text(selectedFile.name).html()}</span>
                        <span class="remove-attachment" title="Remove attachment">&times;</span>
                    `).show();
                } else {
                    selectedFile = null;
                    attachmentPreviewArea.empty().hide();
                }
            });

            // Handle remove attachment click
            attachmentPreviewArea.on('click', '.remove-attachment', function() {
                selectedFile = null;
                chatAttachmentInput.val(''); // Clear the file input
                attachmentPreviewArea.empty().hide();
            });


            // Event listener for case number clicks (using delegation)
            $('#table tbody').on('click', 'a.case-number', function(event) {
                event.preventDefault();
                currentCaseNumberForChat = $(this).data('case-number');
                currentCaseOwnerForChat = $(this).data('case-owner');

                chatModalCaseNumSpan.text(currentCaseNumberForChat); // Update modal title
                chatMessagesContainer.html('<div class="loading-placeholder"><i class="fas fa-spinner fa-spin"></i> Loading messages...</div>'); // Clear/reset messages area
                chatInput.val(''); // Clear input field
                selectedFile = null; // Clear selected file
                chatAttachmentInput.val(''); // Clear file input
                attachmentPreviewArea.empty().hide(); // Hide preview
                chatInput.css('height', 'auto'); // Reset textarea height

                fetchChatMessages(currentCaseNumberForChat); // Initial fetch

                chatModal.show();

                // Start fetching messages periodically
                if (fetchInterval) clearInterval(fetchInterval);
                fetchInterval = setInterval(() => {
                    if ($(chatModalElement).hasClass('show')) { // Check if modal is still shown
                        fetchChatMessages(currentCaseNumberForChat);
                    } else {
                        clearInterval(fetchInterval); // Stop if modal closed
                        fetchInterval = null;
                    }
                }, 5000); // Fetch every 5 seconds
            });

            // Function to fetch chat messages
            function fetchChatMessages(caseNumber) {
                if (!caseNumber) return;

                const isScrolledToBottom = chatMessagesContainer[0].scrollHeight - chatMessagesContainer[0].clientHeight <= chatMessagesContainer[0].scrollTop + 5; // Check if scrolled near bottom

                $.ajax({
                    url: `../fetch/chat_messages.php?case_number=${caseNumber}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // Store current scroll position if not near bottom
                        const currentScrollTop = chatMessagesContainer.scrollTop();

                        chatMessagesContainer.empty(); // Clear previous messages

                        if (data && data.length > 0) {
                            data.forEach(message => {
                                // Sanitize potentially unsafe HTML content before display
                                const safeSender = $('<div>').text(message.sender).html();
                                const safeMessage = $('<div>').text(message.message).html().replace(/\n/g, '<br>'); // Convert newlines
                                const safeTime = $('<div>').text(message.created_at).html();
                                const messageClass = (message.sender === currentUserFullName) ? 'message-sender' : 'message-receiver';

                                // Build attachment HTML if present
                                let attachmentHtml = '';
                                // **** BACKEND MUST PROVIDE attachment_url and attachment_name ****
                                if (message.attachment_url && message.attachment_name) {
                                    const safeAttachmentName = $('<div>').text(message.attachment_name).html();
                                    const safeAttachmentUrl = $('<div>').text(message.attachment_url).html(); // Basic sanitization for URL too
                                    attachmentHtml = `
                                        <div class="message-attachment">
                                            <a href="${safeAttachmentUrl}" target="_blank" download="${safeAttachmentName}">
                                                <i class="fas fa-file-alt"></i> ${safeAttachmentName}
                                            </a>
                                        </div>`;
                                }

                                const messageHtml = `
                                     <div class="chat-message ${messageClass}">
                                         <strong>${safeSender}</strong>
                                         ${safeMessage}
                                         ${attachmentHtml}
                                         <span class="message-time">${safeTime}</span>
                                     </div>`;
                                chatMessagesContainer.append(messageHtml);
                            });

                            // Restore scroll position or scroll to bottom
                            if (isScrolledToBottom) {
                                chatMessagesContainer.scrollTop(chatMessagesContainer[0].scrollHeight);
                            } else {
                                chatMessagesContainer.scrollTop(currentScrollTop);
                            }

                        } else {
                            chatMessagesContainer.html('<div class="no-messages-placeholder">No messages yet. Start the conversation!</div>');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error fetching chat messages:', textStatus, errorThrown);
                        chatMessagesContainer.html('<p class="text-center text-danger">Error loading messages. Please try again later.</p>');
                    }
                });
            }

            // Function to send message (handles text and optional attachment)
            function sendMessage() {
                const message = chatInput.val().trim();

                // Requires either a message or a file to send
                if ((!message && !selectedFile) || !currentCaseNumberForChat) {
                    if (!message && !selectedFile) console.log("No message or file to send.");
                    if (!currentCaseNumberForChat) console.log("No case number selected.");
                    return;
                }

                sendMessageButton.prop('disabled', true); // Disable button
                chatInput.prop('disabled', true);
                $('.attachment-btn').css('pointer-events', 'none'); // Disable attachment button via label click

                // Use FormData to handle file uploads
                const formData = new FormData();
                formData.append('case_number', currentCaseNumberForChat);
                formData.append('case_owner', currentCaseOwnerForChat); // Make sure your backend uses this if needed
                formData.append('message', message);

                if (selectedFile) {
                    formData.append('attachment', selectedFile, selectedFile.name); // Add the file
                }

                $.ajax({
                    url: '../process/send_message.php', // Your backend script to handle saving
                    method: 'POST',
                    data: formData,
                    processData: false, // Important! Prevent jQuery from processing the data
                    contentType: false, // Important! Let the browser set the correct content type for FormData
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            chatInput.val(''); // Clear input
                            chatInput.css('height', 'auto'); // Reset textarea height
                            selectedFile = null; // Clear selected file variable
                            chatAttachmentInput.val(''); // Clear file input element
                            attachmentPreviewArea.empty().hide(); // Clear preview
                            fetchChatMessages(currentCaseNumberForChat); // Refresh messages immediately
                        } else {
                            console.error("Error sending message:", data.error || "Unknown backend error");
                            // Optionally show an error message to the user in the UI
                            alert("Error sending message: " + (data.error || "Please try again."));
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error sending message via AJAX:', textStatus, errorThrown, jqXHR.responseText);
                        alert("Failed to send message due to a network or server error.");
                    },
                    complete: function() {
                        sendMessageButton.prop('disabled', false); // Re-enable button
                        chatInput.prop('disabled', false);
                        $('.attachment-btn').css('pointer-events', 'auto');
                    }
                });
            }

            // Event listener for sending messages via Button
            sendMessageButton.on('click', sendMessage);

            // Allow sending message with Enter key in textarea (Shift+Enter for newline)
            chatInput.on('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault(); // Prevent default Enter behavior (newline)
                    sendMessage(); // Trigger send function
                }
            });

            // Stop fetching messages when the chat modal is closed
            $(chatModalElement).on('hidden.bs.modal', function() {
                if (fetchInterval) {
                    clearInterval(fetchInterval);
                    fetchInterval = null;
                    console.log("Chat polling stopped.");
                }
                // Optional: Clear chat content when modal closes fully
                // chatMessagesContainer.empty();
                // chatInput.val('');
                // selectedFile = null;
                // chatAttachmentInput.val('');
                // attachmentPreviewArea.empty().hide();
                // currentCaseNumberForChat = null;
            });

        }); // End $(document).ready
    </script>

</body>

</html>