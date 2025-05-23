<?php
session_start();
// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Ensure PHPMailer autoloader is included if needed directly on this page
// (Usually better handled in a central bootstrap or config file)
// require_once __DIR__ . '/../vendor/autoload.php'; // Adjust path if needed

require_once __DIR__ . "/../config/database.php"; // For session check, maybe user info

// --- Security Check ---
if (!isset($_SESSION["user_full_name"])) {
    header("Location: ../../auth/login.php");
    exit();
}
// --- End Security Check ---

$pageTitle = "Emails"; // Changed title
$default_since_date = date("Y-m-d");

// Placeholder for SMTP configuration (LOAD THESE SECURELY!)
// Example using environment variables:
$smtp_host = getenv('SMTP_HOST') ?: 'your_smtp_host';
$smtp_port = getenv('SMTP_PORT') ?: 587;
$smtp_username = getenv('SMTP_USERNAME') ?: 'your_smtp_username';
$smtp_password = getenv('SMTP_PASSWORD') ?: 'your_smtp_password'; // Sensitive!
$smtp_secure = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_STARTTLS; // or ENCRYPTION_SMTPS
$from_email = getenv('FROM_EMAIL') ?: 'noreply@yourdomain.com'; // Default sending address
$from_name = getenv('FROM_NAME') ?: 'i-Secure System';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once __DIR__ . "/../components/head.php"; ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> - i-Secure</title>
    <style>
        /* --- Keep your existing styles --- */
        .email-layout {
            display: flex;
            height: calc(100vh - 180px);
            overflow: hidden;
            border-top: 1px solid #e3e6f0;
        }

        #email-list-pane {
            width: 35%;
            border-right: 1px solid #e3e6f0;
            height: 100%;
            overflow-y: auto;
            background-color: #f8f9fc;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        #email-detail-pane {
            width: 65%;
            height: 100%;
            overflow-y: auto;
            padding: 25px;
            background-color: #ffffff;
        }

        #email-detail-placeholder {
            text-align: center;
            padding-top: 60px;
            color: #b7b9cc;
        }

        #list-header {
            padding: 10px 15px;
            border-bottom: 1px solid #e3e6f0;
            background-color: #fff;
        }

        #list-header label {
            margin-bottom: 0.25rem;
            font-size: 0.8rem;
            font-weight: 500;
        }

        #fetch-emails-btn .spinner-border-sm {
            width: 1em;
            height: 1em;
            border-width: .2em;
        }

        #email-list-container {
            min-height: 50px;
            position: relative;
            flex-grow: 1;
            overflow-y: auto;
        }

        .loading-indicator {
            padding: 30px;
            text-align: center;
            color: #858796;
        }

        .no-emails-message {
            padding: 30px;
            text-align: center;
            color: #858796;
        }

        #reply-status,
        #compose-status {
            margin-top: 10px;
            font-weight: bold;
        }

        #detail-reply-form .btn-primary[disabled],
        #compose-email-form .btn-primary[disabled] {
            cursor: not-allowed;
            opacity: 0.65;
        }

        #detail-reply-form {
            margin-top: 2rem;
            border-top: 1px solid #e3e6f0;
            padding-top: 1.5rem;
        }

        .email-item {
            display: flex;
            align-items: center;
            cursor: pointer;
            border-bottom: 1px solid #e3e6f0;
            padding: 12px 15px;
            position: relative;
            transition: background-color 0.15s ease-in-out;
            background-color: #fff;
            font-weight: 600;
        }

        .email-item::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background-color: #4e73df;
            border-radius: 50%;
        }

        .email-item .email-content {
            flex-grow: 1;
            overflow: hidden;
            padding-left: 15px;
        }

        .email-item .email-sender {
            color: #6c757d;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.8rem;
            font-weight: normal;
        }

        .email-item .email-subject {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
            font-size: 0.9rem;
            color: #3a3b45;
        }

        .email-item .email-date {
            font-size: 0.75rem;
            color: #858796;
            font-weight: normal;
        }

        .email-item:hover {
            background-color: #e9ecef;
        }

        .email-item.active {
            background-color: #cfe2ff !important;
            border-left: 4px solid #4e73df;
            padding-left: 11px;
            font-weight: 600;
        }

        .email-item.active .email-content {
            padding-left: 11px;
        }

        .email-item.read {
            font-weight: normal;
            color: #858796;
            background-color: #f8f9fc;
        }

        .email-item.read::before {
            display: none;
        }

        .email-item.read .email-sender {
            color: #858796;
            font-weight: normal;
        }

        .email-item.read .email-subject {
            color: #858796;
        }

        .email-item.unread .email-sender {
            font-weight: 500;
            color: #5a5c69;
        }

        #detail-subject {
            margin-bottom: 0.75rem;
            font-size: 1.3rem;
            color: #3a3b45;
        }

        #detail-from,
        #detail-date {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .email-detail-body-plain {
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: #ffffff;
            border: none;
            padding: 5px 0;
            max-height: none;
            overflow-y: visible;
            font-family: sans-serif;
            font-size: 0.9rem;
            line-height: 1.6;
            color: #5a5c69;
        }

        .email-detail-body-html iframe {
            width: 100%;
            min-height: 250px;
            border: 1px solid #dee2e6;
        }

        @media (max-width: 992px) {
            #email-list-pane {
                width: 40%;
            }

            #email-detail-pane {
                width: 60%;
            }
        }

        @media (max-width: 768px) {
            .email-layout {
                flex-direction: column;
                height: auto;
            }

            #email-list-pane {
                width: 100%;
                max-height: 40vh;
            }

            #email-detail-pane {
                width: 100%;
                max-height: none;
                padding: 20px;
            }
        }

        /* --- End existing styles --- */
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once __DIR__ . "/../components/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once __DIR__ . "/../components/user_topbar.php"; ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                        <div>
                            <button type="button" class="btn btn-sm btn-success shadow-sm mr-2" data-toggle="modal" data-target="#composeEmailModal">
                                <i class="fas fa-edit fa-sm text-white-50"></i> Compose Email
                            </button>
                            <a href="read_emails.php" class="d-none d-sm-inline-block btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fas fa-envelope-open-text fa-sm text-primary-50"></i> View Read Emails
                            </a>
                        </div>
                    </div>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Inbox</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="email-layout">
                                <div id="email-list-pane">
                                    <div id="list-header">
                                        <div class="form-row align-items-end">
                                            <div class="col">
                                                <label for="since-date-input">Show emails since:</label>
                                                <input type="date" id="since-date-input" class="form-control form-control-sm" value="<?= htmlspecialchars($default_since_date) ?>">
                                            </div>
                                            <div class="col-auto">
                                                <button id="fetch-emails-btn" class="btn btn-sm btn-primary">Fetch</button>
                                            </div>
                                        </div>
                                        <div id="error-message-list" class="text-danger pt-2 small" style="display: none;"></div>
                                    </div>
                                    <div id="email-list-container">
                                        <div class="loading-indicator">Loading today's emails...</div>
                                    </div>
                                    <div class="p-3 d-block d-sm-none">
                                        <a href="read_emails.php" class="btn btn-sm btn-outline-primary btn-block">View Read Emails</a>
                                        <button type="button" class="btn btn-sm btn-success btn-block mt-2" data-toggle="modal" data-target="#composeEmailModal">Compose Email</button>
                                    </div>
                                </div>
                                <div id="email-detail-pane">
                                    <div id="email-detail-placeholder"><i class="fas fa-envelope fa-3x text-gray-300 mb-3"></i>
                                        <p>Select an email from the list or compose a new one.</p>
                                    </div>
                                    <div id="email-detail-content" style="display: none;">
                                        <h4 id="detail-subject"></h4>
                                        <hr class="mt-2 mb-3">
                                        <div class="d-flex justify-content-between mb-3">
                                            <p class="mb-0"><strong>From:</strong> <span id="detail-from"></span></p>
                                            <p class="mb-0 text-muted"><small id="detail-date"></small></p>
                                        </div>
                                        <hr class="mt-0 mb-3">
                                        <div id="detail-body">
                                            <div class="loading-indicator">Loading body...</div>
                                        </div>
                                        <div id="error-message-detail" class="text-danger mt-2 small" style="display: none;"></div>
                                        <form id="detail-reply-form">
                                            <hr class="mt-4">
                                            <h5>Reply</h5> <input type="hidden" id="reply-original-msgno" name="original_msgno"> <input type="hidden" id="reply-original-message-id" name="original_message_id">
                                            <div class="form-group"><label for="reply-to" class="small">To:</label><input type="text" id="reply-to" name="reply_to" class="form-control form-control-sm" readonly required></div>
                                            <div class="form-group"><label for="reply-subject" class="small">Subject:</label><input type="text" id="reply-subject" name="reply_subject" class="form-control form-control-sm" required></div>
                                            <div class="form-group"><label for="reply-body" class="small">Message:</label><textarea id="reply-body" name="reply_body" class="form-control form-control-sm" rows="5" required></textarea></div> <button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
                                            <div id="reply-status" class="small mt-2 d-inline-block ml-3"></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once __DIR__ . "/../components/footer.php"; ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <?php include_once __DIR__ . "/../modals/logout.php"; ?>

    <div class="modal fade" id="composeEmailModal" tabindex="-1" role="dialog" aria-labelledby="composeEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="compose-email-form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="composeEmailModalLabel">Compose New Email</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="compose-to">To:</label>
                            <input type="email" class="form-control" id="compose-to" name="compose_to" placeholder="recipient@example.com" required>
                            <small class="form-text text-muted">Enter one email address.</small>
                        </div>
                        <div class="form-group">
                            <label for="compose-subject">Subject:</label>
                            <input type="text" class="form-control" id="compose-subject" name="compose_subject" required>
                        </div>
                        <div class="form-group">
                            <label for="compose-body">Message:</label>
                            <textarea class="form-control" id="compose-body" name="compose_body" rows="8" required></textarea>
                        </div>
                        <div id="compose-status" class="small mt-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Send Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script>
        let currentDisplayedEmails = {};
        let initialLoadAttempted = false;
        let activeMsgNo = null;
        let isFetching = false; // For list fetching
        let isSendingCompose = false; // For compose sending
        let isSendingReply = false; // For reply sending

        function displayError(message, area = 'list') {
            const errorDivId = area === 'detail' ? '#error-message-detail' :
                area === 'compose' ? '#compose-status' // Use status div for compose errors too
                :
                '#error-message-list';
            const errorDiv = $(errorDivId);
            errorDiv.text(message).removeClass('text-success').addClass('text-danger').show();
            // If it's the compose status div, ensure it's visible
            if (area === 'compose') {
                errorDiv.css('display', 'block'); // Ensure visibility
            }
        }

        function displayStatus(message, area = 'reply', type = 'info') {
            const statusDivId = area === 'compose' ? '#compose-status' : '#reply-status';
            const statusDiv = $(statusDivId);
            statusDiv.text(message)
                .removeClass('text-danger text-success text-info')
                .addClass(type === 'success' ? 'text-success' : type === 'error' ? 'text-danger' : 'text-info')
                .show();
            statusDiv.css('display', 'block'); // Ensure visibility if it was hidden
        }


        function clearErrors(area = 'all') {
            if (area === 'all' || area === 'list') {
                $('#error-message-list').text('').hide();
            }
            if (area === 'all' || area === 'detail') {
                $('#error-message-detail').text('').hide();
                $('#reply-status').text('').removeClass('text-danger text-success text-info').hide();
            }
            if (area === 'all' || area === 'compose') {
                $('#compose-status').text('').removeClass('text-danger text-success text-info').hide();
            }
        }

        function renderEmailList(emails) {
            const container = $('#email-list-container');
            container.empty();
            currentDisplayedEmails = {};
            clearErrors('list');
            if (!emails) emails = [];
            if (emails.length === 0) {
                // Check if initial load has been tried to show appropriate message
                const message = initialLoadAttempted ? 'No unread emails found since the selected date.' : 'Loading emails...';
                container.html(`<p class="no-emails-message">${message}</p>`);
                if (activeMsgNo === null) {
                    $('#email-detail-placeholder').show();
                    $('#email-detail-content').hide();
                }
            } else {
                emails.forEach(email => {
                    const msgno = email.message_no;
                    const readStatusClass = (email.seen === true) ? 'read' : 'unread';
                    const isActive = msgno === activeMsgNo ? ' active' : '';
                    const senderInfo = email.from ? `<div class="email-sender small">${escapeHtml(email.from)}</div>` : '';
                    const newItemHtml = `
                        <div class="email-item ${readStatusClass}${isActive}" data-msgno="${msgno}" onclick="viewEmail(${msgno})">
                             <div class="email-content">
                                 ${senderInfo}
                                 <span class="email-subject">${escapeHtml(email.subject)}</span>
                                 <div class="email-date">${escapeHtml(new Date(email.sent_at).toLocaleString())}</div>
                             </div>
                        </div>`;
                    container.append(newItemHtml);
                    currentDisplayedEmails[msgno] = email;
                });
                if (activeMsgNo === null || !currentDisplayedEmails[activeMsgNo]) {
                    if (!$('#email-detail-content').is(':visible') || activeMsgNo !== null) {
                        $('#email-detail-placeholder').show();
                        $('#email-detail-content').hide();
                        activeMsgNo = null; // Reset active message if it's no longer in the list
                    }
                }
                initialLoadAttempted = true; // Mark that we have processed emails
            }
            // Moved initialLoadAttempted update here
        }

        function fetchEmailList(sinceDate) {
            if (isFetching) return;
            if (!sinceDate) {
                displayError("Please select a date.", 'list');
                return;
            }
            isFetching = true;
            // initialLoadAttempted = false; // Reset only if needed, or manage state differently
            $('#email-list-container').html('<div class="loading-indicator">Loading emails...</div>');
            $('#fetch-emails-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Fetching...');
            clearErrors('list');

            // If fetching new list, clear the detail pane if an email was selected
            if (activeMsgNo !== null) {
                $('#email-detail-placeholder').show();
                $('#email-detail-content').hide();
                activeMsgNo = null;
            }

            $.ajax({
                url: `api_get_emails.php?status=unread&since_date=${sinceDate}`,
                method: 'GET',
                dataType: 'json',
                timeout: 30000, // Increased timeout
                success: function(response) {
                    if (response.error) {
                        displayError('Error loading list: ' + response.error);
                        renderEmailList([]); // Show empty state
                    } else {
                        renderEmailList(response.emails || []);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("List fetch error:", status, error, xhr.responseText); // Log more details
                    if (status !== 'abort') {
                        displayError('Error loading list. Check connection or server status.');
                        renderEmailList([]); // Show empty state on error too
                    }
                },
                complete: function() {
                    // Ensure placeholder or message is shown if loading fails or returns empty
                    if ($('#email-list-container').find('.email-item').length === 0 && $('#email-list-container').find('.no-emails-message').length === 0) {
                        $('#email-list-container').html('<p class="no-emails-message">No unread emails found or failed to load.</p>');
                    }
                    isFetching = false;
                    initialLoadAttempted = true; // Set here after attempt completes
                    $('#fetch-emails-btn').prop('disabled', false).text('Fetch');
                }
            });
        }

        function viewEmail(msgno) {
            if (!msgno) return;
            if (isFetching) return; // Don't interfere with list fetching
            clearErrors('detail'); // Clear previous detail errors/status
            $('#detail-reply-form button[type="submit"]').prop('disabled', false); // Re-enable reply button initially

            // Immediately update UI
            $('#email-detail-placeholder').hide();
            $('#email-detail-content').show();
            $('#detail-body').html('<div class="loading-indicator">Loading email...</div>');
            $('#detail-subject, #detail-from, #detail-date').text(''); // Clear previous details
            $('#reply-original-msgno, #reply-original-message-id, #reply-to, #reply-subject, #reply-body').val(''); // Clear reply form

            // --- Visual update for list items ---
            // Deactivate previous item if different
            if (activeMsgNo !== null && activeMsgNo !== msgno) {
                const previousItem = $(`#email-list-container .email-item[data-msgno="${activeMsgNo}"]`);
                if (previousItem.length > 0) {
                    // Only visually mark as read if it wasn't already
                    if (previousItem.hasClass('unread')) {
                        previousItem.removeClass('unread').addClass('read');
                    }
                    previousItem.removeClass('active');
                }
            }
            // Activate current item
            const selectedItem = $(`#email-list-container .email-item[data-msgno="${msgno}"]`);
            $('#email-list-container .email-item').removeClass('active'); // Deactivate all first
            selectedItem.addClass('active'); // Activate current
            activeMsgNo = msgno; // Update the active message number
            // --- End visual update ---


            $.ajax({
                url: `api_get_email_details.php?msgno=${msgno}`,
                method: 'GET',
                dataType: 'json',
                timeout: 20000,
                success: function(response) {
                    // Only process if the response is for the currently selected email
                    if (msgno !== activeMsgNo) return;

                    if (response.error) {
                        displayError('Error fetching email details: ' + response.error, 'detail');
                        $('#detail-body').html('<p class="text-danger">Could not load email content.</p>');
                        // Optionally revert active state if load fails
                        // selectedItem.removeClass('active');
                        // activeMsgNo = null;
                    } else if (response.details) {
                        displayEmailDetails(response.details);
                        // Mark as read visually ONLY if it was unread before clicking
                        if (selectedItem.hasClass('unread')) {
                            selectedItem.removeClass('unread').addClass('read');
                        }
                        // Check if list is empty of UNREAD emails *after* marking as read
                        if ($('#email-list-container').find('.email-item.unread').length === 0) {
                            // You could add a message here if needed, but the list still shows read items
                        }
                    } else {
                        // Handle case where details are missing but no specific error reported
                        displayError('Could not retrieve email details.', 'detail');
                        $('#detail-body').html('<p class="text-warning">Email content unavailable.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    if (msgno !== activeMsgNo) return;
                    displayError('Network error fetching email details.', 'detail');
                    $('#detail-body').html('<p class="text-danger">Could not load email content due to network error.</p>');
                    console.error("Detail fetch error:", status, error);
                    // Optionally revert active state on network error
                    // selectedItem.removeClass('active');
                    // activeMsgNo = null;
                }
            });
        }

        function displayEmailDetails(details) {
            $('#detail-subject').text(details.subject || '(No Subject)');
            $('#detail-from').text(details.from || 'N/A');
            $('#detail-date').text(details.date || 'N/A');
            $('#reply-original-msgno').val(details.message_no || '');
            $('#reply-original-message-id').val(details.message_id || '');

            // Prepare Reply Form Fields
            let replyToAddress = details.from || '';
            const emailMatch = replyToAddress.match(/<([^>]+)>/); // Extract email from "Name <email@addr.com>" format
            if (emailMatch && emailMatch[1]) replyToAddress = emailMatch[1];
            $('#reply-to').val(replyToAddress);
            $('#reply-subject').val('Re: ' + (details.subject || ''));
            // Add original message to reply body (optional, common practice)
            const originalMessageQuote = `\n\n----- Original Message -----\nFrom: ${details.from}\nDate: ${details.date}\nSubject: ${details.subject}\n\n${details.body_plain || '(HTML Content)'}`;
            $('#reply-body').val(originalMessageQuote); // Clear previous reply content and add quote

            // Display Body
            let bodyContent = '<p><em>(Email content not available or could not be displayed.)</em></p>';
            if (details.body_plain) {
                bodyContent = `<div class="email-detail-body-plain">${escapeHtml(details.body_plain)}</div>`;
            } else if (details.body_html) {
                // Basic HTML sanitization/display (iframe is safer but more complex)
                // Displaying HTML directly can be risky. Text extraction is safer.
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = details.body_html; // Let browser parse
                const extractedText = tempDiv.textContent || tempDiv.innerText || "";
                bodyContent = `<div class="email-detail-body-plain">${escapeHtml(extractedText)}</div><p class="text-muted small mt-2"><em>(Displayed text extracted from HTML content. Formatting may be lost.)</em></p>`;
                // Alternative: Use an iframe (requires more setup for sandboxing/styling)
                // bodyContent = `<div class="email-detail-body-html"><iframe srcdoc="${escapeHtml(details.body_html)}" sandbox></iframe></div>`;
            }
            $('#detail-body').html(bodyContent);

            $('#detail-reply-form button[type="submit"]').prop('disabled', false); // Ensure reply button is enabled
            $('#reply-status').text('').hide(); // Clear previous reply status
        }


        function escapeHtml(unsafe) {
            if (unsafe === null || typeof unsafe === 'undefined') return '';
            try {
                return unsafe.toString()
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            } catch (e) {
                console.error("Error escaping HTML:", e, unsafe);
                return '';
            }
        }

        // --- REPLY SUBMISSION ---
        function handleReplySubmit(event) {
            event.preventDefault();
            if (isSendingReply) return; // Prevent double submission

            clearErrors('detail'); // Clear previous errors/status in the detail pane
            const form = $(event.target);
            const submitButton = form.find('button[type="submit"]');
            const statusDiv = $('#reply-status');
            const formData = {
                reply_to: $('#reply-to').val(),
                reply_subject: $('#reply-subject').val(),
                reply_body: $('#reply-body').val(),
                original_message_id: $('#reply-original-message-id').val() // Send original Message-ID for threading
            };

            // Basic Validation
            if (!formData.reply_to || !formData.reply_subject || !formData.reply_body) {
                displayStatus('Please fill in all reply fields.', 'reply', 'error');
                return;
            }

            isSendingReply = true;
            submitButton.prop('disabled', true);
            displayStatus('Sending reply...', 'reply', 'info');

            $.ajax({
                url: 'api_send_reply.php', // Your reply API endpoint
                method: 'POST',
                data: formData,
                dataType: 'json',
                timeout: 30000, // Longer timeout for sending
                success: function(response) {
                    if (response.success) {
                        displayStatus('Reply sent successfully!', 'reply', 'success');
                        setTimeout(() => {
                            // Optionally clear only the body after sending reply
                            // $('#reply-body').val(''); // Keep subject/to filled
                            displayStatus('', 'reply', 'info'); // Hide status message
                        }, 3000);
                    } else {
                        displayStatus('Error sending reply: ' + (response.error || 'Unknown error'), 'reply', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Reply send error:", status, error, xhr.responseText);
                    displayStatus('Network error sending reply. Please try again.', 'reply', 'error');
                },
                complete: function() {
                    isSendingReply = false;
                    submitButton.prop('disabled', false); // Re-enable button regardless of outcome
                }
            });
        }

        // --- COMPOSE SUBMISSION ---
        function handleComposeSubmit(event) {
            event.preventDefault();
            if (isSendingCompose) return; // Prevent double submission

            clearErrors('compose'); // Clear previous errors/status in the compose modal
            const form = $(event.target);
            const submitButton = form.find('button[type="submit"]');
            const statusDiv = $('#compose-status');
            const formData = {
                compose_to: $('#compose-to').val(),
                compose_subject: $('#compose-subject').val(),
                compose_body: $('#compose-body').val()
            };

            // Basic Validation
            if (!formData.compose_to || !formData.compose_subject || !formData.compose_body) {
                displayStatus('Please fill in all fields.', 'compose', 'error');
                return;
            }
            // Simple email format check (basic)
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.compose_to)) {
                displayStatus('Please enter a valid recipient email address.', 'compose', 'error');
                return;
            }


            isSendingCompose = true;
            submitButton.prop('disabled', true);
            displayStatus('Sending email...', 'compose', 'info');

            $.ajax({
                url: 'api_send_compose.php', // *** NEW API endpoint for composing ***
                method: 'POST',
                data: formData,
                dataType: 'json',
                timeout: 30000, // Longer timeout for sending
                success: function(response) {
                    if (response.success) {
                        displayStatus('Email sent successfully!', 'compose', 'success');
                        form[0].reset(); // Clear the form fields
                        setTimeout(() => {
                            $('#composeEmailModal').modal('hide'); // Close modal on success
                            displayStatus('', 'compose', 'info'); // Clear status
                        }, 2000);
                    } else {
                        displayStatus('Error sending email: ' + (response.error || 'Unknown error'), 'compose', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Compose send error:", status, error, xhr.responseText);
                    displayStatus('Network error sending email. Please check connection or configuration.', 'compose', 'error');
                },
                complete: function() {
                    isSendingCompose = false;
                    submitButton.prop('disabled', false); // Re-enable button
                }
            });
        }


        $(document).ready(function() {
            // Initial Fetch
            const initialDate = $('#since-date-input').val();
            if (initialDate) {
                fetchEmailList(initialDate);
            } else {
                $('#email-list-container').html('<p class="no-emails-message">Please select a date and click Fetch.</p>');
                initialLoadAttempted = true; // Mark as attempted even if no date initially
            }

            // Event Listeners
            $('#fetch-emails-btn').on('click', function() {
                const selectedDate = $('#since-date-input').val();
                fetchEmailList(selectedDate);
            });

            $('#detail-reply-form').on('submit', handleReplySubmit);

            // *** ADDED: Compose form submission handler ***
            $('#compose-email-form').on('submit', handleComposeSubmit);

            // *** ADDED: Clear compose form/status when modal is hidden ***
            $('#composeEmailModal').on('hidden.bs.modal', function() {
                $('#compose-email-form')[0].reset(); // Clear form fields
                clearErrors('compose'); // Clear status/error messages
            });

        });
    </script>
</body>

</html>