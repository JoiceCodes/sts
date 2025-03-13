<?php
session_start();
require_once "../config/database.php";

$data = json_decode(file_get_contents('php://input'), true);
$caseNumber = $data['case_number'];

$receiverId = isset($data["case_owner"]) ? $data["case_owner"] : $data["user_id"];
$receiverFullName = "System";
// $caseOwner = $data["case_owner"];
$message = $data['message'];

if ($receiverId != null) {
    $getReceiverName = mysqli_prepare($connection, "SELECT * FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($getReceiverName, "i", $receiverId);
    mysqli_stmt_execute($getReceiverName);
    $getReceiverNameResult = mysqli_stmt_get_result($getReceiverName);
    if (mysqli_num_rows($getReceiverNameResult) > 0) {
        $row = mysqli_fetch_assoc($getReceiverNameResult);
        $receiverFullName = $row["full_name"];
    }

    $query = "INSERT INTO chats (case_number, sender, receiver, message) VALUES (?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ssss", $caseNumber, $_SESSION['user_full_name'], $receiverFullName, $message);
    $success = $stmt->execute();
}

echo json_encode(['success' => $success]);
