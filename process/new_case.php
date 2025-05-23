<?php
session_start();

// --- IMPORTANT: Ensure user is logged in ---
if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php?error=" . urlencode("Authentication required. Please login."));
    exit;
}

// --- Load Dependencies ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// <<< ADAPT >>> Ensure this path is correct
require '../vendor/autoload.php';
// <<< ADAPT >>> Ensure this path is correct AND that it provides $connection (mysqli object)
require_once "../config/database.php";

// Check if $connection is valid
if (!isset($connection) || $connection->connect_error) {
    error_log("[Case Creation Fatal Error] Database connection failed: " . ($connection->connect_error ?? 'Unknown error'));
    $_SESSION['case_error'] = "A critical error occurred while connecting to the database. Please contact support.";
    header("Location: ../user/new_cases.php"); // Adjust redirect path if needed
    exit();
}


// --- Helper function to get email settings ---
function get_email_settings($connection) {
    // (Code for get_email_settings function as provided before - unchanged)
    $settings = [];
    $query = "SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'";
    if ($stmt = $connection->prepare($query)) {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            $result->free();
        } else {
            error_log("[Case Creation] Failed to execute email settings query: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("[Case Creation] Failed to prepare email settings query: " . $connection->error);
    }
    return $settings;
}


// --- Process only POST requests ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --- Generate Unique Case Number ---
    $case_number = "CASE-" . date("Ymd-His") . "-" . $_SESSION['user_id'];

    // --- Get POST Data (Trim values) ---
    $product_name_from_form = isset($_POST["product_name"]) ? trim($_POST["product_name"]) : '';
    $subject_base           = isset($_POST["subject_base"]) ? trim($_POST["subject_base"]) : '';
    $subject_other          = isset($_POST["subject_other"]) ? trim($_POST["subject_other"]) : '';
    $severity               = isset($_POST["severity"]) ? trim($_POST["severity"]) : '';
    $serial_number          = isset($_POST["serial_number"]) ? trim($_POST["serial_number"]) : '';
    $product_group          = isset($_POST["product_group"]) ? trim($_POST["product_group"]) : '';
    $company                = isset($_POST["company"]) ? trim($_POST["company"]) : '';
    $product_version        = isset($_POST["product_version"]) ? trim($_POST["product_version"]) : '';

    // --- Determine DB values based on form input ---
    $db_type = $product_name_from_form;
    $db_product = $product_name_from_form;

    // Determine final subject
    if ($subject_base === 'Other' && !empty($subject_other)) {
        $db_subject = $subject_other;
    } elseif ($subject_base === 'Other' && empty($subject_other)) {
        $_SESSION['case_error'] = "Error: Please specify the subject when selecting 'Other'.";
        header("Location: ../user/new_cases.php"); exit;
    } elseif (empty($subject_base) || $subject_base === '-- Select Subject --') {
         $_SESSION['case_error'] = "Error: Please select a subject.";
         header("Location: ../user/new_cases.php"); exit;
    } else {
        $db_subject = $subject_base;
    }

    // --- Get Case Submitter (Owner) ID from Session ---
    $db_case_owner_id = $_SESSION["user_id"];

    // --- File Upload Handling ---
    // (Code for file upload handling as provided before - unchanged)
    // Define upload directory using __DIR__ for reliability
    $upload_dir_relative_from_script = "../uploads/case_attachments/";
    $upload_dir_absolute = realpath(__DIR__ . DIRECTORY_SEPARATOR . $upload_dir_relative_from_script);
    $allowed_types = ["jpg", "jpeg", "png", "pdf", "doc", "docx"];
    $max_file_size = 10 * 1024 * 1024; // 10 MB
    $db_attachment_filename = null; // Initialize as NULL

    if (isset($_FILES["attachment"]) && $_FILES["attachment"]["error"] == UPLOAD_ERR_OK && !empty($_FILES["attachment"]["name"])) {
        $file_name = $_FILES["attachment"]["name"];
        $file_tmp = $_FILES["attachment"]["tmp_name"];
        $file_size = $_FILES["attachment"]["size"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Server-side validation
        if (!in_array($file_ext, $allowed_types)) {
            $_SESSION['case_error'] = "Error: Invalid file type. Allowed types: " . implode(", ", $allowed_types);
            header("Location: ../user/new_cases.php"); exit();
        }
        if ($file_size > $max_file_size) {
             $_SESSION['case_error'] = "Error: File size exceeds " . ($max_file_size / 1024 / 1024) . "MB limit.";
             header("Location: ../user/new_cases.php"); exit();
        }
        // Directory Checks and Creation
        if ($upload_dir_absolute === false) {
             $_SESSION['case_error'] = "Error: Server configuration issue (upload path invalid).";
             header("Location: ../user/new_cases.php"); exit();
        }
        if (!is_dir($upload_dir_absolute)) {
            if (!mkdir($upload_dir_absolute, 0755, true)) {
                error_log("[Case Creation Error] Failed to create upload directory: '" . $upload_dir_absolute . "'");
                 $_SESSION['case_error'] = "Error: Server configuration issue prevents file upload (cannot create directory).";
                 header("Location: ../user/new_cases.php"); exit();
            }
        } elseif (!is_writable($upload_dir_absolute)) {
            error_log("[Case Creation Error] Upload directory is not writable: '" . $upload_dir_absolute . "'");
            $_SESSION['case_error'] = "Error: Server configuration issue prevents file upload (directory permissions).";
            header("Location: ../user/new_cases.php"); exit();
        }
        // Create unique filename
        $safe_original_name = preg_replace("/[^a-zA-Z0-9._-]/", "_", basename($file_name));
        $unique_filename = $case_number . "_" . uniqid() . "_" . $safe_original_name;
        $destination_path = $upload_dir_absolute . DIRECTORY_SEPARATOR . $unique_filename;
        // Move the uploaded file
        if (move_uploaded_file($file_tmp, $destination_path)) {
            $db_attachment_filename = $unique_filename;
        } else {
            error_log("[Case Creation Error] Failed to move uploaded file '$file_tmp' to '$destination_path'.");
            $_SESSION['case_error'] = "Error: Failed to save uploaded file due to a server issue.";
            header("Location: ../user/new_cases.php"); exit();
        }
    } elseif (isset($_FILES["attachment"]) && $_FILES["attachment"]["error"] !== UPLOAD_ERR_NO_FILE) {
        // Handle other specific PHP upload errors
        $upload_errors = [ UPLOAD_ERR_INI_SIZE   => "File too large (server limit).", UPLOAD_ERR_FORM_SIZE  => "File too large (form limit).", UPLOAD_ERR_PARTIAL    => "File only partially uploaded.", UPLOAD_ERR_NO_TMP_DIR => "Server missing temporary folder.", UPLOAD_ERR_CANT_WRITE => "Server cannot write file.", UPLOAD_ERR_EXTENSION  => "PHP extension stopped upload."];
        $error_code = $_FILES["attachment"]["error"];
        $error_message = $upload_errors[$error_code] ?? "Unknown upload error (Code: " . $error_code . ")";
        $_SESSION['case_error'] = "Error during file upload: " . $error_message;
        header("Location: ../user/new_cases.php"); exit();
    }
    // --- End File Upload ---

    // --- Set Default Values for DB Columns ---
    $db_case_status = 'New';
    $db_reopen = 0;
    $db_date_accepted = null;
    $db_date_solved = null;
    $current_datetime = date('Y-m-d H:i:s'); // Timestamp for creation/modification

    // --- Prepare and Execute Case Insertion ---
    // V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V
    // --- IMPORTANT CHECK: Does your 'cases' table's 'created_at' column have DEFAULT CURRENT_TIMESTAMP? ---
    // --- If YES: The current INSERT statement below is likely CORRECT (database handles created_at).
    // --- If NO: You MUST modify the statement below to include 'created_at' and bind $current_datetime to it.
    // --- See previous response for HOW TO MODIFY if needed.
    // V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V V

    // <<< ADAPT >>> Ensure column names listed here EXACTLY match your 'cases' table
    $sql_insert_case = "INSERT INTO cases (
                            case_number, type, subject, user_id, product_group, product, product_version,
                            severity, case_status, attachment, case_owner, company, last_modified,
                            datetime_opened, reopen, date_accepted, date_solved
                            -- potentially add 'created_at' here if needed
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // Adjust '?' count if adding created_at

    if ($stmt_insert_case = $connection->prepare($sql_insert_case)) {

        // <<< ADAPT >>> Ensure types (s/i) and order match your SQL columns EXACTLY
        // If you added 'created_at' to SQL, add 's' to types and $current_datetime to variables
        $stmt_insert_case->bind_param(
            "sssisssssissssiis", // Adjust types if adding created_at
            $case_number, $db_type, $db_subject, $db_case_owner_id, $product_group,
            $db_product, $product_version, $severity, $db_case_status, $db_attachment_filename,
            $db_case_owner_id, $company, $current_datetime, $current_datetime, $db_reopen,
            $db_date_accepted, $db_date_solved // Add $current_datetime here if adding created_at
        );

        if ($stmt_insert_case->execute()) {
            // --- Case Inserted Successfully ---
            $inserted_case_id = $connection->insert_id;
            $stmt_insert_case->close();
            error_log("[Case Creation Info] Case $case_number inserted successfully with DB ID $inserted_case_id.");

            // --- Get Submitter Details ---
            // (Code to get $case_owner_name, $case_owner_email as provided before - unchanged)
            $case_owner_name = "Valued Customer"; $case_owner_email = "";
             $query_user = "SELECT full_name, email FROM users WHERE id = ?"; // <<< ADAPT users table/columns if needed
             if ($stmt_user = $connection->prepare($query_user)) {
                 $stmt_user->bind_param("i", $db_case_owner_id);
                 if ($stmt_user->execute()) {
                     $result_user = $stmt_user->get_result();
                     if ($row_user = $result_user->fetch_assoc()) {
                         $case_owner_name = $row_user["full_name"];
                         $case_owner_email = $row_user["email"];
                     }
                     $result_user->free();
                 } $stmt_user->close();
             }

            // --- Send Email Notification to User ---
            // (Code for sending email with PHPMailer as provided before - unchanged, relies on get_email_settings)
             if (!empty($case_owner_email)) {
                $email_settings = get_email_settings($connection);
                if (!empty($email_settings['smtp_host']) && !empty($email_settings['smtp_from_email'])) {
                    // Prepare Email Content
                    $plainMessageUser = "Dear " . htmlspecialchars($case_owner_name) . ",\n\n" . "Your support case has been logged with Case Number: " . htmlspecialchars($case_number) . ".\n\nDetails:\n- Subject: " . htmlspecialchars($db_subject) . "\n- Product: " . htmlspecialchars($db_product) . "\n- Severity: " . htmlspecialchars($severity) . "\n\nThank you,\nSupport Team";
                    $emailTemplatePath = '../email_templates/new_case_user.html'; // <<< ADAPT path if needed
                    $userEmailBody = '';
                     if (file_exists($emailTemplatePath)) {
                         $userEmailBody = file_get_contents($emailTemplatePath);
                         if ($userEmailBody !== false) {
                             $userEmailBody = str_replace(['{{case_number}}', '{{user_name}}', '{{subject}}', '{{product}}', '{{severity}}'], [htmlspecialchars($case_number), htmlspecialchars($case_owner_name), htmlspecialchars($db_subject), htmlspecialchars($db_product), htmlspecialchars($severity)], $userEmailBody);
                         } else { $userEmailBody = "<p>" . nl2br(htmlspecialchars($plainMessageUser)) . "</p>";}
                     } else { $userEmailBody = "<p>" . nl2br(htmlspecialchars($plainMessageUser)) . "</p>";}

                    // Configure and Send
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = $email_settings['smtp_host'];
                        $mail->SMTPAuth = !empty($email_settings['smtp_username']);
                        $mail->Username = $email_settings['smtp_username'] ?? '';
                        $mail->Password = $email_settings['smtp_password'] ?? '';
                        $secure_type = strtolower($email_settings['smtp_secure'] ?? 'tls');
                        if ($secure_type === 'tls') $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        elseif ($secure_type === 'ssl') $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        else $mail->SMTPSecure = false;
                        $mail->Port = (int)($email_settings['smtp_port'] ?? ($secure_type === 'ssl' ? 465 : 587));
                        $mail->setFrom($email_settings['smtp_from_email'], $email_settings['smtp_from_name'] ?? 'Support Team');
                        $mail->addAddress($case_owner_email, $case_owner_name);
                        $mail->isHTML(true);
                        $mail->Subject = "Support Case #" . htmlspecialchars($case_number) . " Created Successfully";
                        $mail->Body    = $userEmailBody;
                        $mail->AltBody = $plainMessageUser;
                        $mail->send();
                    } catch (Exception $e) {
                        error_log("[Case Creation Error] User Email Error for Case $case_number. PHPMailer Error: {$mail->ErrorInfo}");
                    }
                } else { error_log("[Case Creation Warning] Skipping user email for case $case_number. SMTP not configured.");}
            }

            // --- Insert System Chat Message (Optional) ---
            // (Code for inserting system chat message - unchanged)
             try {
                 $system_name = "System";
                 $chatMessage = "Case #" . htmlspecialchars($case_number) . " created by " . htmlspecialchars($case_owner_name) . ". Severity: " . htmlspecialchars($severity) . ".";
                 $sendMessage = $connection->prepare("INSERT INTO chats (case_number, sender, receiver, message) VALUES (?, ?, ?, ?)"); // <<< ADAPT chats table/columns if needed
                 if ($sendMessage) {
                     $chatReceiver = $case_owner_name;
                     $sendMessage->bind_param("ssss", $case_number, $system_name, $chatReceiver, $chatMessage);
                     if (!$sendMessage->execute()) {error_log("[Case Creation Error] Storing chat message: " . $sendMessage->error);}
                     $sendMessage->close();
                 } else {error_log("[Case Creation Error] Preparing chat insert: " . $connection->error);}
             } catch (Exception $e) {error_log("[Case Creation Exception] Chat insert: " . $e->getMessage());}

            // --- Store User Notification in Database (Optional) ---
            // (Code for storing user notification - unchanged)
             try {
                 $notificationSubjectUser = "Case #" . htmlspecialchars($case_number) . " Created";
                 $insertNotificationUser = $connection->prepare("INSERT INTO notifications (case_id, recipient_username, recipient_email, message_subject, message_body) VALUES (?, ?, ?, ?, ?)"); // <<< ADAPT notifications table/columns if needed
                 if ($insertNotificationUser) {
                     $insertNotificationUser->bind_param("issss", $inserted_case_id, $case_owner_name, $case_owner_email, $notificationSubjectUser, $plainMessageUser);
                     if (!$insertNotificationUser->execute()) {error_log("[Case Creation Error] Storing user notification: " . $insertNotificationUser->error);}
                     $insertNotificationUser->close();
                 } else {error_log("[Case Creation Error] Preparing user notification: " . $connection->error);}
             } catch (Exception $e) {error_log("[Case Creation Exception] User notification insert: " . $e->getMessage());}

            // --- Store Admin Notification in Database (Optional) ---
            // (Code for storing admin notification - unchanged)
             try {
                  $adminNotificationSubject = "New Case #" . htmlspecialchars($case_number) . " (" . htmlspecialchars($severity). ") from " . htmlspecialchars($company);
                  $adminNotificationPlainTextBody = "New Support Case Logged\n===================================\nCase Number: " . htmlspecialchars($case_number) . "\nSubmitted By: " . htmlspecialchars($case_owner_name) . " (" . htmlspecialchars($case_owner_email) . ")\nCompany: " . htmlspecialchars($company) . "\nSerial Number: " . htmlspecialchars($serial_number) . "\n\n--- Case Details ---\nSubject: " . htmlspecialchars($db_subject) . "\nProduct: " . htmlspecialchars($db_product) . "\nSeverity: " . htmlspecialchars($severity) . "\nProduct Group: " . htmlspecialchars($product_group) . "\nProduct Version: " . htmlspecialchars($product_version) . "\nAttachment: " . ($db_attachment_filename ? htmlspecialchars($db_attachment_filename) : 'None Uploaded') . "\n\nAdmin dashboard access needed.\n===================================";
                  $insertAdminNotification = $connection->prepare("INSERT INTO admin_notifications (case_number, notification_subject, notification_body) VALUES (?, ?, ?)"); // <<< ADAPT admin_notifications table/columns if needed
                  if ($insertAdminNotification) {
                      $insertAdminNotification->bind_param("sss", $case_number, $adminNotificationSubject, $adminNotificationPlainTextBody);
                      if (!$insertAdminNotification->execute()) {error_log("[Case Creation Error] Storing admin notification: " . $insertAdminNotification->error);}
                      $insertAdminNotification->close();
                  } else {error_log("[Case Creation Error] Preparing admin notification: " . $connection->error);}
             } catch (Exception $e) {error_log("[Case Creation Exception] Admin notification insert: " . $e->getMessage());}


            // --- Redirect User on Success ---
            $_SESSION['case_success'] = "Case #" . htmlspecialchars($case_number) . " created successfully!";
            header("Location: ../user/new_cases.php"); // Adjust redirect path if needed
            exit();

        } else { // --- Handle Case Insertion Execution Failure ---
            $db_error_code = $stmt_insert_case->errno;
            $db_error_msg = $stmt_insert_case->error;
            $stmt_insert_case->close();
            error_log("[Case Creation Error] Error executing case insert: ($db_error_code) $db_error_msg");
            // Clean up uploaded file if needed
            if (!empty($db_attachment_filename) && isset($destination_path) && file_exists($destination_path)) { if (!unlink($destination_path)) {error_log("[Case Creation Warning] Failed cleanup upload: " . $destination_path);}}
             $_SESSION['case_error'] = "Failed to create case due to a database error (Code: $db_error_code).";
             header("Location: ../user/new_cases.php"); exit();
        }
    } else { // --- Handle Statement Preparation Failure ---
        $db_error_code = $connection->errno;
        $db_error_msg = $connection->error;
        error_log("[Case Creation Error] Error preparing case insert statement: ($db_error_code) $db_error_msg");
        $_SESSION['case_error'] = "Failed to prepare case creation due to a system error (Code: $db_error_code).";
        header("Location: ../user/new_cases.php"); exit();
    }

} else { // --- Handle Invalid Request Method (Not POST) ---
     header("Location: ../user/new_cases.php"); exit();
}

// --- Close Database Connection (Might not be reached) ---
if (isset($connection) && $connection instanceof mysqli && !$connection->connect_error) {
    $connection->close();
}
?>