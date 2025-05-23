<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_full_name"])) {
    header("Location: ../../auth/login.php"); // Redirect if not logged in
    exit();
}

// --- CHANGED: Page Title ---
$pageTitle = "Sent Email History";
$default_since_date = ''; // Keep empty initially

// --- NEW: Define Sent Mailbox Name ---
// Standard Gmail name. Adjust if your target server uses something different (e.g., 'Sent', 'Sent Items')
$sent_mailbox_name = '[Gmail]/Sent Mail';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once __DIR__ . "/../components/head.php"; ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> - i-Secure</title>
    <style>
        /* --- CSS Styles (Mostly the same as read_emails.php) --- */
        /* Add specific styles if needed for Sent view */
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
            /* Sent items aren't bold */
            color: #5a5c69;
            /* Use slightly darker text than read */
            background-color: #fff;
        }

        .email-item::before {
            content: '';
            display: none;
        }

        .email-item .email-content {
            flex-grow: 1;
            overflow: hidden;
            padding-left: 0;
        }

        /* --- CHANGED: Display 'To' address in list item if available --- */
        .email-item .email-recipient {
            display: block;
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .email-item .email-subject {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
            font-size: 0.9rem;
            color: #5a5c69;
        }

        /* Keep subject noticeable */
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
        }

        .email-item.active .email-content {
            padding-left: 0px;
        }

        .email-item.active .email-subject,
        .email-item.active .email-recipient {
            color: #495057;
        }

        /* Adjust active recipient color */
        #detail-subject {
            margin-bottom: 0.75rem;
            font-size: 1.3rem;
            color: #3a3b45;
        }

        /* --- CHANGED: Labels for To/Date --- */
        #detail-to-label,
        #detail-date-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: bold;
        }

        #detail-to,
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

        /* --- NEW: Attachment List Styles --- */
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

        /* Hide Reply Form in Sent View */
        #detail-reply-form {
            display: none !important;
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
                <?php include_once "../components/engineer_topbar.php" ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                        <div>
                            <a href="all_notifications.php" class="d-none d-sm-inline-block btn btn-sm btn-outline-primary shadow-sm mr-2">
                                <i class="fas fa-envelope fa-sm text-primary-50"></i> View Unread Inbox
                            </a>
                            <a href="read_emails.php" class="d-none d-sm-inline-block btn btn-sm btn-outline-secondary shadow-sm">
                                <i class="fas fa-envelope-open-text fa-sm text-secondary-50"></i> View Read Inbox
                            </a>
                        </div>
                    </div>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Sent Emails</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="email-layout">
                                <div id="email-list-pane">
                                    <div id="list-header">
                                        <div class="form-row align-items-end">
                                            <div class="col">
                                                <label for="since-date-input">Show sent emails since:</label>
                                                <input type="date" id="since-date-input" class="form-control form-control-sm" value="<?= htmlspecialchars($default_since_date) ?>">
                                            </div>
                                            <div class="col-auto">
                                                <button id="fetch-emails-btn" class="btn btn-sm btn-primary">Fetch</button>
                                            </div>
                                        </div>
                                        <div id="error-message-list" class="text-danger pt-2 small" style="display: none;"></div>
                                    </div>
                                    <div id="email-list-container">
                                        <div class="loading-indicator">Select a date and click Fetch to view sent emails.</div>
                                    </div>
                                    <div id="load-more-indicator">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"><span class="sr-only">Loading...</span></div> Loading more...
                                    </div>
                                    <div class="p-3 d-block d-sm-none">
                                        <a href="all_notifications.php" class="btn btn-sm btn-outline-primary btn-block">View Unread Inbox</a>
                                        <a href="read_emails.php" class="btn btn-sm btn-outline-secondary btn-block mt-2">View Read Inbox</a>
                                    </div>
                                </div>
                                <div id="email-detail-pane">
                                    <div id="email-detail-placeholder">
                                        <i class="fas fa-paper-plane fa-3x text-gray-300 mb-3"></i>
                                        <p>Select a sent email from the list.</p>
                                    </div>
                                    <div id="email-detail-content" style="display: none;">
                                        <h4 id="detail-subject"></h4>
                                        <hr class="mt-2 mb-3">
                                        <div class="mb-3">
                                            <p class="mb-1"><span id="detail-to-label">To:</span> <span id="detail-to"></span></p>
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
                                            <input type="hidden" id="reply-original-msgno" name="original_msgno"> <input type="hidden" id="reply-original-message-id" name="original_message_id">
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
        // --- NEW: Mailbox variable ---
        const mailbox = "<?= htmlspecialchars(rawurlencode($sent_mailbox_name)) ?>"; // URL encode for API call

        function displayError(message, area = 'list') {
            const errorDivId = area === 'detail' ? '#error-message-detail' : '#error-message-list';
            $(errorDivId).text(message).show();
        }

        function clearErrors(area = 'all') {
            if (area === 'all' || area === 'list') $('#error-message-list').text('').hide();
            if (area === 'all' || area === 'detail') $('#error-message-detail').text('').hide();
        }

        function renderInitialEmailList(emails) {
            const container = $('#email-list-container');
            container.empty();
            currentDisplayedEmails = {};
            if (!emails || emails.length === 0) {
                if (initialLoadAttempted) {
                    container.html('<p class="no-emails-message">No sent emails found for the selected date.</p>');
                } else {
                    container.html('<div class="loading-indicator">Select a date and click Fetch to view sent emails.</div>');
                }
                $('#email-detail-placeholder').show();
                $('#email-detail-content').hide();
                activeMsgNo = null;
            } else {
                appendEmailsToList(emails);
                // Reset placeholder if needed after initial load with results
                $('#email-detail-placeholder').show();
                $('#email-detail-content').hide();
                activeMsgNo = null;
            }
        }

        function appendEmailsToList(emails) {
            const container = $('#email-list-container');
            container.find('.no-emails-message, .loading-indicator').remove();
            if (!emails || emails.length === 0) return;

            emails.forEach(email => {
                if (!currentDisplayedEmails[email.message_no]) {
                    const msgno = email.message_no;
                    // Sent emails are always considered 'read' visually
                    const readStatusClass = 'read';
                    const isActive = msgno === activeMsgNo ? ' active' : '';
                    // --- CHANGED: Show recipient (To) if available ---
                    const recipientInfo = email.to ? `<div class="email-recipient small">${escapeHtml(email.to)}</div>` : '';
                    const newItemHtml = `
                        <div class="email-item ${readStatusClass}${isActive}" data-msgno="${msgno}" onclick="viewEmail(${msgno})">
                           <div class="email-content">
                                ${recipientInfo}
                                <span class="email-subject">${escapeHtml(email.subject)}</span>
                                <div class="email-date">${escapeHtml(new Date(email.sent_at).toLocaleString())}</div>
                            </div>
                        </div>`;
                    container.append(newItemHtml);
                    currentDisplayedEmails[msgno] = email;
                }
            });
        }

        function fetchEmailList(offset = 0, isLoadMore = false, sinceDate = null) {
            if (isLoadingMore && isLoadMore) return;

            const dateToFetch = sinceDate || currentSinceDate;

            if (!dateToFetch) {
                displayError("Please select a date.", 'list');
                if (offset === 0) {
                    $('#email-list-container').html('<div class="loading-indicator">Select a date and click Fetch to view sent emails.</div>');
                    initialLoadAttempted = false;
                }
                return;
            }

            if (offset === 0) { // Resetting for a new fetch
                noMoreEmails = false;
                currentOffset = 0;
                initialLoadAttempted = false;
                $('#email-list-container').html('<div class="loading-indicator">Loading emails...</div>');
                $('#email-detail-placeholder').show();
                $('#email-detail-content').hide();
                activeMsgNo = null;
                currentDisplayedEmails = {};
                currentSinceDate = dateToFetch; // Store the date used for this fetch
            }

            isLoadingMore = true;
            if (isLoadMore) $('#load-more-indicator').show();
            clearErrors('list');
            $('#fetch-emails-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Fetching...');

            // --- UPDATED: Add mailbox parameter to API call. Use 'ALL' status for Sent? Or keep 'read'? ---
            // Let's try using 'read' status first, as Gmail flags might work. If not, change to 'all' or remove status.
            // Also removed limit= parameter as Sent folder might not need strict batching like unread. Adjust if needed.
            const apiUrl = `api_get_emails.php?status=read&offset=${offset}&limit=${BATCH_LIMIT}&since_date=${dateToFetch}&mailbox=${mailbox}`;

            $.ajax({
                url: apiUrl,
                method: 'GET',
                dataType: 'json',
                timeout: 30000, // Increased timeout
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
                            renderInitialEmailList([]); // Ensure prompt is replaced if error/empty
                        }
                    }
                    isLoadingMore = false;
                    if (isLoadMore) $('#load-more-indicator').hide();
                    $('#fetch-emails-btn').prop('disabled', false).text('Fetch');
                }
            });
        }

        function viewEmail(msgno) {
            if (!msgno) return;
            clearErrors('detail');
            // No reply status in Sent view
            $('#email-detail-placeholder').hide();
            $('#email-detail-content').show();
            $('#detail-body').html('<div class="loading-indicator">Loading email...</div>');
            $('#detail-subject, #detail-to, #detail-date').text('');
            $('#detail-attachments').hide(); // Hide attachments initially
            $('#attachment-list').empty();

            $('#email-list-container .email-item').removeClass('active');
            const selectedItem = $(`#email-list-container .email-item[data-msgno="${msgno}"]`);
            selectedItem.addClass('active');
            activeMsgNo = msgno;

            // --- UPDATED: Add mailbox parameter to API call ---
            $.ajax({
                url: `api_get_email_details.php?msgno=${msgno}&mailbox=${mailbox}`,
                method: 'GET',
                dataType: 'json',
                timeout: 30000, // Increased timeout for potentially large emails
                success: function(response) {
                    if (msgno !== activeMsgNo) return; // Avoid race condition
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
            $('#detail-subject').text(details.subject || '(No Subject)');
            // --- CHANGED: Display To address ---
            $('#detail-to').text(details.to || 'N/A');
            $('#detail-date').text(details.date || 'N/A');

            // Display body (prefer plain text)
            let bodyContent = '<p><em>(Email content not available.)</em></p>';
            if (details.body_plain) {
                bodyContent = `<div class="email-detail-body-plain">${escapeHtml(details.body_plain)}</div>`;
            } else if (details.body_html) {
                // Fallback: Try to display HTML, but sanitize or use iframe for safety
                // Basic text extraction as a safer default:
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = details.body_html; // CAUTION: Potential XSS if not sanitized server-side or displayed in iframe
                const extractedText = tempDiv.textContent || tempDiv.innerText || "";
                bodyContent = `<div class="email-detail-body-plain">${escapeHtml(extractedText)}</div><p class="text-muted small mt-2"><em>(Displayed text extracted from HTML. Formatting may be lost.)</em></p>`;
                // --- Safter HTML display using iframe (Alternative) ---
                // Need CSS for .email-detail-body-html iframe { width: 100%; min-height: 300px; border: 1px solid #ccc; }
                // bodyContent = `<div class="email-detail-body-html"><iframe srcdoc="${escapeHtmlAttribute(details.body_html)}" sandbox="allow-same-origin"></iframe></div>`;
            }
            $('#detail-body').html(bodyContent);

            // --- NEW: Display Attachments ---
            const attachmentsContainer = $('#detail-attachments');
            const attachmentList = $('#attachment-list');
            attachmentList.empty(); // Clear previous list

            if (details.attachments && details.attachments.length > 0) {
                details.attachments.forEach(att => {
                    // Exclude inline images unless needed
                    if (att.disposition === 'attachment' || !att.cid) {
                        // URL encode filename for the GET parameter
                        const downloadUrl = `api_download_attachment.php?msgno=${details.message_no}&part_path=${att.part_path}&filename=${encodeURIComponent(att.filename)}&mailbox=${mailbox}`;
                        const fileSize = att.size > 0 ? formatBytes(att.size) : '';
                        const listItem = `
                            <li>
                                <i class="fas fa-paperclip"></i>
                                <a href="${downloadUrl}" target="_blank">${escapeHtml(att.filename)}</a>
                                ${fileSize ? `<span class="attachment-size">(${fileSize})</span>` : ''}
                            </li>`;
                        attachmentList.append(listItem);
                    }
                });
                attachmentsContainer.show(); // Show the attachment section
            } else {
                attachmentsContainer.hide(); // Hide if no attachments
            }
            // --- END NEW ---

            // Reply form is hidden by CSS, no need to manage its state here
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

        // --- NEW: Helper to format bytes ---
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        // --- NEW: Helper to escape HTML attributes (like srcdoc) ---
        function escapeHtmlAttribute(unsafe) {
            if (unsafe === null || typeof unsafe === 'undefined') return '';
            try {
                return unsafe.toString()
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;") // Important for attributes
                    .replace(/'/g, "&#039;"); // Important for attributes
            } catch (e) {
                console.error("Error escaping HTML Attribute:", e, unsafe);
                return '';
            }
        }


        $(document).ready(function() {
            // Fetch button click handler
            $('#fetch-emails-btn').on('click', function() {
                const selectedDate = $('#since-date-input').val();
                fetchEmailList(0, false, selectedDate); // Start new fetch from offset 0
            });

            // Infinite scroll for email list
            const listContainer = $('#email-list-container');
            listContainer.on('scroll', function() {
                if (isLoadingMore || noMoreEmails || !currentSinceDate) return;
                if (listContainer[0].scrollHeight <= listContainer.innerHeight()) return; // No scrollbar yet

                const threshold = 150; // Pixels from bottom
                const isNearBottom = listContainer[0].scrollHeight - listContainer.scrollTop() - listContainer.innerHeight() < threshold;

                if (isNearBottom) {
                    fetchEmailList(currentOffset, true, currentSinceDate); // Load more using current date
                }
            });

            // Initial message setup
            if (!$('#since-date-input').val()) {
                $('#email-list-container').html('<div class="loading-indicator">Select a date and click Fetch to view sent emails.</div>');
            }
        });
    </script>
</body>

</html>