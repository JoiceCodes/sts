<?php
// process_transfer_case.php
header('Content-Type: application/json');
session_start();
require_once '../config/database2.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (!isset($pdo) || !($pdo instanceof PDO)) {
    error_log("process_transfer_case.php: Database connection object (\$pdo) is not a valid PDO instance.");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Database configuration problem.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['case_id']) || !isset($data['engineer_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input: case_id and engineer_id are required.']);
    exit;
}

$caseId = filter_var($data['case_id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$newEngineerId = filter_var($data['engineer_id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

if ($caseId === false || $newEngineerId === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid Case ID or Engineer ID provided (must be positive integers).']);
    exit;
}

$newStatus = "Waiting in Progress";
$newEngineerName = null;

try {
    $pdo->beginTransaction();

    $sql = "UPDATE cases SET user_id = :new_user_id, case_status = :new_status, last_modified = NOW() WHERE id = :case_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':new_user_id', $newEngineerId, PDO::PARAM_INT);
    $stmt->bindParam(':new_status', $newStatus, PDO::PARAM_STR);
    $stmt->bindParam(':case_id', $caseId, PDO::PARAM_INT);
    $updateSuccess = $stmt->execute();

    if ($updateSuccess && $stmt->rowCount() > 0) {

        // Fetch the updated timestamp
        $tsStmt = $pdo->prepare("SELECT DATE_FORMAT(last_modified, '%Y-%m-%d %H:%i:%s') as formatted_last_modified FROM cases WHERE id = :case_id");
        $tsStmt->bindParam(':case_id', $caseId, PDO::PARAM_INT);
        $tsStmt->execute();
        $updatedTimestamp = $tsStmt->fetchColumn();

        // Fetch the name of the newly assigned engineer
        $nameStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = :user_id");
        $nameStmt->bindParam(':user_id', $newEngineerId, PDO::PARAM_INT);
        $nameStmt->execute();
        $newEngineerName = $nameStmt->fetchColumn();

        // Fetch the original case owner's ID
        $originalOwnerStmt = $pdo->prepare("SELECT case_owner FROM cases WHERE id = :case_id");
        $originalOwnerStmt->bindParam(':case_id', $caseId, PDO::PARAM_INT);
        $originalOwnerStmt->execute();
        $originalOwnerId = $originalOwnerStmt->fetchColumn();

        // Fetch the original case owner's details
        $originalOwnerDetailsStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = :user_id");
        $originalOwnerDetailsStmt->bindParam(':user_id', $originalOwnerId, PDO::PARAM_INT);
        $originalOwnerDetailsStmt->execute();
        $originalOwnerDetails = $originalOwnerDetailsStmt->fetch(PDO::FETCH_ASSOC);

        // Fetch the new engineer's email
        $newOwnerEmailStmt = $pdo->prepare("SELECT email FROM users WHERE id = :user_id");
        $newOwnerEmailStmt->bindParam(':user_id', $newEngineerId, PDO::PARAM_INT);
        $newOwnerEmailStmt->execute();
        $newOwnerEmail = $newOwnerEmailStmt->fetchColumn();

        // Fetch the case number and brief description
        $caseDetailsStmt = $pdo->prepare("SELECT case_number, subject FROM cases WHERE id = :case_id");
        $caseDetailsStmt->bindParam(':case_id', $caseId, PDO::PARAM_INT);
        $caseDetailsStmt->execute();
        $caseDetails = $caseDetailsStmt->fetch(PDO::FETCH_ASSOC);

        $pdo->commit();

        // --- Prepare Email and Notification Data ---
        $emailSubject = "Case: " . $caseDetails['case_number'] . " Transferred.";

        // Enhanced Email Body (HTML)
        $emailBody = "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>$emailSubject</title>
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
                </style>
            </head>
            <body>
                <div class='container'>
                    <h1>$emailSubject</h1>
                    <p>Dear <span class='highlight'>" . htmlspecialchars($originalOwnerDetails['full_name']) . "</span>,</p>
                    <p>We would like to inform you that your case (<span class='highlight'>" . htmlspecialchars($caseDetails['case_number']) . "</span>) regarding <span class='highlight'>" . htmlspecialchars($caseDetails['subject']) . "</span> has been transferred to a different support agent/team for further assistance.</p>
                    <p>The new agent handling your case is:</p>
                    <p>Agent Name: <span class='highlight'>" . htmlspecialchars($newEngineerName) . "</span></p>
                    <p>Contact: <span class='highlight'>" . htmlspecialchars($newOwnerEmail) . "</span></p>
                    <p>Please rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.</p>
                    <p>If you have any further questions or concerns, feel free to reach out.</p>
                    <p>Thank you for your patience and cooperation.</p>
                    <p>Best regards,<br>i-Secure Networks and Business Solutions Inc.</p>
                </div>
            </body>
            </html>
        ";

        // Prepare plain text for database storage
        $plainMessage = "Dear " . htmlspecialchars($originalOwnerDetails['full_name']) . ",\n\n"
            . "We would like to inform you that your your case (" . htmlspecialchars($caseDetails['case_number']) . ") regarding " . htmlspecialchars($caseDetails['subject']) . " has been transferred to a different support agent/team for further assistance.\n\n"
            . "The new agent handling your case is:\n"
            . "Agent Name: " . htmlspecialchars($newEngineerName) . "\n"
            . "Contact: " . htmlspecialchars($newOwnerEmail) . "\n\n"
            . "Please rest assured that we are actively working on resolving your issue. You will be notified of any updates or progress regarding your ticket.\n\n"
            . "If you have any further questions or concerns, feel free to reach out.\n\n"
            . "Thank you for your patience and cooperation.\n\n"
            . "Best regards,\ni-Secure Networks and Business Solutions Inc.";

        // --- Send Email ---
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF; // Enable verbose debug output
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'joicebarandon31@gmail.com';
            $mail->Password = 'gmbviduachzzyazu';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('joicebarandon31@gmail.com', 'Support Team');
            $mail->addAddress($originalOwnerDetails['email'], $originalOwnerDetails['full_name']);
            $mail->addAddress($newOwnerEmail, $newEngineerName); // Add new engineer

            $mail->isHTML(true);
            $mail->Subject = $emailSubject;
            $mail->Body = $emailBody;
            $mail->AltBody = $plainMessage; // Set plain text version

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }

        // --- Store Notification ---
        $insertNotification = $pdo->prepare("INSERT INTO notifications (case_id, recipient_username, recipient_email, message_subject, message_body) VALUES (?, ?, ?, ?, ?)");
        $insertNotification->bindParam(1, $caseDetails['case_number'], PDO::PARAM_STR); // Use case_number
        $insertNotification->bindParam(2, $originalOwnerDetails['full_name'], PDO::PARAM_STR);
        $insertNotification->bindParam(3, $originalOwnerDetails['email'], PDO::PARAM_STR);
        $insertNotification->bindParam(4, $emailSubject, PDO::PARAM_STR);
        $insertNotification->bindParam(5, $plainMessage, PDO::PARAM_STR); // Store plain text
        $insertNotification->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Case transferred successfully to ' . ($newEngineerName ?: 'Engineer ID ' . $newEngineerId) . '.',
            'last_modified' => $updatedTimestamp ?: date('Y-m-d H:i:s'),
            'new_status' => $newStatus,
            'new_owner_name' => $newEngineerName
        ]);
    } else {
        $pdo->rollBack();
        if ($updateSuccess && $stmt->rowCount() == 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Case not found (ID: ' . $caseId . ') or already has the requested owner/status.']);
        } else {
            error_log("Failed to execute case transfer update for case ID $caseId. PDO ErrorInfo: " . implode(" | ", $stmt->errorInfo()));
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update case in database.']);
        }
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error transferring case (PDO): " . $e->getMessage() . " for case ID $caseId");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred during transfer.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Unexpected error transferring case: " . $e->getMessage() . " for case ID $caseId");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred during transfer.']);
}
