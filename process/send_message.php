<?php
session_start();
require_once "../config/database.php";

$data = json_decode(file_get_contents('php://input'), true);
$caseNumber = $data['case_number'];

$receiver = isset($data["case_owner"]) ? $data["case_owner"] : $data["contact_name"]; 
// $caseOwner = $data["case_owner"];
$message = $data['message'];

$query = "INSERT INTO chats (case_number, sender, receiver, message) VALUES (?, ?, ?, ?)";
$stmt = $connection->prepare($query);
$stmt->bind_param("ssss", $caseNumber, $_SESSION['user_full_name'], $receiver, $message);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
?>