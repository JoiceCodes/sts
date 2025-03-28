<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // --- Generate Case Number ---
    $query = "SELECT case_number FROM cases ORDER BY id DESC LIMIT 1";
    $result = $connection->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_case_number = intval($row["case_number"]);
        $case_number = str_pad($last_case_number + 1, 8, "0", STR_PAD_LEFT);
    } else {
        $case_number = "00000001";
    }

    // --- Sanitize and Retrieve Data ---
    $type = mysqli_real_escape_string($connection, trim($_POST["type"]));
    $subject = mysqli_real_escape_string($connection, trim($_POST["subject"]));
    $severity = mysqli_real_escape_string($connection, trim($_POST["severity"]));
    $product_group = mysqli_real_escape_string($connection, trim($_POST["product_group"]));
    $product = mysqli_real_escape_string($connection, trim($_POST["product_name"]));
    $case_owner = $_SESSION["user_id"];
    $company = mysqli_real_escape_string($connection, trim($_POST["company"]));
    $product_version = mysqli_real_escape_string($connection, trim($_POST["product_version"]));

    // --- File Upload Handling ---
    $upload_dir = "../uploads/";
    $allowed_types = ["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "mp4", "avi", "mov"];
    $max_file_size = 10 * 1024 * 1024;
    $attachment_name = "";

    if (!empty($_FILES["attachment"]["name"])) {
        $file_name = $_FILES["attachment"]["name"];
        $file_tmp = $_FILES["attachment"]["tmp_name"];
        $file_size = $_FILES["attachment"]["size"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            die("Error: Invalid file type. Allowed types: " . implode(", ", $allowed_types));
        }

        if ($file_size > $max_file_size) {
            die("Error: File size exceeds 10MB limit.");
        }

        $attachment_name = time() . "_" . basename($file_name);
        $upload_path = $upload_dir . $attachment_name;

        if (!move_uploaded_file($file_tmp, $upload_path)) {
            die("Error: Failed to upload file.");
        }
    }

    // --- Insert Case into Database ---
    $query = "INSERT INTO cases (case_number, type, subject, severity, product_group, product, company, product_version, case_owner, attachment) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $connection->prepare($query)) {
        $stmt->bind_param("ssssssssss", $case_number, $type, $subject, $severity, $product_group, $product, $company, $product_version, $case_owner, $attachment_name);

        if ($stmt->execute()) {
            // --- Fetch Case Owner Details ---
            $query_user = "SELECT full_name, email FROM users WHERE id = ?";
            if ($stmt_user = $connection->prepare($query_user)) {
                $stmt_user->bind_param("s", $case_owner);
                $stmt_user->execute();
                $result_user = $stmt_user->get_result();

                if ($row_user = $result_user->fetch_assoc()) {
                    $case_owner_name = $row_user["full_name"];
                    $case_owner_email = $row_user["email"];

                    // --- Prepare Plain Text Message for Database ---
                    $plainMessage = "Dear " . htmlspecialchars($case_owner_name) . ",\n\n"
                        . "Your issue has been successfully logged as case #" . htmlspecialchars($case_number) . ".\n\n"
                        . "Please Note: Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.\n\n"
                        . "Thank you,\nTechnical Support Team\n\n";

                    // --- Enhanced Email Body (HTML) ---
                    $emailBody = "
                        <!DOCTYPE html>
                        <html lang='en'>
                        <head>
                            <meta charset='UTF-8'>
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                            <title>Case #$case_number Created</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    line-height: 1.6;
                                    color: #333;
                                    background-color: #f4f4f4;
                                    margin: 0;
                                    padding: 0;
                                }
                                .container {
                                    max-width: 600px;
                                    margin: 20px auto;
                                    background-color: #fff;
                                    padding: 20px;
                                    border-radius: 5px;
                                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                }
                                h1 {
                                    color: #0056b3;
                                    border-bottom: 2px solid #0056b3;
                                    padding-bottom: 10px;
                                }
                                p {
                                    margin-bottom: 10px;
                                }
                                .highlight {
                                    font-weight: bold;
                                    color: #0056b3;
                                }
                                .note {
                                    font-size: 0.9em;
                                    color: #777;
                                    font-style: italic;
                                }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <h1>Case #$case_number Created</h1>
                                <p>Dear <span class='highlight'>" . htmlspecialchars($case_owner_name) . "</span>,</p>
                                <p>Your issue has been successfully logged as case #<span class='highlight'>" . htmlspecialchars($case_number) . "</span>.</p>
                                <p class='note'>
                                    <b>Please Note:</b> Customers needing immediate assistance on a Severity issue opened outside of normal business hours must contact us by phone.
                                </p>
                                <p>Thank you.</p>
                                <p><i>Please do not reply to this email. To update your case, please use the support portal.</i></p>
                            </div>
                        </body>
                        </html>
                        ";

                    // --- Send Email Notification ---
                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'joicebarandon31@gmail.com';
                        $mail->Password = 'gmbviduachzzyazu';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('joicebarandon31@gmail.com', 'Support Team');
                        $mail->addAddress($case_owner_email, $case_owner_name);

                        $mail->Subject = "New Case #$case_number Created";
                        $mail->isHTML(true);
                        $mail->Body = $emailBody;
                        $mail->send();

                        // --- Store Chat Message ---
                        $system_name = "System";
                        $message = $plainMessage; // Use the plain text message
                        $sendMessage = mysqli_prepare($connection, "INSERT INTO chats (case_number, sender, receiver, message) VALUES (?, ?, ?, ?)");
                        mysqli_stmt_bind_param($sendMessage, "ssss", $case_number, $system_name, $case_owner_name, $message);
                        mysqli_stmt_execute($sendMessage);

                        // --- Store Notification ---
                        $notificationSubject = "New Case #$case_number Created";
                        $notificationMessage = $plainMessage; // Use the plain text message
                        $insertNotification = mysqli_prepare($connection, "INSERT INTO notifications (case_id, recipient_username, recipient_email, message_subject, message_body) VALUES (?, ?, ?, ?, ?)");
                        mysqli_stmt_bind_param($insertNotification, "issss", $case_number, $case_owner_name, $case_owner_email, $notificationSubject, $notificationMessage);
                        mysqli_stmt_execute($insertNotification);

                    } catch (Exception $e) {
                        echo "Error: Could not send email. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            }
        } else {
            echo "Error: " . $stmt->error;
        }

        header("Location: ../user/my_cases.php?success=1");
        exit();
    } else {
        echo "Error: " . $connection->error;
    }

    $connection->close();
}
?>