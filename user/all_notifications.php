<?php
session_start();
require_once __DIR__ . "/../config/database.php"; // Ensure path is correct

// Redirect to login if user is not authenticated
if (!isset($_SESSION["user_full_name"])) {
    header("Location: ../../auth/login.php"); // Adjust path if needed
    exit();
}

$pageTitle = "Email Client";
// Set default date for fetching emails (e.g., today)
$default_since_date = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once __DIR__ . "/../components/head.php"; // Ensure path is correct 
    ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> - i-Secure</title>
    <style>
        /* Layout */
        .email-layout {
            display: flex;
            height: calc(100vh - 180px);
            /* Adjust based on header/footer */
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

        /* List Header */
        #list-header {
            padding: 10px 15px;
            border-bottom: 1px solid #e3e6f0;
            background-color: #fff;
            flex-shrink: 0;
            /* Prevent shrinking */
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

        /* Email List */
        #email-list-container {
            min-height: 50px;
            /* Prevent collapse */
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

        /* Email Item */
        .email-item {
            display: flex;
            align-items: center;
            cursor: pointer;
            border-bottom: 1px solid #e3e6f0;
            padding: 12px 15px;
            position: relative;
            transition: background-color 0.15s ease-in-out;
            background-color: #fff;
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

        /* Unread Styles */
        .email-item.unread {
            font-weight: 600;
        }

        .email-item.unread::before {
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

        .email-item.unread .email-sender {
            font-weight: 500;
            color: #5a5c69;
        }

        /* Read Styles */
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

        .email-item.read .email-content {
            padding-left: 0;
        }

        /* Align read items */
        .email-item.read.active .email-content {
            padding-left: 0px;
        }

        /* Override active padding for read */

        /* Detail Pane */
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

        /* Attachments (Detail & Compose) */
        #detail-attachments {
            margin-top: 1.5rem;
            border-top: 1px solid #e3e6f0;
            padding-top: 1rem;
        }

        #detail-attachments h6 {
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            color: #5a5c69;
            font-weight: bold;
        }

        #detail-attachments ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        #detail-attachments li {
            margin-bottom: 5px;
            font-size: 0.85rem;
        }

        #detail-attachments li a {
            text-decoration: none;
            color: #4e73df;
        }

        #detail-attachments li a:hover {
            text-decoration: underline;
        }

        #detail-attachments li .fas {
            margin-right: 5px;
            color: #858796;
        }

        #detail-attachments li .attachment-size {
            font-size: 0.75rem;
            color: #858796;
            margin-left: 8px;
        }

        #compose-attachments-list {
            margin-top: 10px;
            font-size: 0.8rem;
            max-height: 80px;
            overflow-y: auto;
            background-color: #f8f9fc;
            border: 1px dashed #ddd;
            padding: 5px;
            border-radius: 4px;
        }

        #compose-attachments-list div {
            padding: 2px 5px;
            border-bottom: 1px solid #eee;
        }

        #compose-attachments-list div:last-child {
            border-bottom: none;
        }

        /* Forms & Status */
        #reply-status,
        #compose-status {
            margin-top: 10px;
            font-weight: bold;
            display: block;
            /* Ensure visibility */
            min-height: 1.2em;
            /* Prevent layout shift */
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

        /* Responsive */
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
                max-height: 45vh;
                /* Slightly more height */
            }

            #email-detail-pane {
                width: 100%;
                max-height: none;
                padding: 20px;
            }
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once __DIR__ . "/../components/sidebar.php"; // Ensure path is correct 
        ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once __DIR__ . "/../components/user_topbar.php"; // Ensure path is correct 
                ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                        <div>
                            <button type="button" class="btn btn-sm btn-success shadow-sm mr-2" data-toggle="modal" data-target="#composeEmailModal">
                                <i class="fas fa-edit fa-sm text-white-50"></i> Compose Email
                            </button>
                            <a href="read_emails.php" class="d-none d-sm-inline-block btn btn-sm btn-outline-secondary shadow-sm mr-2">
                                <i class="fas fa-envelope-open-text fa-sm text-secondary-50"></i> View Read Emails
                            </a>
                            <a href="sent_emails.php" class="d-none d-sm-inline-block btn btn-sm btn-outline-info shadow-sm">
                                <i class="fas fa-paper-plane fa-sm text-info-50"></i> View Sent Emails
                            </a>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Inbox (Unread)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="email-layout">
                                <div id="email-list-pane">
                                    <div id="list-header">
                                        <div class="form-row align-items-end">
                                            <div class="col">
                                                <label for="since-date-input">Show unread since:</label>
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
                                    <div class="p-3 d-block d-sm-none border-top">
                                        <button type="button" class="btn btn-sm btn-success btn-block mb-2" data-toggle="modal" data-target="#composeEmailModal">Compose Email</button>
                                        <a href="read_emails.php" class="btn btn-sm btn-outline-secondary btn-block">View Read Emails</a>
                                        <a href="sent_emails.php" class="btn btn-sm btn-outline-info btn-block mt-2">View Sent Emails</a>
                                    </div>
                                </div>
                                <div id="email-detail-pane">
                                    <div id="email-detail-placeholder">
                                        <i class="fas fa-envelope fa-3x text-gray-300 mb-3"></i>
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
                                        <div id="detail-attachments" style="display: none;">
                                            <h6><i class="fas fa-paperclip"></i> Attachments</h6>
                                            <ul id="attachment-list"></ul>
                                        </div>
                                        <div id="error-message-detail" class="text-danger mt-2 small" style="display: none;"></div>
                                        <form id="detail-reply-form">
                                            <hr class="mt-4">
                                            <h5>Reply</h5>
                                            <input type="hidden" id="reply-original-msgno" name="original_msgno">
                                            <input type="hidden" id="reply-original-message-id" name="original_message_id">
                                            <div class="form-group">
                                                <label for="reply-to" class="small">To:</label>
                                                <input type="text" id="reply-to" name="reply_to" class="form-control form-control-sm" readonly required>
                                            </div>
                                            <div class="form-group">
                                                <label for="reply-subject" class="small">Subject:</label>
                                                <input type="text" id="reply-subject" name="reply_subject" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="reply-body" class="small">Message:</label>
                                                <textarea id="reply-body" name="reply_body" class="form-control form-control-sm" rows="5" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
                                            <div id="reply-status" class="small mt-2 d-inline-block ml-3"></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><?php include_once __DIR__ . "/../components/footer.php"; // Ensure path is correct 
                    ?>
        </div>
    </div><a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <?php include_once __DIR__ . "/../modals/logout.php"; // Ensure path is correct 
    ?>

    <div class="modal fade" id="composeEmailModal" tabindex="-1" role="dialog" aria-labelledby="composeEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="compose-email-form" enctype="multipart/form-data">
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
                            <small class="form-text text-muted">Enter one primary recipient email address.</small>
                        </div>
                        <div class="form-group">
                            <label for="compose-cc">Cc:</label>
                            <input type="text" class="form-control" id="compose-cc" name="compose_cc" placeholder="cc1@example.com, cc2@example.com">
                            <small class="form-text text-muted">Enter comma-separated email addresses (optional).</small>
                        </div>
                        <div class="form-group">
                            <label for="compose-subject">Subject:</label>
                            <input type="text" class="form-control" id="compose-subject" name="compose_subject" required>
                        </div>
                        <div class="form-group">
                            <label for="compose-body">Message:</label>
                            <textarea class="form-control" id="compose-body" name="compose_body" rows="8" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="compose-attachments">Attachments:</label>
                            <input type="file" class="form-control-file" id="compose-attachments" name="compose_attachments[]" multiple>
                            <div id="compose-attachments-list" class="mt-2"></div>
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
        // --- State Variables ---
        let currentDisplayedEmails = {};
        let initialLoadAttempted = false;
        let activeMsgNo = null;
        let isFetching = false;
        let isSendingCompose = false;
        let isSendingReply = false;
        const mailbox = "INBOX"; // This page specifically deals with INBOX

        // --- UI Feedback Functions ---
        function displayError(message, area = 'list') {
            const errorDivId = area === 'detail' ? '#error-message-detail' : area === 'compose' ? '#compose-status' : '#error-message-list';
            const errorDiv = $(errorDivId);
            errorDiv.text(message).removeClass('text-success text-info').addClass('text-danger').show().css('display', 'block'); // Ensure visibility
        }

        function displayStatus(message, area = 'reply', type = 'info') {
            const statusDivId = area === 'compose' ? '#compose-status' : '#reply-status';
            const statusDiv = $(statusDivId);
            statusDiv.text(message)
                .removeClass('text-danger text-success text-info')
                .addClass(type === 'success' ? 'text-success' : type === 'error' ? 'text-danger' : 'text-info')
                .show().css('display', 'block'); // Ensure visibility
        }

        function clearErrors(area = 'all') {
            if (area === 'all' || area === 'list') $('#error-message-list').text('').hide();
            if (area === 'all' || area === 'detail') {
                $('#error-message-detail').text('').hide();
                $('#reply-status').text('').removeClass('text-danger text-success text-info').hide();
            }
            if (area === 'all' || area === 'compose') $('#compose-status').text('').removeClass('text-danger text-success text-info').hide();
        }

        // --- Email List Rendering ---
        function renderEmailList(emails) {
            const container = $('#email-list-container');
            container.empty();
            currentDisplayedEmails = {};
            clearErrors('list');
            if (!emails) emails = [];

            const unreadEmails = emails.filter(email => !email.seen); // Only show unread

            if (unreadEmails.length === 0) {
                const message = initialLoadAttempted ? 'No unread emails found since the selected date.' : 'Loading emails...';
                container.html(`<p class="no-emails-message">${message}</p>`);
                if (activeMsgNo === null) { // Only hide detail if nothing is selected
                    $('#email-detail-placeholder').show();
                    $('#email-detail-content').hide();
                }
            } else {
                unreadEmails.forEach(email => {
                    const msgno = email.message_no;
                    const readStatusClass = 'unread'; // All here are unread
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
                    currentDisplayedEmails[msgno] = email; // Add to cache
                });

                // Reset detail view if active email is no longer in the list (shouldn't happen here often)
                if (activeMsgNo !== null && !currentDisplayedEmails[activeMsgNo]) {
                    $('#email-detail-placeholder').show();
                    $('#email-detail-content').hide();
                    activeMsgNo = null;
                } else if (activeMsgNo === null && unreadEmails.length > 0) {
                    // If nothing was selected and we got emails, ensure placeholder is shown initially
                    $('#email-detail-placeholder').show();
                    $('#email-detail-content').hide();
                }
            }
            initialLoadAttempted = true;
        }

        // --- Email Fetching ---
        function fetchEmailList(sinceDate) {
            if (isFetching) return;
            if (!sinceDate) {
                displayError("Please select a date.", 'list');
                return;
            }
            isFetching = true;
            initialLoadAttempted = false;
            $('#email-list-container').html('<div class="loading-indicator">Loading emails...</div>');
            $('#fetch-emails-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Fetching...');
            clearErrors('list');

            // Reset detail view when fetching new list
            $('#email-detail-placeholder').show();
            $('#email-detail-content').hide();
            activeMsgNo = null;

            $.ajax({
                url: `api_get_emails.php?status=unread&since_date=${sinceDate}`, // Fetches UNREAD from INBOX (default)
                method: 'GET',
                dataType: 'json',
                timeout: 30000, // 30 seconds
                success: function(response) {
                    if (response.error) {
                        displayError('Error loading list: ' + response.error, 'list');
                        renderEmailList([]); // Show empty state
                    } else {
                        renderEmailList(response.emails || []);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("List fetch error:", status, error, xhr.responseText);
                    if (status !== 'abort') {
                        displayError('Network error loading list. Check connection or server status.', 'list');
                        renderEmailList([]); // Show empty state
                    }
                },
                complete: function() {
                    isFetching = false;
                    initialLoadAttempted = true;
                    $('#fetch-emails-btn').prop('disabled', false).text('Fetch');
                    // Final check if container is empty after loading attempt
                    if ($('#email-list-container').children('.email-item').length === 0 && !$('#email-list-container').find('.no-emails-message').length) {
                        $('#email-list-container').html('<p class="no-emails-message">No unread emails found or failed to load.</p>');
                    }
                }
            });
        }

        // --- View Email Details ---
        function viewEmail(msgno) {
            if (!msgno || isFetching) return; // Don't fetch if list is loading
            clearErrors('detail');
            $('#detail-reply-form button[type="submit"]').prop('disabled', false); // Ensure reply button is enabled

            // Update UI immediately for responsiveness
            $('#email-detail-placeholder').hide();
            $('#email-detail-content').show();
            $('#detail-body').html('<div class="loading-indicator">Loading email...</div>');
            $('#detail-subject, #detail-from, #detail-date').text('');
            $('#reply-original-msgno, #reply-original-message-id, #reply-to, #reply-subject, #reply-body').val('');
            $('#detail-attachments').hide(); // Hide attachments section
            $('#attachment-list').empty(); // Clear previous attachments

            // Visual selection update (mark previous as read - visually only)
            if (activeMsgNo !== null && activeMsgNo !== msgno) {
                const previousItem = $(`#email-list-container .email-item[data-msgno="${activeMsgNo}"]`);
                if (previousItem.length > 0 && previousItem.hasClass('unread')) {
                    previousItem.removeClass('unread').addClass('read'); // Visual change
                }
                previousItem.removeClass('active'); // Remove active state from previous
            }
            $('#email-list-container .email-item').removeClass('active'); // Ensure only one is active
            const selectedItem = $(`#email-list-container .email-item[data-msgno="${msgno}"]`);
            selectedItem.addClass('active');
            activeMsgNo = msgno; // Update active message ID

            $.ajax({
                url: `api_get_email_details.php?msgno=${msgno}`, // Fetches from INBOX (default), API marks as read
                method: 'GET',
                dataType: 'json',
                timeout: 30000, // 30 seconds
                success: function(response) {
                    if (msgno !== activeMsgNo) return; // Stale request, user clicked another email

                    if (response.error) {
                        displayError('Error fetching email details: ' + response.error, 'detail');
                        $('#detail-body').html('<p class="text-danger">Could not load email content.</p>');
                    } else if (response.details) {
                        displayEmailDetails(response.details);
                        // API handled marking as read, now confirm visual update
                        if (selectedItem.hasClass('unread')) {
                            selectedItem.removeClass('unread').addClass('read');
                        }
                    } else {
                        displayError('Email details not found or incomplete.', 'detail');
                        $('#detail-body').html('<p class="text-warning">Could not display email content.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    if (msgno !== activeMsgNo) return; // Stale request
                    console.error("Detail fetch error:", status, error, xhr.responseText);
                    displayError('Network error fetching email details.', 'detail');
                    $('#detail-body').html('<p class="text-danger">Could not load email content due to network error.</p>');
                }
            });
        }

        // --- Display Email Details ---
        function displayEmailDetails(details) {
            $('#detail-subject').text(details.subject || '(No Subject)');
            $('#detail-from').text(details.from || 'N/A');
            $('#detail-date').text(details.date || 'N/A');
            $('#reply-original-msgno').val(details.message_no || '');
            $('#reply-original-message-id').val(details.message_id || '');

            // --- Prepare Reply Form ---
            let replyToAddress = details.from || '';
            const emailMatch = replyToAddress.match(/<([^>]+)>/); // Extract email from "Name <email@addr.com>"
            if (emailMatch && emailMatch[1]) replyToAddress = emailMatch[1];
            $('#reply-to').val(replyToAddress);
            $('#reply-subject').val('Re: ' + (details.subject || ''));
            // Add quote to reply body (plain text)
            const originalMessageQuote = `\n\n----- Original Message -----\nFrom: ${details.from || 'N/A'}\nDate: ${details.date || 'N/A'}\nSubject: ${details.subject || '(No Subject)'}\n\n${details.body_plain || '(Original content not available in plain text)'}`;
            $('#reply-body').val(originalMessageQuote); // Pre-fill reply body

            // --- Display Body ---
            let bodyContent = '<p><em>(Email content not available.)</em></p>';
            if (details.body_plain) {
                bodyContent = `<div class="email-detail-body-plain">${escapeHtml(details.body_plain)}</div>`;
            } else if (details.body_html) {
                // Basic text extraction as a safer default:
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = details.body_html; // Use cautiously if HTML isn't sanitized server-side
                const extractedText = tempDiv.textContent || tempDiv.innerText || "";
                bodyContent = `<div class="email-detail-body-plain">${escapeHtml(extractedText)}</div><p class="text-muted small mt-2"><em>(Displayed text extracted from HTML. Formatting may be lost.)</em></p>`;
                // Alternative: Iframe for safer HTML display (requires CSS for iframe styling)
                // bodyContent = `<div class="email-detail-body-html"><iframe srcdoc="${escapeHtmlAttribute(details.body_html)}" sandbox="allow-same-origin"></iframe></div>`;
            }
            $('#detail-body').html(bodyContent);

            // --- Display Attachments ---
            const attachmentsContainer = $('#detail-attachments');
            const attachmentList = $('#attachment-list');
            attachmentList.empty(); // Clear previous list

            if (details.attachments && details.attachments.length > 0) {
                details.attachments.forEach(att => {
                    // Show only actual attachments, not inline images (cid check)
                    if (att.disposition === 'attachment' || !att.cid) {
                        // Use INBOX mailbox explicitly for download link from this page
                        const downloadUrl = `api_download_attachment.php?msgno=${details.message_no}&part_path=${att.part_path}&filename=${encodeURIComponent(att.filename)}&mailbox=INBOX`;
                        const fileSize = att.size > 0 ? formatBytes(att.size) : '';
                        const listItem = `
                             <li>
                                 <i class="fas fa-paperclip"></i>
                                 <a href="${downloadUrl}" target="_blank" title="Download ${escapeHtmlAttribute(att.filename)}">${escapeHtml(att.filename)}</a>
                                 ${fileSize ? `<span class="attachment-size">(${fileSize})</span>` : ''}
                             </li>`;
                        attachmentList.append(listItem);
                    }
                });
                attachmentsContainer.show(); // Show the attachment section if files exist
            } else {
                attachmentsContainer.hide(); // Hide if no attachments
            }

            // --- Reset Reply Form State ---
            $('#detail-reply-form button[type="submit"]').prop('disabled', false);
            $('#reply-status').text('').hide(); // Clear previous reply status
        }

        // --- Utility Functions ---
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

        function escapeHtmlAttribute(unsafe) {
            if (unsafe === null || typeof unsafe === 'undefined') return '';
            try {
                return unsafe.toString()
                    .replace(/&/g, "&amp;")
                    .replace(/"/g, "&quot;") // Crucial for attributes
                    .replace(/'/g, "&#039;") // Crucial for attributes
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;");
            } catch (e) {
                console.error("Error escaping HTML Attribute:", e, unsafe);
                return '';
            }
        }

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        // --- Form Submission Handlers ---
        function handleReplySubmit(event) {
            event.preventDefault();
            if (isSendingReply) return; // Prevent double submit

            clearErrors('detail');
            const form = $(event.target);
            const submitButton = form.find('button[type="submit"]');
            const statusDiv = $('#reply-status');
            const formData = {
                reply_to: $('#reply-to').val(),
                reply_subject: $('#reply-subject').val(),
                reply_body: $('#reply-body').val(),
                original_message_id: $('#reply-original-message-id').val() // Used by API to find original email
            };

            if (!formData.reply_to || !formData.reply_subject || !formData.reply_body) {
                displayStatus('Please fill in all reply fields.', 'reply', 'error');
                return;
            }

            isSendingReply = true;
            submitButton.prop('disabled', true);
            displayStatus('Sending reply...', 'reply', 'info');

            $.ajax({
                url: 'api_send_reply.php', // Assuming this endpoint exists
                method: 'POST',
                data: formData,
                dataType: 'json',
                timeout: 30000,
                success: function(response) {
                    if (response.success) {
                        displayStatus('Reply sent successfully!', 'reply', 'success');
                        // Optionally clear just the body after sending
                        // $('#reply-body').val('');
                        setTimeout(() => {
                            displayStatus('', 'reply'); // Hide status after delay
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
                    submitButton.prop('disabled', false); // Re-enable button
                }
            });
        }

        function handleComposeSubmit(event) {
            event.preventDefault();
            if (isSendingCompose) return;

            clearErrors('compose');
            const form = $(event.target);
            const submitButton = form.find('button[type="submit"]');
            const statusDiv = $('#compose-status');
            const toField = $('#compose-to');
            const ccField = $('#compose-cc');
            const subjectField = $('#compose-subject');
            const bodyField = $('#compose-body');

            // Validation
            if (!toField.val() || !subjectField.val() || !bodyField.val()) {
                displayStatus('Please fill in To, Subject, and Message fields.', 'compose', 'error');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(toField.val())) {
                displayStatus('Please enter a valid email address in the To field.', 'compose', 'error');
                return;
            }
            if (ccField.val()) { // Validate CC only if not empty
                const ccEmails = ccField.val().split(',');
                for (let email of ccEmails) {
                    if (email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
                        displayStatus('Please enter valid, comma-separated email addresses in the Cc field.', 'compose', 'error');
                        return; // Stop if any CC is invalid
                    }
                }
            }

            isSendingCompose = true;
            submitButton.prop('disabled', true);
            displayStatus('Sending email...', 'compose', 'info');

            // Use FormData to include files and all named fields (to, cc, subject, body, attachments)
            const formData = new FormData(form[0]);

            $.ajax({
                url: 'api_send_compose.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                processData: false, // Important for FormData
                contentType: false, // Important for FormData
                timeout: 60000, // 60 seconds for potential uploads
                success: function(response) {
                    if (response.success) {
                        displayStatus('Email sent successfully!', 'compose', 'success');
                        form[0].reset(); // Clear the form fields
                        $('#compose-attachments-list').empty(); // Clear displayed file list
                        setTimeout(() => {
                            $('#composeEmailModal').modal('hide');
                            displayStatus('', 'compose'); // Clear status message
                        }, 2000);
                    } else {
                        displayStatus('Error sending email: ' + (response.error || 'Unknown error'), 'compose', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Compose send error:", status, error, xhr.responseText);
                    let errorMsg = 'Network error sending email. Check connection/configuration.';
                    if (xhr.status === 413) { // Payload Too Large
                        errorMsg = 'Error: Files are too large to upload. Please reduce file size or quantity.';
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = 'Error: ' + xhr.responseJSON.error; // Show specific server error if available
                    }
                    displayStatus(errorMsg, 'compose', 'error');
                },
                complete: function() {
                    isSendingCompose = false;
                    submitButton.prop('disabled', false); // Re-enable button
                }
            });
        }

        // --- Document Ready ---
        $(document).ready(function() {
            // Initial Fetch
            const initialDate = $('#since-date-input').val();
            if (initialDate) {
                fetchEmailList(initialDate);
            } else {
                // Handle case where default date might be invalid or missing
                $('#email-list-container').html('<p class="no-emails-message">Please select a date and click Fetch.</p>');
                initialLoadAttempted = true;
            }

            // Event Listeners
            $('#fetch-emails-btn').on('click', function() {
                const selectedDate = $('#since-date-input').val();
                fetchEmailList(selectedDate);
            });

            $('#detail-reply-form').on('submit', handleReplySubmit);
            $('#compose-email-form').on('submit', handleComposeSubmit);

            // Update file list display on change for compose modal
            $('#compose-attachments').on('change', function() {
                const fileListDiv = $('#compose-attachments-list');
                fileListDiv.empty(); // Clear previous list
                if (this.files && this.files.length > 0) {
                    for (let i = 0; i < this.files.length; i++) {
                        const file = this.files[i];
                        fileListDiv.append(`<div><i class="fas fa-paperclip fa-sm mr-1 text-gray-500"></i>${escapeHtml(file.name)} <span class="text-gray-600">(${formatBytes(file.size)})</span></div>`);
                    }
                }
            });

            // Reset compose form when modal is closed
            $('#composeEmailModal').on('hidden.bs.modal', function() {
                $('#compose-email-form')[0].reset();
                $('#compose-attachments-list').empty(); // Clear file list display
                clearErrors('compose'); // Clear any status messages
            });
        });
    </script>
</body>

</html>