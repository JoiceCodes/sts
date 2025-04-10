<?php
// reports_table.php

session_start();
$pageTitle = "Reports"; // Default Title
// Determine Current Time based on Philippines timezone for potential use later
date_default_timezone_set('Asia/Manila');
$currentDateTime = date('Y-m-d H:i:s'); // Example: 2025-04-10 14:48:01 based on your context

include("../config/database2.php"); // Ensure this path is correct and sets up $pdo

// --- User Role Check ---
$loggedInUserId = $_SESSION['user_id'] ?? null;
$loggedInUserRole = $_SESSION['user_role'] ?? null;
$isEngineerView = ($loggedInUserRole === 'Engineer' && $loggedInUserId !== null);
// --- End User Role Check ---

// --- Configuration & Constants ---
$ongoingCaseStatus = 'Waiting in Progress';
$solvedStatus = 'Solved';
$reopenThreshold = 0;
$poorPerformanceThreshold = 3.0;
// --- End Configuration ---

// --- Helper Functions (displayStars, formatTimeDiff - unchanged) ---
function displayStars(float $rating, int $maxStars = 5): string
{
    $output = '';
    $rating = max(0, min($rating, $maxStars));
    $roundedRating = round($rating * 2) / 2;
    $fullStars = floor($roundedRating);
    $halfStar = ($roundedRating - $fullStars) >= 0.5;
    $emptyStars = $maxStars - $fullStars - ($halfStar ? 1 : 0);
    for ($i = 0; $i < $fullStars; $i++) {
        $output .= '<i class="fas fa-star text-warning"></i>';
    }
    if ($halfStar) {
        $output .= '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $output .= '<i class="far fa-star text-warning"></i>';
    }
    $output .= ' (' . number_format($rating, 1) . ')';
    return $output;
}
function formatTimeDiff(?string $start_date, ?string $end_date): string
{
    if (empty($start_date) || empty($end_date)) {
        return 'N/A';
    }
    try {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        if ($end < $start) {
            return 'Invalid Dates';
        }
        $diff = $end->diff($start);
        $parts = [];
        if ($diff->days > 0) {
            $parts[] = $diff->days . ' day' . ($diff->days > 1 ? 's' : '');
            if ($diff->h > 0 && $diff->days <= 3) {
                $parts[] = $diff->h . ' hr' . ($diff->h > 1 ? 's' : '');
            }
        } else {
            if ($diff->h > 0) {
                $parts[] = $diff->h . ' hr' . ($diff->h > 1 ? 's' : '');
            }
            if ($diff->i > 0) {
                $parts[] = $diff->i . ' min' . ($diff->i > 1 ? 's' : '');
            }
            if ($diff->s > 0 && empty($parts)) {
                $parts[] = $diff->s . ' sec' . ($diff->s > 1 ? 's' : '');
            }
        }
        return !empty($parts) ? implode(', ', $parts) : 'Less than a minute';
    } catch (Exception $e) {
        error_log("Error formatting time diff: " . $e->getMessage());
        return 'Error';
    }
}
// --- End Helper Functions ---

// --- Initialize Data Arrays ---
// Admin/Head View Data
$engineers = [];
$ongoingCaseCounts = [];
$solvedReopenedCounts = [];
$topCompanies = [];
$topEngineers = [];
$poorPerformers = [];
$displayablePoorPerformers = [];
// Engineer View Data
$engineerCases = [];
$myAvgRating = 0.0;
$mySolvedCount = 0;
$myOngoingCount = 0;
$engineerName = '';
// Chart Data (Admin/Head Only)
$engineerPerformanceData = ['labels' => [], 'data' => []];
$topCompanyData = ['labels' => [], 'data' => []];
$topEngineerData = ['labels' => [], 'data' => []];
$lowestRatedData = ['labels' => [], 'data' => []];
// Error Flags
$engineerRatingsError = null;
$caseCountError = null;
$solvedCountError = null;
$topCompanyError = null;
$topEngineerError = null;
$poorPerformerError = null;
$engineerCasesError = null;
$myStatsError = null;
$showPoorPerformersCard = false;
// --- End Initialize Data Arrays ---

// --- Data Fetching (Conditional based on User Role) ---
if ($isEngineerView && $loggedInUserId) {
    // ===============================
    // ===== ENGINEER-SPECIFIC VIEW =====
    // ===============================
    $pageTitle = "My Performance Report";
    try {
        $sql_name = "SELECT full_name FROM users WHERE id = :engineer_id LIMIT 1";
        $stmt_name = $pdo->prepare($sql_name);
        $stmt_name->bindParam(':engineer_id', $loggedInUserId, PDO::PARAM_INT);
        $stmt_name->execute();
        $engineerName = $stmt_name->fetchColumn();
        if ($engineerName) {
            $pageTitle = "My Performance Report";
        }
        $sql_my_rating = "SELECT COALESCE(AVG(rating), 0) FROM engineer_ratings WHERE engineer_id = :engineer_id";
        $stmt_my_rating = $pdo->prepare($sql_my_rating);
        $stmt_my_rating->bindParam(':engineer_id', $loggedInUserId, PDO::PARAM_INT);
        $stmt_my_rating->execute();
        $myAvgRating = (float)$stmt_my_rating->fetchColumn();
        $sql_my_solved = "SELECT COUNT(*) FROM cases WHERE user_id = :engineer_id AND case_status = :solved_status";
        $stmt_my_solved = $pdo->prepare($sql_my_solved);
        $stmt_my_solved->bindParam(':engineer_id', $loggedInUserId, PDO::PARAM_INT);
        $stmt_my_solved->bindParam(':solved_status', $solvedStatus, PDO::PARAM_STR);
        $stmt_my_solved->execute();
        $mySolvedCount = (int)$stmt_my_solved->fetchColumn();
        $sql_my_ongoing = "SELECT COUNT(*) FROM cases WHERE user_id = :engineer_id AND case_status = :ongoing_status";
        $stmt_my_ongoing = $pdo->prepare($sql_my_ongoing);
        $stmt_my_ongoing->bindParam(':engineer_id', $loggedInUserId, PDO::PARAM_INT);
        $stmt_my_ongoing->bindParam(':ongoing_status', $ongoingCaseStatus, PDO::PARAM_STR);
        $stmt_my_ongoing->execute();
        $myOngoingCount = (int)$stmt_my_ongoing->fetchColumn();
    } catch (\PDOException $e) {
        $myStatsError = "Error fetching your performance stats: " . $e->getMessage();
        error_log($myStatsError . " (User ID: " . $loggedInUserId . ")");
    }
    try {
        $sql_engineer_cases = "SELECT c.id as case_id, c.subject, c.date_accepted, c.date_solved FROM cases c WHERE c.user_id = :engineer_id AND c.case_status = :solved_status AND c.date_accepted IS NOT NULL AND c.date_solved IS NOT NULL ORDER BY c.date_solved DESC";
        $stmt_engineer_cases = $pdo->prepare($sql_engineer_cases);
        $stmt_engineer_cases->bindParam(':engineer_id', $loggedInUserId, PDO::PARAM_INT);
        $stmt_engineer_cases->bindParam(':solved_status', $solvedStatus, PDO::PARAM_STR);
        $stmt_engineer_cases->execute();
        $engineerCases = $stmt_engineer_cases->fetchAll(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        $engineerCasesError = "Error fetching your solved case details: " . $e->getMessage();
        error_log($engineerCasesError . " (User ID: " . $loggedInUserId . ")");
    }
} else {
    // =======================================
    // ===== ADMIN / TECHNICAL HEAD VIEW =====
    // =======================================
    // (This part remains unchanged - fetches aggregate data and prepares aggregate charts)
    $pageTitle = "Engineer Reports Overview";
    try {
        $sql_engineers = "SELECT u.id, u.full_name, COALESCE(AVG(er.rating), 0) AS average_rating FROM users u LEFT JOIN engineer_ratings er ON u.id = er.engineer_id WHERE u.role = 'engineer' GROUP BY u.id, u.full_name ORDER BY u.full_name;";
        $stmt_engineers = $pdo->query($sql_engineers);
        $engineers = $stmt_engineers->fetchAll(PDO::FETCH_ASSOC);
        foreach ($engineers as $eng) {
            $engineerPerformanceData['labels'][] = $eng['full_name'];
            $engineerPerformanceData['data'][] = round((float)$eng['average_rating'], 1);
        }
    } catch (\PDOException $e) {
        $engineerRatingsError = "Error fetching engineer ratings: " . $e->getMessage();
        error_log($engineerRatingsError);
    }
    if (!empty($engineers)) {
        try {
            $sql_ongoing_cases = "SELECT user_id, COUNT(*) FROM cases WHERE user_id IS NOT NULL AND case_status = :ongoing_status GROUP BY user_id;";
            $stmt_ongoing_cases = $pdo->prepare($sql_ongoing_cases);
            $stmt_ongoing_cases->bindParam(':ongoing_status', $ongoingCaseStatus, PDO::PARAM_STR);
            $stmt_ongoing_cases->execute();
            $ongoingCaseCounts = $stmt_ongoing_cases->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\PDOException $e) {
            $caseCountError = "Error fetching ongoing counts: " . $e->getMessage();
            error_log($caseCountError);
        }
        try {
            $sql_solved_table = "SELECT user_id, COUNT(*) FROM cases WHERE user_id IS NOT NULL AND reopen > :reopen_threshold GROUP BY user_id;";
            $stmt_solved_table = $pdo->prepare($sql_solved_table);
            $stmt_solved_table->bindParam(':reopen_threshold', $reopenThreshold, PDO::PARAM_INT);
            $stmt_solved_table->execute();
            $solvedReopenedCounts = $stmt_solved_table->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\PDOException $e) {
            $solvedCountError = "Error fetching reopened counts: " . $e->getMessage();
            error_log($solvedCountError);
        }
    }
    try {
        $sql_top_company = "SELECT company, COUNT(*) as case_count FROM cases WHERE company IS NOT NULL AND company != '' GROUP BY company ORDER BY case_count DESC LIMIT 5;";
        $stmt_top_company = $pdo->query($sql_top_company);
        $topCompanies = $stmt_top_company->fetchAll(PDO::FETCH_ASSOC);
        foreach ($topCompanies as $comp) {
            $topCompanyData['labels'][] = $comp['company'];
            $topCompanyData['data'][] = (int)$comp['case_count'];
        }
    } catch (\PDOException $e) {
        $topCompanyError = "Error fetching top companies: " . $e->getMessage();
        error_log($topCompanyError);
    }
    try {
        $sql_top_engineer = "SELECT u.full_name, COUNT(c.id) as solved_count FROM cases c JOIN users u ON c.user_id = u.id WHERE c.user_id IS NOT NULL AND c.reopen > :reopen_threshold GROUP BY c.user_id, u.full_name ORDER BY solved_count DESC LIMIT 3;";
        $stmt_top_engineer = $pdo->prepare($sql_top_engineer);
        $stmt_top_engineer->bindParam(':reopen_threshold', $reopenThreshold, PDO::PARAM_INT);
        $stmt_top_engineer->execute();
        $topEngineers = $stmt_top_engineer->fetchAll(PDO::FETCH_ASSOC);
        foreach ($topEngineers as $eng) {
            $topEngineerData['labels'][] = $eng['full_name'];
            $topEngineerData['data'][] = (int)$eng['solved_count'];
        }
    } catch (\PDOException $e) {
        $topEngineerError = "Error fetching top engineers: " . $e->getMessage();
        error_log($topEngineerError);
    }
    try {
        $sql_poor_perf = "SELECT u.id, u.full_name, COALESCE(AVG(er.rating), 0) AS average_rating FROM users u LEFT JOIN engineer_ratings er ON u.id = er.engineer_id WHERE u.role = 'engineer' GROUP BY u.id, u.full_name HAVING COUNT(er.id) > 0 ORDER BY average_rating ASC LIMIT 3;";
        $stmt_poor_perf = $pdo->query($sql_poor_perf);
        $poorPerformers = $stmt_poor_perf->fetchAll(PDO::FETCH_ASSOC);
        foreach ($poorPerformers as $performer) {
            if ((float)$performer['average_rating'] <= $poorPerformanceThreshold) {
                $displayablePoorPerformers[] = $performer;
                $lowestRatedData['labels'][] = $performer['full_name'];
                $lowestRatedData['data'][] = round((float)$performer['average_rating'], 1);
            }
        }
        if (!empty($lowestRatedData['labels'])) {
            $showPoorPerformersCard = true;
        }
    } catch (\PDOException $e) {
        $poorPerformerError = "Error fetching poor performers: " . $e->getMessage();
        error_log($poorPerformerError);
    }
}
// --- End Data Fetching ---

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Support System Reporting Dashboard">
    <title><?= htmlspecialchars($pageTitle) ?> - Support System</title>

    <?php include_once "../components/head.php"; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <style>
        /* --- Base & Print Styles (Keep unchanged from previous version) --- */
        .availability-available {
            color: #1cc88a;
            font-weight: bold;
        }

        .availability-busy {
            color: #e74a3b;
            font-weight: bold;
        }

        .availability-unknown {
            color: #858796;
            font-style: italic;
        }

        .text-warning {
            color: #f6c23e !important;
        }

        .solved-count-error {
            color: #e74a3b;
            font-style: italic;
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 250px;
            width: 100%;
            padding: 15px;
        }

        .chart-container-small {
            height: 150px;
            /* Smaller height for rating bar */
        }

        .card-body-chart {
            padding: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dataTables_wrapper .row:first-child {
            margin-bottom: 0.5rem;
        }

        .dataTables_wrapper .row:last-child {
            margin-top: 0.5rem;
        }

        .dataTables_wrapper .dt-buttons .btn-group {
            margin-left: 0.5rem;
        }

        .dt-buttons .dropdown-menu {
            margin-top: 0.125rem;
        }

        #dataTableExportButtons.dropdown-menu {
            min-width: 150px;
        }

        #dataTableExportButtons .dropdown-item {
            cursor: pointer;
        }

        .summary-card .card-body {
            font-size: 1.1rem;
        }

        .summary-card i {
            margin-right: 0.5rem;
        }

        @media print {
            body {
                background-color: #fff !important;
                color: #000 !important;
                margin: 0;
                padding: 0;
                font-size: 10pt;
                width: 100% !important;
            }

            #accordionSidebar,
            #sidebar,
            .sidebar,
            ul.navbar-nav.bg-gradient-primary.sidebar {
                display: none !important;
            }

            #content-wrapper #topbar,
            nav.navbar.topbar {
                display: none !important;
            }

            .btn,
            .modal,
            a.scroll-to-top,
            footer.sticky-footer,
            .dataTables_filter,
            .dataTables_length,
            .dataTables_paginate,
            .dataTables_info,
            .dt-buttons,
            .alert .close,
            .dropdown-menu,
            .dropdown-list,
            .dropdown-toggle {
                display: none !important;
            }

            #wrapper,
            #content-wrapper,
            #content {
                margin: 0 !important;
                padding: 10px !important;
                width: 100% !important;
                overflow: visible !important;
                background: none !important;
                box-shadow: none !important;
            }

            .container-fluid {
                padding: 0 !important;
                width: 100% !important;
            }

            .card {
                border: 1px solid #ccc !important;
                box-shadow: none !important;
                margin-bottom: 15px !important;
                page-break-inside: avoid;
                width: 100% !important;
            }

            .card-header {
                background-color: #eee !important;
                color: #000 !important;
                border-bottom: 1px solid #ccc !important;
                padding: 5px 10px !important;
            }

            .card-body {
                padding: 10px !important;
            }

            .table,
            .table th,
            .table td {
                border: 1px solid #666 !important;
                color: #000 !important;
                font-size: 9pt;
                word-wrap: break-word;
            }

            .table thead {
                display: table-header-group;
                background-color: #f2f2f2 !important;
                font-weight: bold;
            }

            .table tbody tr {
                page-break-inside: avoid;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .table th,
            .table td {
                padding: 4px 6px !important;
            }

            a,
            a:visited {
                text-decoration: none !important;
                color: #000 !important;
            }

            a[href]:after {
                content: none !important;
            }

            .chart-container {
                height: 200px;
                width: 98%;
                padding: 5px;
                page-break-inside: avoid;
            }

            canvas {
                max-width: 100%;
            }

            .row>div {
                page-break-inside: avoid;
            }

            h1,
            h3,
            h6 {
                color: #000 !important;
                margin-bottom: 10px;
                page-break-after: avoid;
            }

            .text-primary,
            .text-danger,
            .text-success,
            .text-info {
                color: #000 !important;
            }

            .text-muted {
                color: #333 !important;
            }

            .font-weight-bold {
                font-weight: bold !important;
            }

            .fas.fa-star,
            .fas.fa-star-half-alt,
            .far.fa-star {
                color: #555 !important;
            }

            .alert {
                border: 1px solid #ccc;
                background-color: #f9f9f9;
                color: #000;
                padding: 5px;
                margin-bottom: 10px;
                page-break-inside: avoid;
            }

            @page {
                size: A4;
                margin: 0.75in;
            }
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/administrator_topbar.php"; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                        <div class="d-print-none">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-info shadow-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-download fa-sm text-white-50"></i> Export / Print
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" id="dataTableExportButtons">
                                    <span class="dropdown-item disabled">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($myStatsError): ?> <div class="alert alert-danger alert-dismissible fade show" role="alert"> <?= htmlspecialchars($myStatsError) ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div> <?php endif; ?>
                    <?php if ($engineerCasesError): ?> <div class="alert alert-danger alert-dismissible fade show" role="alert"> <?= htmlspecialchars($engineerCasesError) ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div> <?php endif; ?>
                    <?php if (!$isEngineerView && $engineerRatingsError): ?> <div class="alert alert-danger alert-dismissible fade show" role="alert"> <?= htmlspecialchars($engineerRatingsError) ?> <button type="button" class="close" data-dismiss="alert">&times;</button></div> <?php endif; ?>
                    <?php // ... other admin error checks ... 
                    ?>
                    <?php if ($isEngineerView): ?>
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-primary shadow h-100 py-2 summary-card">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"> Average Rating</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"> <?php echo displayStars($myAvgRating); ?> </div>
                                            </div>
                                            <div class="col-auto"> <i class="fas fa-star fa-2x text-gray-300"></i> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-success shadow h-100 py-2 summary-card">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1"> Total Solved Cases</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"> <?= htmlspecialchars($mySolvedCount) ?> </div>
                                            </div>
                                            <div class="col-auto"> <i class="fas fa-check-circle fa-2x text-gray-300"></i> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-warning shadow h-100 py-2 summary-card">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"> Ongoing Cases</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"> <?= htmlspecialchars($myOngoingCount) ?> </div>
                                            </div>
                                            <div class="col-auto"> <i class="fas fa-tasks fa-2x text-gray-300"></i> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-6 mb-3">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">My Case Status Distribution</h6>
                                    </div>
                                    <div class="card-body card-body-chart">
                                        <?php if ($myStatsError): ?>
                                            <div class="p-3 text-danger">Could not load chart data due to error.</div>
                                        <?php elseif ($mySolvedCount == 0 && $myOngoingCount == 0): ?>
                                            <div class="p-3 text-muted">No case data found to display.</div>
                                        <?php else: ?>
                                            <div class="chart-container"> <canvas id="myCaseStatusChart"></canvas> </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">My Average Rating Visualization</h6>
                                    </div>
                                    <div class="card-body card-body-chart">
                                        <?php if ($myStatsError): ?>
                                            <div class="p-3 text-danger">Could not load chart data due to error.</div>
                                        <?php elseif ($myAvgRating == 0): ?>
                                            <div class="p-3 text-muted">No rating data found yet.</div>
                                        <?php else: ?>
                                            <div class="chart-container chart-container-small"> <canvas id="myRatingChart"></canvas> </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">My Solved Cases Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($engineerCases)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover" id="engineerCasesTable" width="100%" cellspacing="0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Case ID</th>
                                                            <th>Subject</th>
                                                            <th>Date Accepted</th>
                                                            <th>Date Solved</th>
                                                            <th>Resolution Time</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody> <?php foreach ($engineerCases as $case): $resolutionTime = formatTimeDiff($case['date_accepted'], $case['date_solved']); ?> <tr>
                                                                <td><?= htmlspecialchars($case['case_id']) ?></td>
                                                                <td><?= htmlspecialchars($case['subject']) ?></td>
                                                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($case['date_accepted']))) ?></td>
                                                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($case['date_solved']))) ?></td>
                                                                <td data-order="<?= strtotime($case['date_solved']) - strtotime($case['date_accepted']) ?>"> <?= $resolutionTime ?> </td>
                                                            </tr> <?php endforeach; ?> </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?> <div class="alert alert-info"> <?php if ($engineerCasesError) {
                                                                                            echo "Could not load case details.";
                                                                                        } else {
                                                                                            echo "No solved case details found.";
                                                                                        } ?> </div> <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">IT Engineers Overview</h6>
                                    </div>
                                    <div class="card-body"> <?php if (!empty($engineers)): ?> <div class="table-responsive">
                                                <table class="table table-bordered table-hover" id="comparisonTable" width="100%" cellspacing="0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Engineer Name</th>
                                                            <th style="display:none;">Avg Rating (Num)</th>
                                                            <th>Performance Rating</th>
                                                            <th>Availability</th>
                                                            <th>Reopened Cases</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody> <?php foreach ($engineers as $engineer): $engineerId = $engineer['id'];
                                                                    $avgRating = (float)$engineer['average_rating'];
                                                                    $ongoingCount = $ongoingCaseCounts[$engineerId] ?? 0;
                                                                    $isAvailable = $ongoingCount < 3;
                                                                    $availabilityText = ($caseCountError !== null) ? 'Unknown' : ($isAvailable ? 'Available' : 'Busy');
                                                                    $availabilityClass = ($caseCountError !== null) ? 'availability-unknown' : ($isAvailable ? 'availability-available' : 'availability-busy');
                                                                    $solvedReopenedCount = $solvedReopenedCounts[$engineerId] ?? 0;
                                                                    $solvedDisplayText = ($solvedCountError !== null) ? '<i class="fas fa-exclamation-triangle text-danger"></i> Error' : $solvedReopenedCount;
                                                                    $solvedClass = ($solvedCountError !== null) ? 'text-center text-danger font-italic' : 'text-center'; ?> <tr>
                                                                <td><?= htmlspecialchars($engineer['full_name']) ?></td>
                                                                <td data-export="rating" style="display:none;"><?= number_format($avgRating, 1) ?></td>
                                                                <td data-order="<?= $avgRating ?>"><?php echo displayStars($avgRating); ?></td>
                                                                <td class="<?= $availabilityClass ?>"><?= htmlspecialchars($availabilityText) ?></td>
                                                                <td class="<?= $solvedClass ?>"><?= $solvedDisplayText ?></td>
                                                            </tr> <?php endforeach; ?> </tbody>
                                                </table>
                                            </div> <?php else: ?> <div class="alert alert-info"> <?php if ($engineerRatingsError) {
                                                                                                        echo "Could not load engineer overview.";
                                                                                                    } else {
                                                                                                        echo "No engineer data found.";
                                                                                                    } ?> </div> <?php endif; ?> </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 mb-4">
                                <div class="card shadow">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Engineer Performance Ratings (Average)</h6>
                                    </div>
                                    <div class="card-body card-body-chart"> <?php if ($engineerRatingsError): ?> <div class="p-3 text-danger">Error loading chart data.</div> <?php elseif (empty($engineerPerformanceData['labels'])): ?> <div class="p-3 text-muted">No performance data.</div> <?php else: ?> <div class="chart-container" style="height: 300px;"><canvas id="engineerPerformanceChart"></canvas></div> <?php endif; ?> </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-4 col-lg-5 mb-4">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Top Companies</h6>
                                    </div>
                                    <div class="card-body card-body-chart"> <?php if ($topCompanyError): ?> <div class="p-3 text-danger">Error loading company data.</div> <?php elseif (empty($topCompanyData['labels'])): ?> <div class="p-3 text-muted">No company data.</div> <?php else: ?> <div class="chart-container"><canvas id="topCompanyChart"></canvas></div> <?php endif; ?> </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-5 mb-4">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Top Engineers (Reopened)</h6>
                                    </div>
                                    <div class="card-body card-body-chart"> <?php if ($topEngineerError): ?> <div class="p-3 text-danger">Error loading top engineer data.</div> <?php elseif (empty($topEngineerData['labels'])): ?> <div class="p-3 text-muted">No top engineer data.</div> <?php else: ?> <div class="chart-container"><canvas id="topEngineerChart"></canvas></div> <?php endif; ?> </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-5 mb-4">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-danger">Lowest Rated Engineers</h6>
                                    </div>
                                    <div class="card-body card-body-chart"> <?php if ($poorPerformerError): ?> <div class="p-3 text-danger">Error loading lowest rated data.</div> <?php elseif (!$showPoorPerformersCard): ?> <div class="p-3 text-muted text-center">No engineers meet low rating criteria.</div> <?php elseif (empty($lowestRatedData['labels'])): ?> <div class="p-3 text-muted text-center">No data for chart.</div> <?php else: ?> <div class="chart-container"><canvas id="lowestRatedChart"></canvas></div> <?php endif; ?> </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div> <?php include_once "../components/footer.php"; ?>
        </div>
    </div> <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
    <?php include_once "../modals/logout.php"; ?>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

    <script>
        $(document).ready(function() {
            $('body').tooltip({
                selector: '[data-toggle="tooltip"], [title]'
            });

            // --- DataTable Initialization (Conditional) ---
            var table;
            var commonButtonConfig = [ // Added Print Button
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> Export CSV',
                    titleAttr: 'Export table data to CSV',
                    className: 'dropdown-item',
                    exportOptions: {}
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Export Excel',
                    titleAttr: 'Export table data to Excel',
                    className: 'dropdown-item',
                    exportOptions: {}
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print Table',
                    titleAttr: 'Print table data',
                    className: 'dropdown-item',
                    exportOptions: {}
                } // Added Print
            ];

            if ($('#engineerCasesTable').length > 0) { // ENGINEER VIEW
                table = $('#engineerCasesTable').DataTable({
                    /* ... options ... */
                    "order": [
                        [3, "desc"]
                    ],
                    "pageLength": 10,
                    "lengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: {
                        dom: {
                            container: {
                                tag: 'div',
                                className: 'dt-buttons btn-group w-100'
                            },
                            collection: {
                                tag: 'div',
                                className: 'dropdown-menu w-100'
                            }
                        },
                        buttons: $.map(commonButtonConfig, function(btn) {
                            var newBtn = $.extend(true, {}, btn);
                            newBtn.filename = 'my_solved_cases_report_<?= date("Ymd") ?>';
                            newBtn.exportOptions = {
                                columns: [0, 1, 2, 3, 4]
                            };
                            if (btn.extend === 'print') {
                                newBtn.title = 'My Solved Cases Report'; /* Add print title */
                                newBtn.exportOptions = {
                                    columns: ':visible'
                                }; /* Print visible */
                            }
                            return newBtn;
                        })
                    },
                    columnDefs: [{
                        type: 'num',
                        targets: 4
                    }]
                });
                table.buttons().container().appendTo('#dataTableExportButtons');
                $('#dataTableExportButtons .disabled').remove();
            } else if ($('#comparisonTable').length > 0) { // ADMIN/HEAD VIEW
                table = $('#comparisonTable').DataTable({
                    /* ... options ... */
                    "order": [
                        [1, "desc"]
                    ],
                    "pageLength": 10,
                    "lengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: {
                        dom: {
                            container: {
                                tag: 'div',
                                className: 'dt-buttons btn-group w-100'
                            },
                            collection: {
                                tag: 'div',
                                className: 'dropdown-menu w-100'
                            }
                        },
                        buttons: $.map(commonButtonConfig, function(btn) {
                            var newBtn = $.extend(true, {}, btn);
                            newBtn.filename = 'engineer_comparison_report_<?= date("Ymd") ?>';
                            newBtn.exportOptions = {
                                columns: [0, 1, 3, 4]
                            };
                            if (btn.extend === 'print') {
                                newBtn.title = 'Engineer Comparison Report';
                                newBtn.exportOptions = {
                                    columns: ':visible'
                                };
                            }
                            return newBtn;
                        })
                    }
                });
                table.buttons().container().appendTo('#dataTableExportButtons');
                $('#dataTableExportButtons .disabled').remove();
            } else {
                $('.btn-group:has(#dataTableExportButtons)').hide();
            }

            // REMOVED standalone print button handler

            // --- Chart Initialization ---
            const primaryColor = '#4e73df',
                dangerColor = '#e74a3b',
                warningColor = '#f6c23e',
                infoColor = '#36b9cc',
                successColor = '#1cc88a',
                secondaryColor = '#858796';
            Chart.defaults.font.family = "'Nunito', sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#858796';
            const commonChartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7,
                            color: '#858796'
                        }
                    },
                    y: {
                        ticks: {
                            padding: 10,
                            color: '#858796'
                        },
                        grid: {
                            color: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2]
                        }
                    }
                }
            };

            // --- ENGINEER VIEW CHARTS ---
            <?php if ($isEngineerView && $myStatsError === null): ?>
                // 1. My Case Status Chart (Doughnut)
                const caseStatusCtx = document.getElementById('myCaseStatusChart')?.getContext('2d');
                if (caseStatusCtx && (<?= $mySolvedCount ?> > 0 || <?= $myOngoingCount ?> > 0)) {
                    try {
                        new Chart(caseStatusCtx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Solved Cases', 'Ongoing Cases'],
                                datasets: [{
                                    data: [<?= $mySolvedCount ?>, <?= $myOngoingCount ?>],
                                    backgroundColor: [successColor, warningColor],
                                    hoverBackgroundColor: ['#17a673', '#dda20a'],
                                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                                    hoverOffset: 4
                                }]
                            },
                            options: $.extend(true, {}, commonChartOptions, {
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 12,
                                            padding: 15
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: (context) => `${context.label || ''}: ${context.raw || 0}`
                                        }
                                    }
                                },
                                cutout: '75%'
                            })
                        });
                    } catch (e) {
                        console.error("Error creating Case Status Chart:", e);
                    }
                }

                // 2. My Rating Chart (Horizontal Bar)
                const ratingCtx = document.getElementById('myRatingChart')?.getContext('2d');
                if (ratingCtx && <?= $myAvgRating ?> > 0) {
                    try {
                        new Chart(ratingCtx, {
                            type: 'bar',
                            data: {
                                labels: ['My Avg Rating'], // Single label
                                datasets: [{
                                    label: 'Average Rating',
                                    data: [<?= $myAvgRating ?>],
                                    backgroundColor: primaryColor,
                                    borderColor: primaryColor,
                                    borderWidth: 1,
                                    barPercentage: 0.5 // Make bar thinner
                                }]
                            },
                            options: $.extend(true, {}, commonChartOptions, {
                                indexAxis: 'y', // Make it horizontal
                                scales: {
                                    x: { // X-axis is the rating value
                                        beginAtZero: true,
                                        max: 5, // Rating scale 0-5
                                        title: {
                                            display: true,
                                            text: 'Rating (out of 5)',
                                            font: {
                                                size: 10
                                            }
                                        },
                                        ticks: {
                                            stepSize: 1
                                        }
                                    },
                                    y: { // Y-axis only shows the label
                                        grid: {
                                            display: false,
                                            drawBorder: false
                                        } // Hide Y grid lines
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }, // Not needed for single bar
                                    tooltip: {
                                        callbacks: {
                                            label: (context) => `${context.dataset.label || ''}: ${context.parsed.x.toFixed(1)} Stars`
                                        }
                                    }
                                }
                            })
                        });
                    } catch (e) {
                        console.error("Error creating Rating Chart:", e);
                    }
                }
            <?php endif; ?>

            // --- ADMIN/HEAD VIEW CHARTS ---
            <?php if (!$isEngineerView): ?>
                // (Chart initialization code for Admin/Head remains unchanged)
                <?php if (!$engineerRatingsError && !empty($engineerPerformanceData['labels'])): ?>
                    try {
                        const perfCtx = document.getElementById('engineerPerformanceChart')?.getContext('2d');
                        if (perfCtx) {
                            new Chart(perfCtx, {
                                type: 'bar',
                                data: {
                                    labels: <?= json_encode($engineerPerformanceData['labels']) ?>,
                                    datasets: [{
                                        label: 'Average Rating',
                                        data: <?= json_encode($engineerPerformanceData['data']) ?>,
                                        backgroundColor: primaryColor,
                                        borderColor: primaryColor,
                                        borderWidth: 1,
                                        maxBarThickness: 25,
                                    }]
                                },
                                options: $.extend(true, {}, commonChartOptions, {
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            max: 5,
                                            title: {
                                                display: true,
                                                text: 'Average Rating (out of 5)',
                                                font: {
                                                    size: 12
                                                }
                                            }
                                        },
                                        x: {
                                            ticks: {
                                                autoSkip: false,
                                                maxRotation: 45,
                                                minRotation: 45
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: (context) => `${context.dataset.label || ''}: ${context.parsed.y.toFixed(1)} Stars`
                                            }
                                        }
                                    }
                                })
                            });
                        }
                    } catch (e) {
                        console.error("Error creating Engineer Performance Chart:", e);
                    }
                <?php endif; ?>
                <?php if (!$topCompanyError && !empty($topCompanyData['labels'])): ?>
                    try {
                        const compCtx = document.getElementById('topCompanyChart')?.getContext('2d');
                        if (compCtx) {
                            new Chart(compCtx, {
                                type: 'doughnut',
                                data: {
                                    labels: <?= json_encode($topCompanyData['labels']) ?>,
                                    datasets: [{
                                        label: 'Case Volume',
                                        data: <?= json_encode($topCompanyData['data']) ?>,
                                        backgroundColor: [primaryColor, successColor, infoColor, warningColor, secondaryColor],
                                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#6e707e'],
                                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                                        hoverOffset: 4
                                    }]
                                },
                                options: $.extend(true, {}, commonChartOptions, {
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                            labels: {
                                                boxWidth: 12,
                                                padding: 15
                                            }
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: (context) => `${context.label || ''}: ${context.raw || 0} Cases`
                                            }
                                        }
                                    },
                                    cutout: '80%'
                                })
                            });
                        }
                    } catch (e) {
                        console.error("Error creating Top Company Chart:", e);
                    }
                <?php endif; ?>
                <?php if (!$topEngineerError && !empty($topEngineerData['labels'])): ?>
                    try {
                        const topEngCtx = document.getElementById('topEngineerChart')?.getContext('2d');
                        if (topEngCtx) {
                            new Chart(topEngCtx, {
                                type: 'bar',
                                data: {
                                    labels: <?= json_encode($topEngineerData['labels']) ?>,
                                    datasets: [{
                                        label: 'Reopened Cases',
                                        data: <?= json_encode($topEngineerData['data']) ?>,
                                        backgroundColor: successColor,
                                        borderColor: successColor,
                                        borderWidth: 1
                                    }]
                                },
                                options: $.extend(true, {}, commonChartOptions, {
                                    indexAxis: 'y',
                                    scales: {
                                        x: {
                                            beginAtZero: true,
                                            title: {
                                                display: true,
                                                text: 'Number of Reopened Cases',
                                                font: {
                                                    size: 12
                                                }
                                            },
                                            ticks: {
                                                stepSize: 1
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    }
                                })
                            });
                        }
                    } catch (e) {
                        console.error("Error creating Top Engineers Chart:", e);
                    }
                <?php endif; ?>
                <?php if (!$poorPerformerError && $showPoorPerformersCard && !empty($lowestRatedData['labels'])): ?>
                    try {
                        const lowRateCtx = document.getElementById('lowestRatedChart')?.getContext('2d');
                        if (lowRateCtx) {
                            new Chart(lowRateCtx, {
                                type: 'bar',
                                data: {
                                    labels: <?= json_encode($lowestRatedData['labels']) ?>,
                                    datasets: [{
                                        label: 'Average Rating',
                                        data: <?= json_encode($lowestRatedData['data']) ?>,
                                        backgroundColor: dangerColor,
                                        borderColor: dangerColor,
                                        borderWidth: 1
                                    }]
                                },
                                options: $.extend(true, {}, commonChartOptions, {
                                    indexAxis: 'y',
                                    scales: {
                                        x: {
                                            beginAtZero: true,
                                            max: 5,
                                            title: {
                                                display: true,
                                                text: 'Average Rating (out of 5)',
                                                font: {
                                                    size: 12
                                                }
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: (context) => `${context.dataset.label || ''}: ${context.parsed.x.toFixed(1)} Stars`
                                            }
                                        }
                                    }
                                })
                            });
                        }
                    } catch (e) {
                        console.error("Error creating Lowest Rated Chart:", e);
                    }
                <?php endif; ?>
            <?php endif; ?> // --- End Conditional Chart JS ---

        }); // --- End document ready ---
    </script>

</body>

</html>
<?php
$pdo = null; // Close connection
?>

            