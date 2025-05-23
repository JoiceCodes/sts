<?php
require_once '../config/database.php'; 


if (isset($_POST['serial_number'])) {
    $serialNumber = $_POST['serial_number'];

    $serialNumber = mysqli_real_escape_string($connection, $serialNumber);

    $sql = "SELECT product_group,
                   product_type, product_version,
                   license_type, created_at
            FROM products
            WHERE serial_number = '$serialNumber'";

    $result = $connection->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(null);
    }
} else {
    echo json_encode(null);
}

$connection->close();
?>