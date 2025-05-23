<?php
require_once "../config/database.php"; // Adjust path to your database con$connectionection file

header('Content-Type: application/json');

$searchTerm = $_GET['q'] ?? ''; // Get search term from Select2

$data = [];

// Use a prepared statement to prevent SQL injection
$sql = "SELECT id, name FROM companies";
$params = [];
$types = "";

if ($searchTerm) {
    $sql .= " WHERE name LIKE ?";
    $params[] = "%" . $searchTerm . "%";
    $types .= "s";
}

$sql .= " ORDER BY name";

$stmt = $connection->prepare($sql);

if ($searchTerm) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'], // Assuming 'id' is the value you want to submit
            'text' => htmlspecialchars($row['name']) // 'text' is what Select2 displays
        ];
    }
    echo json_encode(['results' => $data]);
} else {
    // Handle database error
    error_log("Database error fetching companies: " . $connection->error);
    echo json_encode(['results' => [], 'error' => 'Database error']);
}

$stmt->close();
$connection->close();
?>