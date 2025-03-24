<?php
session_start();
require_once "../config/database.php";  // Database connection

switch ($_SESSION["user_role"]) {
    case "Technical Engineer":
        $folder = "technical_engineer";
        break;
    case "Technical Head":
        $folder = "technical_head";
        break;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the form data
    $first_name = $_POST['first_name'] ?? '';
    // $middle_name = $_POST['middle_name'] ?? '';
    $middle_name = !empty($_POST["middle_name"]) ? " " . $_POST["middle_name"] . " " : " "; 
    $last_name = $_POST['last_name'] ?? '';
    $suffix = !empty($_POST["suffix"]) ? ", " . $_POST["suffix"] : "";
    // $suffix = $_POST['suffix'] ?? '';

    $full_name = $first_name . $middle_name . $last_name . $suffix;
    $email = $_POST['email'] ?? '';
    $role = "User";
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $repeat_password = $_POST['repeat_password'] ?? '';

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password) || empty($repeat_password)) {
        // echo "All fields are required!";
        // exit;
        header("Location: ../$folder/users.php?empty=1");
        exit;
    }

    // Check if passwords match
    if ($password !== $repeat_password) {
        // echo "Passwords do not match!";
        // exit;
        header("Location: ../$folder/users.php?no_match=1");
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the SQL query to insert the new engineer into the database
    $sql = "INSERT INTO users (full_name, email, username, password, role)
            VALUES (?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $connection->prepare($sql)) {

        // Bind the parameters to the SQL query
        $stmt->bind_param("sssss", $full_name, $email, $username, $hashed_password, $role);

        // Execute the statement
        if ($stmt->execute()) {
            // echo "Engineer added successfully!";
            header("Location: ../$folder/users.php?add=1");
            exit;    
            // Optionally, you can redirect after a successful submission:
            // header("Location: success.php");
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        echo "Error preparing the query: " . $connection->error;
    }
}
?>
