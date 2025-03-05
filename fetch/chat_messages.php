<?php
require_once "../config/database.php";

$caseId = $_GET['case_number'];
$query = "SELECT * FROM chats WHERE case_number = ? ORDER BY created_at ASC";
$stmt = $connection->prepare($query);
$stmt->bind_param("s", $caseId);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];

while ($row = $result->fetch_assoc()) {
    $row["created_at"] = date("h:i a", strtotime($row["created_at"]));
    $messages[] = $row;
}

echo json_encode($messages);
?>