<?php
session_start();
require_once "../config/database.php"; // Adjust path as needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $query = "SELECT case_number FROM cases ORDER BY id DESC LIMIT 1";
    $result = $connection->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_case_number = intval($row["case_number"]); // Convert to integer
        $case_number = str_pad($last_case_number + 1, 8, "0", STR_PAD_LEFT); // Ensure 8 digits
    } else {
        $case_number = "00000001"; // Start from 00000001 if no cases exist
    }

    // Get form data
    // $case_number = trim($_POST["case_number"]);
    $type = trim($_POST["type"]);
    $subject = trim($_POST["subject"]);
    $severity = trim($_POST["severity"]);
    // $serial_number = trim($_POST["serial_number"]);
    $product_group = trim($_POST["product_group"]);
    $product = trim($_POST["product_name"]);
    $case_owner = $_SESSION["user_id"];
    $company = trim($_POST["company"]);
    $product_version = trim($_POST["product_version"]);

    // File upload settings
    $upload_dir = "../uploads/"; // Ensure this directory exists and is writable
    $allowed_types = ["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "mp4", "avi", "mov"];
    $attachment_name = "";

    if (!empty($_FILES["attachment"]["name"])) {
        $file_name = $_FILES["attachment"]["name"];
        $file_tmp = $_FILES["attachment"]["tmp_name"];
        $file_size = $_FILES["attachment"]["size"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($file_ext, $allowed_types)) {
            die("Error: Invalid file type. Allowed types: " . implode(", ", $allowed_types));
        }

        // Set unique file name
        $attachment_name = time() . "_" . basename($file_name);
        $upload_path = $upload_dir . $attachment_name;

        // Move file to uploads directory
        if (!move_uploaded_file($file_tmp, $upload_path)) {
            die("Error: Failed to upload file.");
        }
    }

    // Insert into cases table
    // $query = "INSERT INTO cases (case_number, type, subject, severity, serial_number, product_group, product, attachment)
    //           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $query = "INSERT INTO cases (case_number, type, subject, severity, product_group, product, company, product_version, case_owner, attachment)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


    if ($stmt = $connection->prepare($query)) {
        $stmt->bind_param("ssssssssss", $case_number, $type, $subject, $severity, $product_group, $product, $company, $product_version, $case_owner, $attachment_name);

        if ($stmt->execute()) {
            echo "Success: Case added!";
        } else {
            echo "Error: " . $stmt->error;
        }

        header("Location: ../user/my_cases.php?success=1");
        exit();
    } else {
        echo "Error: " . $connection->error;
    }

    $connection->close();
} else {
    echo "Invalid request.";
}
