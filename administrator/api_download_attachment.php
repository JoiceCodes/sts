<?php
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . '/../vendor/autoload.php'; // Assuming Composer autoload

ini_set('display_errors', 0); // Keep errors logged, not displayed
error_reporting(E_ALL);
set_time_limit(0); // Allow long downloads
ob_start(); // Start output buffering

// --- Authentication ---
if (!isset($_SESSION["user_id"], $_SESSION["user_email"])) {
    ob_end_clean(); // Clean buffer before error output
    http_response_code(401); // Unauthorized
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not authenticated.']);
    exit();
}

// --- Input Validation ---
if (
    !isset($_GET['msgno']) || !filter_var($_GET['msgno'], FILTER_VALIDATE_INT) || $_GET['msgno'] <= 0 ||
    !isset($_GET['part_path']) || empty($_GET['part_path']) || !preg_match('/^[1-9][0-9]*(\.[1-9][0-9]*)*$/', $_GET['part_path']) // Basic validation for part path format
) {
    ob_end_clean();
    http_response_code(400); // Bad Request
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid or missing message number or part path.']);
    exit();
}
if (!isset($_GET['filename']) || empty(trim($_GET['filename']))) {
     ob_end_clean();
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing filename.']);
    exit();
}

$user_id = $_SESSION["user_id"];
$email = $_SESSION["user_email"];
$msgno = intval($_GET['msgno']);
$part_path = $_GET['part_path'];
$filename = trim($_GET['filename']);
// Optional: Sanitize filename further to prevent potential issues
$filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename); // Remove control characters
$filename = str_replace(['/', '\\', '..'], '_', $filename); // Replace potentially problematic characters


// Allow specifying mailbox (defaults to INBOX, but Sent might be needed)
$mailbox_name = isset($_GET['mailbox']) ? trim($_GET['mailbox']) : 'INBOX';
// Basic validation for mailbox name (adjust if needed)
if (!preg_match('/^[a-zA-Z0-9_\-\/\[\] ]+$/', $mailbox_name)) {
    $mailbox_name = 'INBOX'; // Fallback safely
}

// --- Get App Password ---
$app_password = null;
$db_error = null;
if (isset($connection)) {
    $stmt_app_password = $connection->prepare("SELECT app_password FROM gmail_app_password WHERE user_id = ?");
    if ($stmt_app_password) {
        $stmt_app_password->bind_param("i", $user_id);
        $stmt_app_password->execute();
        $result_app_password = $stmt_app_password->get_result();
        if ($result_app_password->num_rows > 0) {
            $app_password = $result_app_password->fetch_assoc()['app_password'];
        } else {
            $db_error = "App Password not configured.";
        }
        $stmt_app_password->close();
    } else {
        $db_error = "DB error preparing config fetch: " . $connection->error;
    }
} else {
    $db_error = "Database connection failed.";
}

if (!$app_password) {
    ob_end_clean();
    http_response_code(500); // Internal Server Error
    header('Content-Type: application/json');
    echo json_encode(['error' => $db_error ?: 'Could not retrieve app password.']);
    exit();
}

// --- IMAP Connection & Fetching ---
$inbox = null;
$imap_error = null;
try {
    $imap_path = "{imap.gmail.com:993/imap/ssl/novalidate-cert}" . $mailbox_name;
    $inbox = @imap_open($imap_path, $email, $app_password, OP_READONLY, 1);

    if (!$inbox) {
        $imap_error = "IMAP connection failed: " . imap_last_error();
    } else {
        // Fetch structure to get encoding and size (optional, but good for Content-Length)
        $structure = @imap_fetchstructure($inbox, $msgno);
        $encoding = 0; // Default: 7bit/8bit/binary
        $size = 0;

        if ($structure) {
            $part = null;
            // Function to find the part by path
            function findPartByPath($parts, $path) {
                $pathSegments = explode('.', $path);
                $currentPart = null;
                $currentIndex = array_shift($pathSegments) - 1; // Path is 1-based, array is 0-based

                if (isset($parts[$currentIndex])) {
                    $currentPart = $parts[$currentIndex];
                    foreach ($pathSegments as $segment) {
                        $subIndex = $segment - 1;
                        if (isset($currentPart->parts[$subIndex])) {
                            $currentPart = $currentPart->parts[$subIndex];
                        } else {
                            return null; // Path segment not found
                        }
                    }
                    return $currentPart;
                }
                return null; // Initial path segment not found
            }

            $foundPart = findPartByPath($structure->parts ?? [$structure], $part_path); // Handle case where structure itself is the part

            if ($foundPart) {
                $encoding = $foundPart->encoding ?? 0;
                $size = $foundPart->bytes ?? 0;
            } else {
                 error_log("Could not find part structure for path $part_path in msgno $msgno");
                 // Continue without size/specific encoding if structure part not found
            }
        } else {
             error_log("Could not fetch structure for msgno $msgno: " . imap_last_error());
             // Continue without size/specific encoding if structure fails
        }


        // Fetch the body part
        $body = @imap_fetchbody($inbox, $msgno, $part_path);

        if ($body === false) {
            $imap_error = "Failed to fetch attachment part $part_path for msgno $msgno: " . imap_last_error();
        } else {
            // Decode the body based on encoding
            switch ($encoding) {
                case ENCBASE64: // 3
                    $decoded_body = base64_decode($body);
                    break;
                case ENCQUOTEDPRINTABLE: // 4
                    $decoded_body = quoted_printable_decode($body);
                    break;
                // case ENC7BIT: // 0
                // case ENC8BIT: // 1
                // case ENCBINARY: // 2
                // case ENCOTHER: // 5
                default:
                    $decoded_body = $body;
                    break;
            }

            if ($decoded_body === false) {
                // Decoding might fail for base64 if data is corrupt
                 $imap_error = "Failed to decode attachment part (Encoding: $encoding).";
                 $decoded_body = $body; // Send raw data as fallback? Might be undesirable. Or just error out.
                 // For this implementation, we will error out if decoding fails explicitly.
                 $imap_error = "Failed to decode attachment part (Encoding: $encoding).";

            } else {
                 ob_end_clean(); // Clear any potential buffer content before sending file

                 // --- Send Headers and Data ---
                 // Try to determine MIME type (optional, requires fileinfo extension)
                 $mime_type = 'application/octet-stream'; // Default
                 if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    if ($finfo) {
                        $determined_mime = finfo_buffer($finfo, $decoded_body);
                        if($determined_mime) $mime_type = $determined_mime;
                        finfo_close($finfo);
                    }
                 }

                 header('Content-Type: ' . $mime_type);
                 header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
                 header('Content-Transfer-Encoding: binary');
                 header('Expires: 0');
                 header('Cache-Control: must-revalidate');
                 header('Pragma: public');
                 // Use actual decoded size for Content-Length
                 header('Content-Length: ' . strlen($decoded_body));

                 echo $decoded_body;
                 @imap_close($inbox);
                 exit(); // Success!
            }
        }
    }
    if ($inbox) @imap_close($inbox);
} catch (Exception $e) {
    error_log("IMAP Exception downloading attachment for $email, msgno $msgno, part $part_path: " . $e->getMessage());
    $imap_error = 'An application error occurred during download.';
    if ($inbox) @imap_close($inbox);
}

// --- Error Handling ---
ob_end_clean(); // Clean buffer before error output
http_response_code(500); // Internal Server Error
header('Content-Type: application/json');
echo json_encode(['error' => $imap_error ?: 'An unknown error occurred while fetching the attachment.']);
exit();
?>