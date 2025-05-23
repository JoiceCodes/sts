<?php
// Location: Manila, Metro Manila, Philippines
// Current Date/Time: Friday, April 11, 2025 at 8:00:26 AM PST
session_start(); // Optional: If you need session data/authentication

// --- STEP 1: INCLUDE DATABASE CONFIG ---
require_once "../config/database.php"; // Adjust path as needed

// --- STEP 2: INCLUDE COMPOSER AUTOLOADER ---
// CRITICAL: Adjust this path based on where export_handler.php is located
// relative to the vendor directory in your project root (sts/).
$vendorPath = __DIR__ . '/../vendor/autoload.php'; // Assumes export_handler.php is in a subfolder like 'technical_head'

if (file_exists($vendorPath)) {
    require_once $vendorPath;
} else {
    // If Excel is requested, Composer autoload is mandatory
    if (isset($_GET['format']) && $_GET['format'] == 'excel') {
         die("FATAL ERROR: Composer autoload file not found at expected path: " . htmlspecialchars($vendorPath) . ". Please check the path and ensure 'composer install' was run.");
    }
    // Allow CSV to proceed without Composer for now (though PhpSpreadsheet's CSV writer is used below)
    // To use PHP's native fputcsv for CSV without Composer, the code structure would need significant changes.
}

// --- STEP 3: Check if PhpSpreadsheet classes exist (needed for BOTH formats with current code) ---
// These 'use' statements are best practice but don't cause the 'class not found' if autoloading works.
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

// Check if Spreadsheet class is loaded (verifies autoloading worked)
if (!class_exists(Spreadsheet::class)) {
     die("Error: PhpSpreadsheet class not found even after including autoloader. Ensure the library is installed via Composer and the path is correct.");
}

// --- STEP 4: Get parameters & Validate ---
$format = $_GET['format'] ?? 'csv';
$chartType = $_GET['chart'] ?? '';
$timeFilter = $_GET['timeFilter'] ?? 'monthly';
$statusFilter = $_GET['statusFilter'] ?? 'all';
$limitCompanies = 10;
$limitEngineers = 10;

if (!in_array($format, ['csv', 'excel']) || empty($chartType)) { die("Invalid parameters."); }
if (!isset($connection)) { die("Database connection error."); }

// --- STEP 5: Prepare data based on chart type ---
$data = [];
$headers = [];
$filename = preg_replace('/[^A-Za-z0-9_]/', '', $chartType) . '_export_' . date('Ymd'); // Sanitize filename

// Prepare checks for required tables for specific charts
$check_rating_table_sql = "SHOW TABLES LIKE 'engineer_ratings'";
$rating_table_exists_result = mysqli_query($connection, $check_rating_table_sql);
$rating_table_exists = ($rating_table_exists_result && mysqli_num_rows($rating_table_exists_result) > 0);
$check_users_table_sql = "SHOW TABLES LIKE 'users'";
$users_table_exists_result = mysqli_query($connection, $check_users_table_sql);
$users_table_exists = ($users_table_exists_result && mysqli_num_rows($users_table_exists_result) > 0);

switch ($chartType) {
    case 'casesStats':
        // ****** REQUIRES IMPLEMENTATION based on line_chart_data_technical.php logic ******
        $headers = ['Period', 'Case Count (' . ucfirst(htmlspecialchars($statusFilter)) . ')'];
        $data = [['Implement fetch logic here', 'based on filters']]; // Placeholder
        $filename .= '_' . preg_replace('/[^A-Za-z0-9]/', '_', $timeFilter) . '_' . preg_replace('/[^A-Za-z0-9]/', '_', $statusFilter);
        break;

    case 'casesRatio':
         $sql_pie = "SELECT SUM(CASE WHEN case_status = 'New' THEN 1 ELSE 0 END) AS new_cases, SUM(CASE WHEN case_status = 'Waiting in Progress' AND reopen = 0 THEN 1 ELSE 0 END) AS ongoing_cases, SUM(CASE WHEN case_status = 'Solved' THEN 1 ELSE 0 END) AS solved_cases, SUM(CASE WHEN case_status = 'Waiting in Progress' AND reopen > 0 THEN 1 ELSE 0 END) AS reopened_cases FROM cases";
         $result_pie = mysqli_query($connection, $sql_pie);
         if($result_pie && $row = mysqli_fetch_assoc($result_pie)) {
             $total = ($row['new_cases'] ?? 0) + ($row['ongoing_cases'] ?? 0) + ($row['solved_cases'] ?? 0) + ($row['reopened_cases'] ?? 0);
             $headers = ['Status', 'Count', 'Percentage'];
             $data = [ /* ... data array ... */ ]; // Populate data array as before
         } else { $headers = ['Status', 'Count', 'Percentage']; error_log("Error fetching cases ratio data: " . mysqli_error($connection));}
        break;

    case 'engineerRatings':
        if ($rating_table_exists && $users_table_exists) {
            $headers = ['Engineer', 'Average Rating'];
            $sql = "SELECT u.full_name, ROUND(AVG(er.rating), 2) as avg_rating FROM engineer_ratings er JOIN users u ON er.engineer_id = u.id WHERE u.role = 'Engineer' GROUP BY er.engineer_id, u.full_name ORDER BY avg_rating DESC"; // Export all engineers?
            $result = mysqli_query($connection, $sql);
            if ($result) { while ($row = mysqli_fetch_assoc($result)) { $data[] = [$row['full_name'], $row['avg_rating']]; } } else { error_log("Error exporting engineer ratings: " . mysqli_error($connection)); }
        } else { $headers = ['Error']; $data[] = ["Required table(s) missing"]; }
        break;

    case 'topCompanies':
         $headers = ['Company', 'Case Count'];
         $sql = "SELECT company, COUNT(*) as case_count FROM cases WHERE company IS NOT NULL AND company != '' GROUP BY company ORDER BY case_count DESC LIMIT ?"; // Kept limit for export consistency
         $stmt = mysqli_prepare($connection, $sql);
         if ($stmt) { mysqli_stmt_bind_param($stmt, "i", $limitCompanies); mysqli_stmt_execute($stmt); $result = mysqli_stmt_get_result($stmt); if($result){ while ($row = mysqli_fetch_assoc($result)) { $data[] = [$row['company'], $row['case_count']]; } } else { error_log("Error exporting top companies: " . mysqli_error($connection)); } mysqli_stmt_close($stmt); }
         else { error_log("Error preparing top companies export statement: " . mysqli_error($connection)); $headers = ['Error']; $data[] = ["Failed to prepare query"]; }
        break;

    case 'topOwners':
         $headers = ['Case Owner Identifier', 'Solved Cases'];
         $sql = "SELECT c.case_owner, COUNT(c.id) AS solved_count FROM cases c WHERE c.case_status = 'Solved' AND c.case_owner IS NOT NULL AND c.case_owner != '' AND c.case_owner != '0' GROUP BY c.case_owner ORDER BY solved_count DESC LIMIT ?"; // Kept limit
         $stmt = mysqli_prepare($connection, $sql);
         if ($stmt) { mysqli_stmt_bind_param($stmt, "i", $limitEngineers); mysqli_stmt_execute($stmt); $result = mysqli_stmt_get_result($stmt); if($result){ while ($row = mysqli_fetch_assoc($result)) { $data[] = [htmlspecialchars($row['case_owner']), $row['solved_count']]; } } else { error_log("Error exporting top owners: " . mysqli_error($connection)); } mysqli_stmt_close($stmt); }
         else { error_log("Error preparing top owners export statement: " . mysqli_error($connection)); $headers = ['Error']; $data[] = ["Failed to prepare query"]; }
        break;

    case 'lowestRatings':
        if ($rating_table_exists && $users_table_exists) {
            $headers = ['Engineer', 'Average Rating (<=3.0)'];
            $sql = "SELECT u.full_name, ROUND(AVG(er.rating), 2) as avg_rating FROM engineer_ratings er JOIN users u ON er.engineer_id = u.id WHERE u.role = 'Engineer' GROUP BY er.engineer_id, u.full_name HAVING avg_rating <= 3.0 ORDER BY avg_rating ASC";
            $result = mysqli_query($connection, $sql);
            if ($result) { while ($row = mysqli_fetch_assoc($result)) { $data[] = [$row['full_name'], $row['avg_rating']]; } } else { error_log("Error exporting lowest rated engineers: " . mysqli_error($connection)); }
        } else { $headers = ['Error']; $data[] = ["Required table(s) missing"]; }
        break;

    default:
        die("Invalid chart type specified.");
}

// --- STEP 6: Generate File using PhpSpreadsheet ---
if (empty($headers)) { die("Error: Could not determine headers for export type '{$chartType}'."); }

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $safeTitle = substr(preg_replace('/[^A-Za-z0-9_ ]/', '', $chartType), 0, 31);
    $sheet->setTitle($safeTitle ?: 'Export');

    $sheet->fromArray($headers, NULL, 'A1');
    $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . '1')->getFont()->setBold(true);

    if (!empty($data)) { $sheet->fromArray($data, NULL, 'A2'); }
    else { $sheet->setCellValue('A2', 'No data available for this export.'); }

    foreach (range('A', $sheet->getHighestDataColumn()) as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

    // --- Output based on format ---
    if ($format == 'excel') {
        if (!class_exists(Xlsx::class)) { die("Error: PhpSpreadsheet Xlsx Writer class not found."); }
        $filename .= '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

    } else { // CSV
         if (!class_exists(Csv::class)) { die("Error: PhpSpreadsheet Csv Writer class not found."); }
        $filename .= '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->save('php://output');
    }

} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
     error_log("PhpSpreadsheet Exception: " . $e->getMessage()); die("Error generating export file. Please check server logs.");
} catch (\Exception $e) {
     error_log("General Export Error: " . $e->getMessage()); die("An unexpected error occurred during export.");
}

exit;
?>