<?php
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!isset($_SESSION["user_id"], $_SESSION["user_email"])) {
    echo json_encode(['error' => 'User not authenticated.']);
    exit();
}

$user_id = $_SESSION["user_id"];
$email = $_SESSION["user_email"];
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 50;
$offset = isset($_GET['offset']) ? max(0, intval($_GET['offset'])) : 0;
$status_filter = isset($_GET['status']) && $_GET['status'] === 'read' ? 'SEEN' : 'UNSEEN';

// --- NEW: Mailbox parameter ---
$mailbox_name = isset($_GET['mailbox']) ? trim($_GET['mailbox']) : 'INBOX';
// Basic validation for mailbox name (adjust if needed)
if (!preg_match('/^[a-zA-Z0-9_\-\/\[\] ]+$/', $mailbox_name)) {
    $mailbox_name = 'INBOX'; // Fallback safely
}
// For Gmail Sent folder, use the standard name
if (strtolower($mailbox_name) === 'sent') {
    $mailbox_name = '[Gmail]/Sent Mail';
}
// --- END NEW ---


$since_date_param = isset($_GET['since_date']) ? $_GET['since_date'] : null;
$since_date_formatted = null;

if ($since_date_param) {
    $date_obj = DateTime::createFromFormat('Y-m-d', $since_date_param);
    if ($date_obj && $date_obj->format('Y-m-d') === $since_date_param) {
        $since_date_formatted = $date_obj->format('d-M-Y');
    }
}
if (!$since_date_formatted) {
    $since_date_formatted = date("d-M-Y", strtotime("-1 month"));
    error_log("Invalid or missing since_date param, using default (1 month ago): " . $since_date_formatted);
}

$app_password = null;
$fetched_emails = [];
$error_message = null;

// ... (Database connection and app password fetching remains the same) ...
if (isset($connection)) {
    $stmt_app_password = $connection->prepare("SELECT app_password FROM gmail_app_password WHERE user_id = ?");
    if ($stmt_app_password) {
        $stmt_app_password->bind_param("i", $user_id);
        $stmt_app_password->execute();
        $result_app_password = $stmt_app_password->get_result();
        if ($result_app_password->num_rows > 0) {
            $app_password = $result_app_password->fetch_assoc()['app_password'];
        } else {
            $error_message = "App Password not configured.";
        }
        $stmt_app_password->close();
    } else {
        $error_message = "DB error preparing config fetch: " . $connection->error;
    }
} else {
    $error_message = "Database connection failed.";
}

// --- UPDATED FUNCTION SIGNATURE ---
function fetchEmailsByStatusDateAndMailbox($accountEmail, $appPassword, $mailbox, $statusCriteria, $sinceDateFormatted, $limit, $offset)
{
    $emails = [];
    $more_available = false;
    $total_found = 0;
    $inboxRead = null;
    try {
        // --- UPDATED: Use mailbox parameter ---
        $imap_path = "{imap.gmail.com:993/imap/ssl/novalidate-cert}" . $mailbox;
        // --- END UPDATED ---

        imap_timeout(IMAP_OPENTIMEOUT, 15);
        imap_timeout(IMAP_READTIMEOUT, 30);
        imap_timeout(IMAP_WRITETIMEOUT, 30);
        imap_timeout(IMAP_CLOSETIMEOUT, 5);

        $inboxRead = @imap_open($imap_path, $accountEmail, $appPassword, OP_READONLY, 1, ['DISABLE_AUTHENTICATOR' => 'GSSAPI']);

        if (!$inboxRead) {
            return ['error' => "IMAP connection failed for mailbox '$mailbox': " . imap_last_error()];
        }

        // --- Search criteria might need adjustment for Sent ---
        // For now, we keep using SEEN/UNSEEN + SINCE. Gmail usually handles flags reasonably in Sent.
        // Alternatives could be just 'SINCE ...' or 'FROM "your_email@example.com" SINCE ...'
        $searchCriteria = $statusCriteria . ' SINCE "' . $sinceDateFormatted . '"';
        // If it's the Sent mailbox, maybe always search ALL within the date?
        // if ($mailbox === '[Gmail]/Sent Mail') {
        //    $searchCriteria = 'SINCE "' . $sinceDateFormatted . '"';
        // }


        $all_uids = imap_search($inboxRead, $searchCriteria, SE_UID);

        if ($all_uids === false) {
            $last_error = imap_last_error();
            @imap_close($inboxRead);
            error_log("IMAP search failed for $accountEmail, mailbox '$mailbox', criteria ($searchCriteria): " . $last_error);
             if (strpos($last_error, 'Search timed out') !== false) {
                 return ['error' => 'Email server search timed out. Please try again later or refine the date range.'];
             }
            return ['error' => "IMAP search failed in '$mailbox'. Please try again later."];
        }

        $total_found = count($all_uids);
        $uids_to_process = $all_uids;
        $uids_to_fetch = [];

        if ($total_found > 0) {
            rsort($uids_to_process, SORT_NUMERIC);
            if ($limit > 0) {
                if ($offset < $total_found) {
                    $uids_to_fetch = array_slice($uids_to_process, $offset, $limit);
                    if (($offset + count($uids_to_fetch)) < $total_found) {
                        $more_available = true;
                    }
                } else {
                    $uids_to_fetch = [];
                    $more_available = false;
                }
            } else {
                $uids_to_fetch = $uids_to_process;
                $more_available = false;
            }
        }

        if (!empty($uids_to_fetch)) {
            if (!function_exists('imap_fetch_overview')) {
                @imap_close($inboxRead);
                return ['error' => 'IMAP function imap_fetch_overview not available on the server.'];
            }

            $overviews = @imap_fetch_overview($inboxRead, implode(',', $uids_to_fetch), FT_UID);

            if ($overviews && is_array($overviews)) {
                foreach ($overviews as $overview) {
                    if (!is_object($overview) || !isset($overview->msgno, $overview->uid, $overview->date)) {
                        error_log("Skipping invalid overview entry in mailbox '$mailbox'.");
                        continue;
                    }

                    // ... (Subject and From decoding remains the same) ...
                    $subject = '(No Subject)';
                     if (isset($overview->subject)) {
                         $decoded_subject = @iconv_mime_decode($overview->subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8");
                         if ($decoded_subject === false || $decoded_subject === null || $decoded_subject === '') {
                             $decoded_subject = @mb_decode_mimeheader($overview->subject);
                         }
                         if ($decoded_subject !== false && !mb_check_encoding($decoded_subject, 'UTF-8')) {
                             $decoded_subject = mb_convert_encoding($decoded_subject, 'UTF-8', mb_list_encodings());
                         }
                         $subject = $decoded_subject ?: '(Subject decoding failed)';
                     }

                     $is_seen = (isset($overview->seen) && $overview->seen);

                     $from_address = 'N/A';
                     if (isset($overview->from)) {
                         $from_address_decoded = @iconv_mime_decode($overview->from, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8");
                         if ($from_address_decoded === false || $from_address_decoded === null || $from_address_decoded === '') {
                             $from_address_decoded = @mb_decode_mimeheader($overview->from);
                         }
                         if ($from_address_decoded !== false && !mb_check_encoding($from_address_decoded, 'UTF-8')) {
                             $from_address_decoded = mb_convert_encoding($from_address_decoded, 'UTF-8', mb_list_encodings());
                         }
                         $from_address = $from_address_decoded ?: '(Sender decoding failed)';
                     }

                     // --- NEW: Optionally fetch 'to' address if available in overview ---
                     // Note: 'to' is often NOT in the overview, depends on IMAP server.
                     $to_address = null;
                     if (isset($overview->to)) {
                        $to_address_decoded = @iconv_mime_decode($overview->to, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8");
                         if ($to_address_decoded === false || $to_address_decoded === null || $to_address_decoded === '') {
                              $to_address_decoded = @mb_decode_mimeheader($overview->to);
                         }
                         if ($to_address_decoded !== false && !mb_check_encoding($to_address_decoded, 'UTF-8')) {
                             $to_address_decoded = mb_convert_encoding($to_address_decoded, 'UTF-8', mb_list_encodings());
                         }
                         $to_address = $to_address_decoded ?: null; // Keep null if decode fails
                     }
                     // --- END NEW ---

                    $emails[] = [
                        'subject' => $subject,
                        'from' => $from_address,
                        'to' => $to_address, // Include 'to' if fetched
                        'sent_at' => date("Y-m-d H:i:s", strtotime($overview->date)),
                        'message_no' => $overview->msgno,
                        'uid' => $overview->uid,
                        'seen' => $is_seen
                    ];
                }
                usort($emails, function ($a, $b) {
                    return strtotime($b['sent_at']) <=> strtotime($a['sent_at']);
                });

            } else {
                $last_error = imap_last_error();
                error_log("IMAP fetch_overview failed for UIDs ($accountEmail, mailbox '$mailbox'): " . $last_error);
                $emails = [];
                $more_available = false;
            }
        } else {
            $emails = [];
            $more_available = false;
        }

        @imap_close($inboxRead);
    } catch (Exception $e) {
        error_log("IMAP Exception in fetchEmailsByStatusDateAndMailbox for $accountEmail, mailbox '$mailbox': " . $e->getMessage());
        if ($inboxRead) @imap_close($inboxRead);
        return ['error' => "An application error occurred while fetching emails from '$mailbox'."];
    }
    return ['emails' => $emails, 'more_available' => $more_available, 'total_approx' => $total_found];
}

if ($app_password && !$error_message) {
    // --- UPDATED FUNCTION CALL ---
    $fetchResult = fetchEmailsByStatusDateAndMailbox($email, $app_password, $mailbox_name, $status_filter, $since_date_formatted, $limit, $offset);
    echo json_encode($fetchResult);
} else {
    echo json_encode(['error' => $error_message ?: 'Could not retrieve app password.']);
}
exit();
?>