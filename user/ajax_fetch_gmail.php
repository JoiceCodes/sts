<?php
session_start();
require_once __DIR__ . "/../config/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['error' => 'Not authenticated', 'emails' => []]);
    exit();
}

/*
if (!isset($connection)) {
     echo json_encode(['error' => 'Database connection failed', 'emails' => []]);
     exit();
}
*/

$user_id = $_SESSION["user_id"];
$email = $_SESSION["user_email"];

function fetchEmailsDirectly($accountEmail, $appPassword, $senderToFilter, $limit = 50)
{
    $emails = []; $inboxRead = null; $fetch_error = null;
    try {
        $mailbox_path = "{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX";
        $inboxRead = @imap_open($mailbox_path, $accountEmail, $appPassword, OP_READONLY, 1);
        if (!$inboxRead) {
            return ['error' => 'IMAP Connection Failed: '.(imap_last_error() ?: 'Unknown')];
        }
        $searchCriteria = 'FROM "' . $senderToFilter . '"';
        $emails_uids = imap_search($inboxRead, $searchCriteria, SE_UID);
        if ($emails_uids) {
             rsort($emails_uids);
             $limited_uids = array_slice($emails_uids, 0, $limit);
             if (!empty($limited_uids)) {
                 $uid_string = implode(',', $limited_uids);
                 $overviews = imap_fetch_overview($inboxRead, $uid_string, FT_UID);
                 if ($overviews) {
                     foreach ($overviews as $overview) {
                         if (!isset($overview->uid)) continue;
                         $emails[] = [
                             'uid' => $overview->uid,
                             'subject' => isset($overview->subject) ? mb_decode_mimeheader($overview->subject) : 'No Subject',
                             'sent_at_iso' => isset($overview->date) ? date('c', strtotime($overview->date)) : date('c'),
                             'sent_at_display_date' => isset($overview->date) ? date("M j, Y", strtotime($overview->date)) : 'N/A',
                             'sent_at_display_time' => isset($overview->date) ? date("g:i a", strtotime($overview->date)) : 'N/A',
                             'is_read' => isset($overview->seen) ? (int)$overview->seen : 0,
                         ];
                     }
                     usort($emails, function($a, $b) { return strtotime($b['sent_at_iso']) <=> strtotime($a['sent_at_iso']); });
                 } else { $fetch_error = 'Failed fetching details.'; }
             }
        }
        if($inboxRead) imap_close($inboxRead);
    } catch (Exception $e) {
        if ($inboxRead) @imap_close($inboxRead);
        return ['error' => 'Exception: ' . $e->getMessage()];
    }
    if ($fetch_error && empty($emails)) { return ['error' => $fetch_error]; }
    return $emails;
}

$sender_filter_address = 'isecurenetworkgroup@gmail.com';
$fetch_limit = 50;

$app_password = null;
$error_message = null;

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
    } else { $error_message = "DB config error."; }
} else {
    if (!$app_password) { $error_message = "App Password config missing."; }
}

$result = ['emails' => []];

if ($app_password && !$error_message) {
    $fetchResult = fetchEmailsDirectly($email, $app_password, $sender_filter_address, $fetch_limit);

    if (isset($fetchResult['error'])) {
        $result['error'] = $fetchResult['error'];
    } else {
        $result['emails'] = $fetchResult;
    }
} elseif ($error_message) {
    $result['error'] = $error_message;
} else {
    $result['error'] = 'App Password not available.';
}

echo json_encode($result);
exit();
?>