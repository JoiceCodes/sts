<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_full_name"])) {
    header("Location: ../../auth/login.php");
    exit();
}
$pageTitle = "Read Email History";
$default_since_date = ''; // Keep empty initially
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once __DIR__ . "/../components/head.php"; ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> - i-Secure</title>
    <style>
        /* --- CSS Styles (Same as sent_emails.php, but keep Reply form visible) --- */
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
            padding-bottom: 40px;
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

        #load-more-indicator {
            text-align: center;
            padding: 15px;
            color: #858796;
            display: none;
        }

        .email-item {
            display: flex;
            align-items: center;
            cursor: pointer;
            border-bottom: 1px solid #e3e6f0;
            padding: 12px 15px;
            position: relative;
            transition: background-color 0.15s ease-in-out;
            font-weight: normal;
            color: #858796;
            /* Read emails are greyed out */
            background-color: #f8f9fc;
            /* Slightly different bg for read */
        }

        .email-item::before {
            display: none;
        }

        /* No blue dot */
        .email-item .email-content {
            flex-grow: 1;
            overflow: hidden;
            padding-left: 0;
        }

        .email-item .email-sender {
            display: block;
            font-size: 0.8rem;
            color: #858796;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Show sender */
        .email-item .email-subject {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
            font-size: 0.9rem;
            color: #858796;
        }

        .email-item .email-date {
            font-size: 0.75rem;
            color: #a5a7b1;
        }

        .email-item:hover {
            background-color: #e9ecef;
        }

        .email-item.active {
            background-color: #cfe2ff !important;
            border-left: 4px solid #4e73df;
            padding-left: 11px;
            color: #495057;
            font-weight: normal;
            background-color: #cfe2ff;
            /* Ensure active bg overrides read bg */
        }

        .email-item.active .email-content {
            padding-left: 0px;
        }

        .email-item.active .email-subject,
        .email-item.active .email-sender {
            color: #495057;
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

        #detail-reply-form {
            margin-top: 2rem;
            border-top: 1px solid #e3e6f0;
            padding-top: 1.5rem;
        }

        /* Keep reply form */
        #reply-status {
            margin-top: 10px;
            font-weight: bold;
        }

        #detail-reply-form .btn-primary[disabled] {
            cursor: not-allowed;
            opacity: 0.65;
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
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once __DIR__ . "/../components/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/administrator_topbar.php" ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                        <div>
                            <a href="all_notifications.php" class="d-none d-sm-inline-block btn btn-sm btn-outline-primary shadow-sm mr-2">
                                <i class="fas fa-envelope fa-sm text-primary-50"></i> View Unread Inbox
                            </a>
                            <a href="sent_emails.php" class="d-none d-sm-inline-block btn btn-sm btn-outline-info shadow-sm">
                                <i class="fas fa-paper-plane fa-sm text-info-50"></i> View Sent Emails
                            </a>
                        </div>
                    </div>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Read Email History (Inbox)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="email-layout">
                                <div id="email-list-pane">
                                    <div id="list-header">
                                        <div class="form-row align-items-end">
                                            <div class="col">
                                                <label for="since-date-input">Show read emails since:</label>
                                                <input type="date" id="since-date-input" class="form-control form-control-sm" value="<?= htmlspecialchars($default_since_date) ?>">
                                            </div>
                                            <div class="col-auto">
                                                <button id="fetch-emails-btn" class="btn btn-sm btn-primary">Fetch</button>
                                            </div>
                                        </div>
                                        <div id="error-message-list" class="text-danger pt-2 small" style="display: none;"></div>
                                    </div>
                                    <div id="email-list-container">
                                        <div class="loading-indicator">Select a date and click Fetch to view read emails.</div>
                                    </div>
                                    <div id="load-more-indicator">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"><span class="sr-only">Loading...</span></div> Loading more...
                                    </div>
                                    <div class="p-3 d-block d-sm-none">
                                        <a href="all_notifications.php" class="btn btn-sm btn-outline-primary btn-block">View Unread Inbox</a>
                                        <a href="sent_emails.php" class="btn btn-sm btn-outline-info btn-block mt-2">View Sent Emails</a>
                                    </div>
                                </div>
                                <div id="email-detail-pane">
                                    <div id="email-detail-placeholder">
                                        <i class="fas fa-history fa-3x text-gray-300 mb-3"></i>
                                        <p>Select a read email from the list.</p>
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
                                            <div class="form-group"><label for="reply-to" class="small">To:</label><input type="text" id="reply-to" name="reply_to" class="form-control form-control-sm" readonly required></div>
                                            <div class="form-group"><label for="reply-subject" class="small">Subject:</label><input type="text" id="reply-subject" name="reply_subject" class="form-control form-control-sm" required></div>
                                            <div class="form-group"><label for="reply-body" class="small">Message:</label><textarea id="reply-body" name="reply_body" class="form-control form-control-sm" rows="5" required></textarea></div>
                                            <button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
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
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script>
        const BATCH_LIMIT = 50;
        let currentOffset = 0;
        let isLoadingMore = false;
        let noMoreEmails = false;
        let currentDisplayedEmails = {};
        let initialLoadAttempted = false;
        let activeMsgNo = null;
        let currentSinceDate = '';
        let isSendingReply = false; // Flag for reply sending
        const mailbox = "INBOX"; // Explicitly INBOX

        function displayError(message, area = 'list') {
            /* ... (same as before) ... */
            const errorDivId = area === 'detail' ? '#error-message-detail' : '#error-message-list';
            $(errorDivId).text(message).show();
        }

        function displayStatus(message, area = 'reply', type = 'info') {
            /* For Reply Form */
            const statusDivId = '#reply-status';
            const statusDiv = $(statusDivId);
            statusDiv.text(message)
                .removeClass('text-danger text-success text-info')
                .addClass(type === 'success' ? 'text-success' : type === 'error' ? 'text-danger' : 'text-info')
                .show();
        }

        function clearErrors(area = 'all') {
            /* ... (same as before) ... */
            if (area === 'all' || area === 'list') $('#error-message-list').text('').hide();
            if (area === 'all' || area === 'detail') {
                $('#error-message-detail').text('').hide();
                $('#reply-status').text('').removeClass('text-danger text-success text-info').hide();
            }
        }


        function renderInitialEmailList(emails) {
            /* ... (same as sent_emails.php, adjust messages) ... */
            const container = $('#email-list-container');
            container.empty();
            currentDisplayedEmails = {};
            if (!emails || emails.length === 0) {
                if (initialLoadAttempted) {
                    container.html('<p class="no-emails-message">No read emails found for the selected date.</p>');
                } else {
                    container.html('<div class="loading-indicator">Select a date and click Fetch to view read emails.</div>');
                }
                $('#email-detail-placeholder').show();
                $('#email-detail-content').hide();
                activeMsgNo = null;
            } else {
                appendEmailsToList(emails);
                $('#email-detail-placeholder').show();
                $('#email-detail-content').hide();
                activeMsgNo = null;
            }
        }

        function appendEmailsToList(emails) {
            /* ... (same as sent_emails.php, but use sender and 'read' class) ... */
            const container = $('#email-list-container');
            container.find('.no-emails-message, .loading-indicator').remove();
            if (!emails || emails.length === 0) return;
            emails.forEach(email => {
                if (!currentDisplayedEmails[email.message_no]) {
                    const msgno = email.message_no;
                    const readStatusClass = 'read'; // All emails here are read
                    const isActive = msgno === activeMsgNo ? ' active' : '';
                    const senderInfo = email.from ? `<div class="email-sender small">${escapeHtml(email.from)}</div>` : '';
                    const newItemHtml = `<div class="email-item ${readStatusClass}${isActive}" data-msgno="${msgno}" onclick="viewEmail(${msgno})"><div class="email-content">${senderInfo}<span class="email-subject">${escapeHtml(email.subject)}</span><div class="email-date">${escapeHtml(new Date(email.sent_at).toLocaleString())}</div></div></div>`;
                    container.append(newItemHtml);
                    currentDisplayedEmails[msgno] = email;
                }
            });
        }

        function fetchEmailList(offset = 0, isLoadMore = false, sinceDate = null) {
            /* ... (same as sent_emails.php, but use status=read) ... */
            if (isLoadingMore && isLoadMore) return;
            const dateToFetch = sinceDate || currentSinceDate;
            if (!dateToFetch) {
                displayError("Please select a date.", 'list');
                if (offset === 0) {
                    $('#email-list-container').html('<div class="loading-indicator">Select a date and click Fetch to view read emails.</div>');
                    initialLoadAttempted = false;
                }
                return;
            }
            if (offset === 0) {
                noMoreEmails = false;
                currentOffset = 0;
                initialLoadAttempted = false;
                $('#email-list-container').html('<div class="loading-indicator">Loading emails...</div>');
                $('#email-detail-placeholder').show();
                $('#email-detail-content').hide();
                activeMsgNo = null;
                currentDisplayedEmails = {};
                currentSinceDate = dateToFetch;
            }
            isLoadingMore = true;
            if (isLoadMore) $('#load-more-indicator').show();
            clearErrors('list');
            $('#fetch-emails-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Fetching...');
            // Use status=read and implicit INBOX
            const apiUrl = `api_get_emails.php?status=read&offset=${offset}&limit=${BATCH_LIMIT}&since_date=${dateToFetch}`;
            $.ajax({
                url: apiUrl,
                method: 'GET',
                dataType: 'json',
                timeout: 30000,
                success: function(response) {
                    if (response.error) {
                        displayError('Error loading emails: ' + response.error);
                        if (offset === 0) renderInitialEmailList([]);
                    } else {
                        if (offset === 0) {
                            renderInitialEmailList(response.emails || []);
                        } else {
                            appendEmailsToList(response.emails || []);
                        }
                        currentOffset += (response.emails ? response.emails.length : 0);
                        noMoreEmails = !response.more_available;
                        if (noMoreEmails) $('#load-more-indicator').hide();
                    }
                },
                error: function(xhr, status, error) {
                    if (status !== 'abort') {
                        displayError('Network error loading emails.');
                        console.error("List fetch error:", status, error, xhr.responseText);
                        if (offset === 0) renderInitialEmailList([]);
                    }
                },
                complete: function() {
                    if (offset === 0) {
                        initialLoadAttempted = true;
                        if ($('#email-list-container').find('.loading-indicator').length > 0) {
                            renderInitialEmailList([]);
                        }
                    }
                    isLoadingMore = false;
                    if (isLoadMore) $('#load-more-indicator').hide();
                    $('#fetch-emails-btn').prop('disabled', false).text('Fetch');
                }
            });
        }


        function viewEmail(msgno) {
            /* ... (same as sent_emails.php, but adjust API call) ... */
            if (!msgno) return;
            clearErrors('detail');
            $('#detail-reply-form button[type="submit"]').prop('disabled', false); // Enable reply button initially
            $('#email-detail-placeholder').hide();
            $('#email-detail-content').show();
            $('#detail-body').html('<div class="loading-indicator">Loading email...</div>');
            $('#detail-subject, #detail-from, #detail-date').text(''); // Use 'from' here
            $('#reply-original-msgno, #reply-original-message-id, #reply-to, #reply-subject, #reply-body').val(''); // Clear reply form
            $('#detail-attachments').hide();
            $('#attachment-list').empty();
            $('#email-list-container .email-item').removeClass('active');
            const selectedItem = $(`#email-list-container .email-item[data-msgno="${msgno}"]`);
            selectedItem.addClass('active');
            activeMsgNo = msgno;
            // --- UPDATED: API call includes view=read implicitly via API logic ---
            $.ajax({
                url: `api_get_email_details.php?msgno=${msgno}&view=read`, // Pass view=read
                method: 'GET',
                dataType: 'json',
                timeout: 30000,
                success: function(response) {
                    if (msgno !== activeMsgNo) return;
                    if (response.error) {
                        displayError('Error fetching email details: ' + response.error, 'detail');
                        $('#detail-body').html('<p class="text-danger">Could not load email content.</p>');
                    } else if (response.details) {
                        displayEmailDetails(response.details);
                    } else {
                        displayError('Email details not found or incomplete.', 'detail');
                        $('#detail-body').html('<p class="text-warning">Could not display email content.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    if (msgno !== activeMsgNo) return;
                    displayError('Network error fetching email details.', 'detail');
                    $('#detail-body').html('<p class="text-danger">Could not load email content due to network error.</p>');
                    console.error("Detail fetch error:", status, error, xhr.responseText);
                }
            });
        }


        function displayEmailDetails(details) {
            /* ... (same as all_notifications.php, ensure reply form is populated) ... */
            $('#detail-subject').text(details.subject || '(No Subject)');
            $('#detail-from').text(details.from || 'N/A'); // Show From
            $('#detail-date').text(details.date || 'N/A');
            $('#reply-original-msgno').val(details.message_no || '');
            $('#reply-original-message-id').val(details.message_id || '');

            // Prepare Reply Form
            let replyToAddress = details.from || '';
            const emailMatch = replyToAddress.match(/<([^>]+)>/);
            if (emailMatch && emailMatch[1]) replyToAddress = emailMatch[1];
            $('#reply-to').val(replyToAddress);
            $('#reply-subject').val('Re: ' + (details.subject || ''));
            const originalMessageQuote = `\n\n----- Original Message -----\nFrom: ${details.from || 'N/A'}\nDate: ${details.date || 'N/A'}\nSubject: ${details.subject || '(No Subject)'}\n\n${details.body_plain || '(Original content not available in plain text)'}`;
            $('#reply-body').val(originalMessageQuote); // Set reply body quote

            // Display Body
            let bodyContent = '<p><em>(Email content not available.)</em></p>';
            if (details.body_plain) {
                bodyContent = `<div class="email-detail-body-plain">${escapeHtml(details.body_plain)}</div>`;
            } else if (details.body_html) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = details.body_html;
                const extractedText = tempDiv.textContent || tempDiv.innerText || "";
                bodyContent = `<div class="email-detail-body-plain">${escapeHtml(extractedText)}</div><p class="text-muted small mt-2"><em>(Displayed text extracted from HTML. Formatting may be lost.)</em></p>`;
            }
            $('#detail-body').html(bodyContent);

            // Display Attachments
            const attachmentsContainer = $('#detail-attachments');
            const attachmentList = $('#attachment-list');
            attachmentList.empty();
            if (details.attachments && details.attachments.length > 0) {
                details.attachments.forEach(att => {
                    if (att.disposition === 'attachment' || !att.cid) {
                        // Use INBOX mailbox for download link
                        const downloadUrl = `api_download_attachment.php?msgno=${details.message_no}&part_path=${att.part_path}&filename=${encodeURIComponent(att.filename)}&mailbox=INBOX`;
                        const fileSize = att.size > 0 ? formatBytes(att.size) : '';
                        const listItem = `<li><i class="fas fa-paperclip"></i> <a href="${downloadUrl}" target="_blank">${escapeHtml(att.filename)}</a> ${fileSize ? `<span class="attachment-size">(${fileSize})</span>` : ''}</li>`;
                        attachmentList.append(listItem);
                    }
                });
                attachmentsContainer.show();
            } else {
                attachmentsContainer.hide();
            }

            // Reset reply form state
            $('#detail-reply-form button[type="submit"]').prop('disabled', false);
            $('#reply-status').text('').hide();
        }

        function handleReplySubmit(event) {
            /* ... (same as all_notifications.php) ... */
            event.preventDefault();
            if (isSendingReply) return;
            clearErrors('detail');
            const form = $(event.target);
            const submitButton = form.find('button[type="submit"]');
            const statusDiv = $('#reply-status');
            const formData = {
                reply_to: $('#reply-to').val(),
                reply_subject: $('#reply-subject').val(),
                reply_body: $('#reply-body').val(),
                original_message_id: $('#reply-original-message-id').val()
            };
            if (!formData.reply_to || !formData.reply_subject || !formData.reply_body) {
                displayStatus('Please fill in all reply fields.', 'reply', 'error');
                return;
            }
            isSendingReply = true;
            submitButton.prop('disabled', true);
            displayStatus('Sending reply...', 'reply', 'info');
            $.ajax({
                url: 'api_send_reply.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                timeout: 30000,
                success: function(response) {
                    if (response.success) {
                        displayStatus('Reply sent successfully!', 'reply', 'success');
                        setTimeout(() => {
                            $('#reply-body').val('');
                            statusDiv.text('').hide();
                        }, 2500);
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
                    submitButton.prop('disabled', false);
                }
            });
        }

        function escapeHtml(unsafe) {
            /* ... (same as before) ... */
            if (unsafe === null || typeof unsafe === 'undefined') return '';
            try {
                return unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            } catch (e) {
                console.error("Error escaping HTML:", e, unsafe);
                return '';
            }
        }

        function escapeHtmlAttribute(unsafe) {
            /* ... (same as before) ... */
            if (unsafe === null || typeof unsafe === 'undefined') return '';
            try {
                return unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            } catch (e) {
                console.error("Error escaping HTML Attribute:", e, unsafe);
                return '';
            }
        }

        function formatBytes(bytes, decimals = 2) {
            /* ... (same as before) ... */
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        $(document).ready(function() {
            /* ... (same as sent_emails.php, just handles reply submit) ... */
            $('#fetch-emails-btn').on('click', function() {
                const selectedDate = $('#since-date-input').val();
                fetchEmailList(0, false, selectedDate);
            });
            $('#detail-reply-form').on('submit', handleReplySubmit); // Add reply handler
            const listContainer = $('#email-list-container');
            listContainer.on('scroll', function() {
                if (isLoadingMore || noMoreEmails || !currentSinceDate) return;
                if (listContainer[0].scrollHeight <= listContainer.innerHeight()) return;
                const threshold = 150;
                const isNearBottom = listContainer[0].scrollHeight - listContainer.scrollTop() - listContainer.innerHeight() < threshold;
                if (isNearBottom) {
                    fetchEmailList(currentOffset, true, currentSinceDate);
                }
            });
            if (!$('#since-date-input').val()) {
                $('#email-list-container').html('<div class="loading-indicator">Select a date and click Fetch to view read emails.</div>');
            }
        });
    </script>
</body>

</html>