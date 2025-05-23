<?php
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// --- Define TYPEMAP Constant ---
// Define mapping from IMAP type integers to MIME type strings
// Needs to be defined before getDecodedEmailBody is called.
const TYPEMAP = [
    TYPETEXT        => 'text',
    TYPEMULTIPART   => 'multipart',
    TYPEMESSAGE     => 'message',
    TYPEAPPLICATION => 'application',
    TYPEAUDIO       => 'audio',
    TYPEIMAGE       => 'image',
    TYPEVIDEO       => 'video',
    TYPEMODEL       => 'model', // Less common
    TYPEOTHER       => 'other', // Fallback
];
// --- End TYPEMAP Definition ---

if (!isset($_SESSION["user_id"], $_SESSION["user_email"])) {
    echo json_encode(['error' => 'User not authenticated.']);
    exit();
}
if (!isset($_GET['msgno']) || !filter_var($_GET['msgno'], FILTER_VALIDATE_INT) || $_GET['msgno'] <= 0) {
    echo json_encode(['error' => 'Invalid or missing message number.']);
    exit();
}

$user_id = $_SESSION["user_id"];
$email = $_SESSION["user_email"];
$msgno = intval($_GET['msgno']);
$is_read_view = isset($_GET['view']) && $_GET['view'] === 'read'; // Check if viewing from read history

$mailbox_name = isset($_GET['mailbox']) ? trim($_GET['mailbox']) : 'INBOX';
if (!preg_match('/^[a-zA-Z0-9_\-\/\[\] ]+$/', $mailbox_name)) {
    $mailbox_name = 'INBOX';
}
if (strtolower($mailbox_name) === 'sent') {
    $mailbox_name = '[Gmail]/Sent Mail';
}
$is_sent_mailbox = (strpos($mailbox_name, 'Sent') !== false);

$app_password = null;
$error_message = null;

// Get App Password (same as before)
if (isset($connection)) {
    $stmt_app_password = $connection->prepare("SELECT app_password FROM gmail_app_password WHERE user_id = ?");
    if ($stmt_app_password) {
        $stmt_app_password->bind_param("i", $user_id);
        $stmt_app_password->execute();
        $result_app_password = $stmt_app_password->get_result();
        if ($result_app_password->num_rows > 0) {
            $app_password = $result_app_password->fetch_assoc()['app_password'];
        } else { $error_message = "App Password not configured."; }
        $stmt_app_password->close();
    } else { $error_message = "DB error preparing config fetch: " . $connection->error; }
} else { $error_message = "Database connection failed."; }


// decodeImapBody function (same as before)
function decodeImapBody($body, $encoding) {
    switch ($encoding) {
        case ENCBASE64: return base64_decode($body);
        case ENCQUOTEDPRINTABLE: return quoted_printable_decode($body);
        default: return $body;
    }
}

// getDecodedEmailBody function (Uses TYPEMAP)
function getDecodedEmailBody($imap_stream, $msgno, $mailbox_name) {
    $structure = @imap_fetchstructure($imap_stream, $msgno);
    $body_html = null; $body_plain = null; $attachments = [];
    if (!$structure) {
        error_log("Failed to fetch structure for msgno $msgno in mailbox '$mailbox_name': " . imap_last_error());
        return ['plain' => null, 'html' => null, 'attachments' => []];
    }
    $parts_stack = [];
    if (isset($structure->parts)) {
        $parts_stack = $structure->parts;
        for ($i = 0; $i < count($structure->parts); $i++) $parts_stack[$i]->path_number = (string)($i + 1);
    } else {
        if (isset($structure->type)) {
            $structure->path_number = '1';
            $parts_stack[] = $structure;
        } else {
             error_log("Structure lacks parts and type info for msgno $msgno in mailbox '$mailbox_name'");
             return ['plain' => null, 'html' => null, 'attachments' => []];
        }
    }

    $current_part_index = 0;
    while ($current_part_index < count($parts_stack)) {
        $part = $parts_stack[$current_part_index];
        $part_path = $part->path_number;

        if (isset($part->parts)) {
            for ($i = 0; $i < count($part->parts); $i++) {
                $sub_part = $part->parts[$i];
                $sub_part->path_number = $part_path . '.' . ($i + 1);
                $parts_stack[] = $sub_part;
            }
        } else {
            $ctype_primary = isset($part->type) ? $part->type : TYPEOTHER; // Default to TYPEOTHER if missing
            $ctype_secondary = isset($part->subtype) ? strtoupper($part->subtype) : '';
            $encoding = isset($part->encoding) ? $part->encoding : 0;
            $disposition = (isset($part->disposition) && strtoupper($part->disposition) == 'ATTACHMENT');
            $is_inline = (isset($part->disposition) && strtoupper($part->disposition) == 'INLINE');
            $charset = 'UTF-8';
            if (isset($part->parameters)) { foreach ($part->parameters as $param) { if (strtoupper($param->attribute) == 'CHARSET') { $charset = strtoupper($param->value); } } }
            if (!in_array($charset, mb_list_encodings())) { $charset = 'UTF-8'; }

            if ($ctype_primary == TYPETEXT && !$disposition && !$is_inline) {
                $body_raw = @imap_fetchbody($imap_stream, $msgno, $part_path);
                if ($body_raw !== false) {
                    $body_decoded = decodeImapBody($body_raw, $encoding);
                    $current_encoding = mb_detect_encoding($body_decoded, mb_detect_order(), true); $final_body = null;
                    if ($current_encoding && strtoupper($current_encoding) !== 'UTF-8') { $body_utf8 = @mb_convert_encoding($body_decoded, 'UTF-8', $current_encoding); if ($body_utf8 !== false) $final_body = $body_utf8; }
                    elseif (!$current_encoding && function_exists('iconv')) { $body_utf8 = @iconv($charset, 'UTF-8//IGNORE', $body_decoded); if ($body_utf8 !== false) $final_body = $body_utf8; }
                    if ($final_body === null) $final_body = $body_decoded;
                    if (!mb_check_encoding($final_body, 'UTF-8')) { $final_body = mb_convert_encoding($final_body, 'UTF-8', 'UTF-8'); }
                    if ($ctype_secondary == 'HTML' && $body_html === null) $body_html = $final_body;
                    elseif (($ctype_secondary == 'PLAIN' || $ctype_secondary == '') && $body_plain === null) $body_plain = $final_body;
                } else { error_log("Failed to fetch body part $part_path for msgno $msgno in '$mailbox_name': " . imap_last_error()); }
            } elseif ($disposition || $is_inline || $ctype_primary > TYPETEXT) {
                $filename = "attachment"; $cid = null;
                 if (isset($part->dparameters)) { foreach ($part->dparameters as $param) { if (strtoupper($param->attribute) == 'FILENAME') { $decoded_filename = iconv_mime_decode($param->value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8"); if ($decoded_filename) $filename = $decoded_filename; break; } } }
                 if ($filename === "attachment" && isset($part->parameters)) { foreach ($part->parameters as $param) { if (strtoupper($param->attribute) == 'NAME') { $decoded_filename = iconv_mime_decode($param->value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8"); if ($decoded_filename) $filename = $decoded_filename; break; } } }
                 if (isset($part->id)) { $cid = trim($part->id, '<>'); }
                 $filename = preg_replace('/[\x00-\x1F\x7F]/u', '', $filename); $filename = str_replace(['/', '\\', '..'], '_', $filename);

                $attachments[] = [
                    'filename' => $filename ?: 'unnamed_attachment',
                    'part_path' => $part_path,
                    'size' => isset($part->bytes) ? $part->bytes : 0,
                    'disposition' => $disposition ? 'attachment' : ($is_inline ? 'inline' : 'unknown'),
                    'cid' => $cid,
                    // --- CORRECTED USAGE OF TYPEMAP ---
                    'mime_type' => (isset(TYPEMAP[$ctype_primary]) ? TYPEMAP[$ctype_primary] : 'application') . '/' . ($ctype_secondary ? strtolower($ctype_secondary) : 'octet-stream'),
                ];
            }
        }
        $current_part_index++;
    }

    if ($body_html !== null && $body_plain === null) {
        $plain_temp = preg_replace('/<br\s*\/?>/i', "\n", $body_html);
        $plain_temp = strip_tags($plain_temp);
        $body_plain = html_entity_decode($plain_temp, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $body_plain = trim(preg_replace('/\n{3,}/', "\n\n", $body_plain));
    }
    return ['plain' => $body_plain, 'html' => $body_html, 'attachments' => $attachments];
}


// Main execution logic (same as before)
if ($app_password && !$error_message) {
    $inbox = null; $details = null;
    try {
        $should_mark_read = !$is_read_view && !$is_sent_mailbox;
        $imap_options = $should_mark_read ? 0 : OP_READONLY;
        $imap_path = "{imap.gmail.com:993/imap/ssl/novalidate-cert}" . $mailbox_name;
        $inbox = @imap_open($imap_path, $email, $app_password, $imap_options, 1);

        if (!$inbox) { $error_message = "IMAP connection failed for mailbox '$mailbox_name': " . imap_last_error(); }
        else {
            imap_timeout(IMAP_READTIMEOUT, 30);
            $headerInfo = @imap_headerinfo($inbox, $msgno);
            if (!$headerInfo) { $error_message = "Could not retrieve email header for msgno $msgno in '$mailbox_name': " . imap_last_error(); }
            else {
                $bodies_data = getDecodedEmailBody($inbox, $msgno, $mailbox_name); // Pass mailbox name

                 // Message ID, Subject, From, To decoding (same as before)
                 $message_id = null;
                 if (isset($headerInfo->message_id)) $message_id = $headerInfo->message_id;
                 else { $full_header = @imap_fetchheader($inbox, $msgno); if ($full_header && preg_match('/^Message-ID:\s*([^\r\n]+)/im', $full_header, $matches)) $message_id = trim($matches[1]); }

                 $subject = '(No Subject)';
                 if (isset($headerInfo->subject)) { $decoded_subject = iconv_mime_decode($headerInfo->subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8"); if ($decoded_subject === false || $decoded_subject === null || $decoded_subject === '') $decoded_subject = mb_decode_mimeheader($headerInfo->subject); if (!mb_check_encoding($decoded_subject, 'UTF-8')) $decoded_subject = mb_convert_encoding($decoded_subject, 'UTF-8', mb_list_encodings()); $subject = $decoded_subject ?: '(Subject decoding failed)'; }

                 $from = 'N/A';
                 if (isset($headerInfo->from) && count($headerInfo->from) > 0) { $from_obj = $headerInfo->from[0]; $from_personal = isset($from_obj->personal) ? iconv_mime_decode($from_obj->personal, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8") : ''; $from_email_addr = (isset($from_obj->mailbox) && isset($from_obj->host)) ? $from_obj->mailbox . '@' . $from_obj->host : ''; if ($from_personal && $from_email_addr) $from = $from_personal . " <" . $from_email_addr . ">"; elseif ($from_email_addr) $from = $from_email_addr; elseif ($from_personal) $from = $from_personal; else $from = $headerInfo->fromaddress ?? 'N/A'; }
                 elseif (isset($headerInfo->fromaddress)) { $from = $headerInfo->fromaddress; }

                 $to = 'N/A';
                 if (isset($headerInfo->toaddress)) { $decoded_to = iconv_mime_decode($headerInfo->toaddress, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8"); if ($decoded_to) $to = $decoded_to; }
                 elseif (isset($headerInfo->to) && is_array($headerInfo->to)) { $toList = []; foreach($headerInfo->to as $to_obj) { $to_personal = isset($to_obj->personal) ? iconv_mime_decode($to_obj->personal, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8") : ''; $to_email_addr = (isset($to_obj->mailbox) && isset($to_obj->host)) ? $to_obj->mailbox . '@' . $to_obj->host : ''; if ($to_personal && $to_email_addr) $toList[] = $to_personal . " <" . $to_email_addr . ">"; elseif ($to_email_addr) $toList[] = $to_email_addr; } if(!empty($toList)) $to = implode(', ', $toList); }


                $details = [
                    'message_no' => $msgno,
                    'message_id' => $message_id,
                    'subject' => $subject,
                    'from' => $from,
                    'to' => $to,
                    'date' => isset($headerInfo->date) ? date("D, M j, Y g:i:s A T", strtotime($headerInfo->date)) : 'N/A',
                    'body_html' => $bodies_data['html'],
                    'body_plain' => $bodies_data['plain'],
                    'attachments' => $bodies_data['attachments']
                ];

                if ($should_mark_read) {
                    if (!@imap_setflag_full($inbox, "$msgno", "\\Seen")) {
                        error_log("Failed to mark message $msgno as read for user $email in '$mailbox_name': " . imap_last_error());
                    }
                }

                echo json_encode(['details' => $details]);
                @imap_close($inbox);
                exit();
            }
        }
        if ($inbox) @imap_close($inbox);
    } catch (Exception $e) {
        error_log("IMAP Exception processing details for $email, msgno $msgno, mailbox '$mailbox_name': " . $e->getMessage());
        $error_message = 'An application error occurred while processing email details.';
        if ($inbox) @imap_close($inbox);
    }
}

echo json_encode(['error' => $error_message ?: 'An unknown error occurred.']);
exit();
?>