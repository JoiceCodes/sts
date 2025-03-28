<?php
// database.php (Using PDO)
date_default_timezone_set("Asia/Manila");

$host = "localhost";
$db   = "sts_db"; // Renamed for clarity (database name)
$user = "root";
$pass = "";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Enable exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Set default fetch mode
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     // $pdo variable is now created and holds the PDO connection object
} catch (\PDOException $e) {
     // Log the error securely in a real application
     error_log('Database Connection Error: ' . $e->getMessage());

     // Stop script execution and provide a generic error for the client
     // Avoid echoing detailed errors ($e->getMessage()) in production
     http_response_code(500); // Internal Server Error
     die(json_encode(['error' => 'Database connection failed. Please try again later.']));
}
?>