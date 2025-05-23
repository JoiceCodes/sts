<?php
require_once '../config/database.php';

if (isset($_POST['serial_number'])) {
    $serialNumber = $_POST['serial_number'];

    // Sanitize the input to prevent SQL injection
    $serialNumber = mysqli_real_escape_string($conn, $serialNumber);

    $sql = "SELECT company, product_group, product_name, product_category,
                   product_type, product_version, supported_platforms,
                   license_type, created_at
            FROM products
            WHERE serial_number = '$serialNumber'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the product details
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        // No product found with the given serial number
        echo json_encode(null);
    }
} else {
    // If serial_number is not set in the POST request
    echo json_encode(null);
}

$conn->close();
?>