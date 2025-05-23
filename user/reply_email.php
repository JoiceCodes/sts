<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_full_name"])) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $login_path = $uri . '/login.php';
    ob_start();
    header("Location: {$protocol}://{$host}{$login_path}");
    ob_end_flush();
    exit();
}

$user_id = $_SESSION["user_id"];
$email = $_SESSION["user_email"];
$username = $_SESSION["user_full_name"];
$pageTitle = "View Email";

$error_message = null;
$email_data = [
    'subject' => 'N/A',
    'from' => 'N/A',
    'to' => 'N/A',
    'date' => 'N/A',
    'body_html' => null,
    'body_plain' => null,
    'display_body' => 'Could not load email content.',
    'display_type' => 'plain',
];
$app_password = null;
$msgno = null;

if (!isset($_GET['msgno']) || !filter_var($_GET['msgno'], FILTER_VALIDATE_INT) || $_GET['msgno'] <= 0) {
    $error_message = "Invalid or missing message number.";
} else {
    $msgno = intval($_GET['msgno']);
}

function decodeBody($body, $encoding)
{
    switch ($encoding) {
        case 3: // BASE64
            return base64_decode($body);
        case 4: // QUOTED-PRINTABLE
            return quoted_printable_decode($body);
        case 0: // 7BIT
        case 1: // 8BIT
        case 2: // BINARY
        default:
            return $body;
    }
}

function getEmailBody($imap_stream, $msgno)
{
    $structure = @imap_fetchstructure($imap_stream, $msgno);
    $body_html = null;
    $body_plain = null;

    if (!$structure) {
        return ['plain' => null, 'html' => null];
    }

    if (isset($structure->parts)) {
        $parts_stack = $structure->parts;
        $part_paths = [];
        for ($i = 0; $i < count($structure->parts); $i++) {
            $part_paths[$i] = (string)($i + 1);
        }

        $current_part_index = 0;
        while ($current_part_index < count($parts_stack)) {
            $part = $parts_stack[$current_part_index];
            $part_path = $part_paths[$current_part_index];

            if (isset($part->parts)) {
                // If it's a container part (like multipart/alternative), add its subparts to the stack
                for ($i = 0; $i < count($part->parts); $i++) {
                    $parts_stack[] = $part->parts[$i];
                    $part_paths[] = $part_path . '.' . ($i + 1); // Append subpart index
                }
            } else {
                // It's a content part, check its type
                $ctype = strtoupper($part->subtype);
                $encoding = $part->encoding;

                if ($part->type == 0 && ($ctype == 'PLAIN' || $ctype == '')) { // TYPETEXT == 0
                    if ($body_plain === null) { // Get the first plain text part found
                        $plain_body_raw = @imap_fetchbody($imap_stream, $msgno, $part_path);
                        if ($plain_body_raw !== false) {
                            $body_plain = decodeBody($plain_body_raw, $encoding);
                            if (isset($part->parameters)) {
                                foreach ($part->parameters as $param) {
                                    if (strtoupper($param->attribute) == 'CHARSET') {
                                        $body_plain = mb_convert_encoding($body_plain, 'UTF-8', $param->value);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } elseif ($part->type == 0 && $ctype == 'HTML') { // TYPETEXT == 0
                    if ($body_html === null) { // Get the first HTML part found
                        $html_body_raw = @imap_fetchbody($imap_stream, $msgno, $part_path);
                        if ($html_body_raw !== false) {
                            $body_html = decodeBody($html_body_raw, $encoding);
                            if (isset($part->parameters)) {
                                foreach ($part->parameters as $param) {
                                    if (strtoupper($param->attribute) == 'CHARSET') {
                                        $body_html = mb_convert_encoding($body_html, 'UTF-8', $param->value);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $current_part_index++;
        }
    } else {
        // Not multipart, treat as single part (usually plain text or maybe HTML)
        $encoding = $structure->encoding;
        $body_raw = @imap_fetchbody($imap_stream, $msgno, "1"); // Fetch part 1
        if ($body_raw !== false) {
            $body_decoded = decodeBody($body_raw, $encoding);
            if (isset($structure->parameters)) {
                foreach ($structure->parameters as $param) {
                    if (strtoupper($param->attribute) == 'CHARSET') {
                        $body_decoded = mb_convert_encoding($body_decoded, 'UTF-8', $param->value);
                        break;
                    }
                }
            }
            if ($structure->type == 0 && strtoupper($structure->subtype) == 'HTML') {
                $body_html = $body_decoded;
            } else {
                $body_plain = $body_decoded;
            }
        }
    }

    return ['plain' => $body_plain, 'html' => $body_html];
}


if ($msgno && isset($connection)) {
    $stmt_app_password = $connection->prepare("SELECT app_password FROM gmail_app_password WHERE user_id = ?");
    if ($stmt_app_password) {
        $stmt_app_password->bind_param("i", $user_id);
        $stmt_app_password->execute();
        $result_app_password = $stmt_app_password->get_result();

        if ($result_app_password->num_rows > 0) {
            $row_app_password = $result_app_password->fetch_assoc();
            $app_password = $row_app_password['app_password'];
        } else {
            $error_message = "App Password not configured for your account (" . htmlspecialchars($email) . ").";
        }
        $stmt_app_password->close();
    } else {
        $error_message = "Database error preparing configuration fetch.";
        error_log("Error preparing app password query: " . $connection->error);
    }

    if ($app_password && !$error_message) {
        $inbox = null;
        try {
            $inbox = @imap_open("{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX", $email, $app_password, 0, 1); // Need RW for setflag

            if (!$inbox) {
                $imap_error = imap_last_error();
                $error_message = "Cannot connect to your Gmail (" . htmlspecialchars($email) . "). Verify App Password/Settings. [" . ($imap_error ?: 'Connection failed') . "]";
                error_log("IMAP Connection Error (RW): user=$email, error=" . ($imap_error ?: 'Unknown IMAP connection error'));
            } else {
                $headerInfo = @imap_headerinfo($inbox, $msgno);

                if (!$headerInfo) {
                    $error_message = "Could not retrieve email header information for message number $msgno.";
                    error_log("imap_headerinfo failed for user $email, msgno $msgno: " . imap_last_error());
                } else {
                    $email_data['subject'] = isset($headerInfo->subject) ? mb_decode_mimeheader($headerInfo->subject) : '(No Subject)';
                    $email_data['from'] = isset($headerInfo->fromaddress) ? mb_decode_mimeheader($headerInfo->fromaddress) : 'N/A';
                    $email_data['to'] = isset($headerInfo->toaddress) ? mb_decode_mimeheader($headerInfo->toaddress) : 'N/A';
                    $email_data['date'] = isset($headerInfo->date) ? date("D, M j, Y g:i:s A T", strtotime($headerInfo->date)) : 'N/A';

                    $bodies = getEmailBody($inbox, $msgno);
                    $email_data['body_html'] = $bodies['html'];
                    $email_data['body_plain'] = $bodies['plain'];

                    if ($email_data['body_html'] !== null) {
                        $email_data['display_body'] = $email_data['body_html'];
                        $email_data['display_type'] = 'html';
                    } elseif ($email_data['body_plain'] !== null) {
                        $email_data['display_body'] = nl2br(htmlspecialchars($email_data['body_plain'], ENT_QUOTES, 'UTF-8'));
                        $email_data['display_type'] = 'plain';
                    } else {
                        $email_data['display_body'] = 'Email body is empty or could not be decoded.';
                        $email_data['display_type'] = 'plain';
                    }

                    if (!@imap_setflag_full($inbox, $msgno, "\\Seen", ST_UID)) { // Use 0 if $msgno is sequence, ST_UID if it's UID
                        // Log error if marking as read fails, but don't stop showing the email
                        error_log("Failed to mark message $msgno as read for user $email: " . imap_last_error());
                    }
                }
                @imap_close($inbox);
            }
        } catch (Exception $e) {
            $error_message = "An exception occurred: " . $e->getMessage();
            error_log("IMAP Exception for user $email: " . $e->getMessage());
            if ($inbox) {
                @imap_close($inbox);
            }
        }
    }
} elseif (!$msgno) {
    // Error message already set if msgno is invalid/missing
} else {
    $error_message = "Database connection not available.";
}


ob_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once __DIR__ . "/../components/head.php"; ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> - i-Secure</title>
    <style>
        .email-header {
            border-bottom: 1px solid #e3e6f0;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
        }

        .email-header dt {
            font-weight: bold;
        }

        .email-body {
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: #fff;
            border: 1px solid #e3e6f0;
            border-radius: .35rem;
        }

        .email-body-plain {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: monospace, monospace;
        }

        .reply-section {
            margin-top: 2rem;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once __DIR__ . "/../components/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once __DIR__ . "/../components/user_topbar.php"; ?>
                <div class="container-fluid">

                    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error_message); ?>
                            <a href="all_notifications.php" class="alert-link ml-2">Return to list</a>
                        </div>
                    <?php else: ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Email Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="email-header">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-2">Subject:</dt>
                                        <dd class="col-sm-10"><?php echo htmlspecialchars($email_data['subject']); ?></dd>

                                        <dt class="col-sm-2">From:</dt>
                                        <dd class="col-sm-10"><?php echo htmlspecialchars($email_data['from']); ?></dd>

                                        <dt class="col-sm-2">To:</dt>
                                        <dd class="col-sm-10"><?php echo htmlspecialchars($email_data['to']); ?></dd>

                                        <dt class="col-sm-2">Date:</dt>
                                        <dd class="col-sm-10"><?php echo htmlspecialchars($email_data['date']); ?></dd>
                                    </dl>
                                </div>

                                <div class="email-body <?php echo $email_data['display_type'] == 'html' ? 'email-body-html' : 'email-body-plain'; ?>">
                                    <?php echo $email_data['display_body']; // Display pre-formatted/sanitized body 
                                    ?>
                                    <?php if ($email_data['display_type'] == 'html'): ?>
                                        <p class="text-muted small mt-3"><em>(Displayed as HTML. Be cautious of links and external content.)</em></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4 reply-section">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Reply</h6>
                            </div>
                            <div class="card-body">
                                <form action="send_reply_handler.php" method="POST">
                                    <input type="hidden" name="original_msgno" value="<?php echo $msgno; ?>">
                                    <input type="hidden" name="original_subject" value="<?php echo htmlspecialchars($email_data['subject']); ?>">
                                    <input type="hidden" name="reply_to_address" value="<?php echo htmlspecialchars($email_data['from']); ?>">

                                    <div class="form-group">
                                        <label for="replyTo">To:</label>
                                        <input type="email" class="form-control" id="replyTo" name="reply_to" value="<?php echo htmlspecialchars($email_data['from']); ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="replySubject">Subject:</label>
                                        <input type="text" class="form-control" id="replySubject" name="reply_subject" value="Re: <?php echo htmlspecialchars($email_data['subject']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="replyBody">Message:</label>
                                        <textarea class="form-control" id="replyBody" name="reply_body" rows="8" required></textarea>
                                        <small class="form-text text-muted">Original message will be quoted below your reply.</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Send Reply</button>
                                    <a href="all_notifications.php" class="btn btn-secondary ml-2">Cancel</a>
                                </form>
                            </div>
                        </div>

                    <?php endif; ?>

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
</body>

</html>
<?php
ob_end_flush();
?>