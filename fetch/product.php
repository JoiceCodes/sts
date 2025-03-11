<?php
header("Content-Type: application/json"); // Ensure JSON output
require_once "../config/database.php"; // Adjust as needed

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["serial_number"])) {
    $serial_number = trim($_POST["serial_number"]);

    $query = "SELECT company, product_version, product_group, product_name FROM products WHERE serial_number = ? LIMIT 1";
    if ($stmt = $connection->prepare($query)) {
        $stmt->bind_param("s", $serial_number);
        $stmt->execute();
        $stmt->bind_result($product_group, $product_name, $company, $product_version);

        if ($stmt->fetch()) {
            echo json_encode([
                "success" => true,
                "product_group" => $product_group,
                "product_name" => $product_name,
                "company" => $company,
                "product_version" => $product_version
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "No matching serial number found"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => $connection->error]);
    }

    $connection->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}
