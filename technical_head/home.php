<?php
// Location: Manila, Metro Manila, Philippines
// Current Date/Time: Friday, April 11, 2025 at 7:51:10 AM PST
session_start();
$pageTitle = "Dashboard";

// Include necessary files for case data (Counts for cards)
require_once "../fetch/new_cases_technical.php";
require_once "../fetch/ongoing_cases_technical.php";
require_once "../fetch/solved_cases_technical.php";
require_once "../fetch/reopened_cases_technical.php";

// Include database connection configuration
require_once "../config/database.php"; // Ensure this path is correct and establishes $connection

// Initialize default values for chart data (JSON encoded empty arrays)
$engineerNamesJson = '[]';              // For Horizontal Bar Ratings Chart
$averageRatingsJson = '[]';             // For Horizontal Bar Ratings Chart
$topCompaniesNamesJson = '[]';          // For Vertical Bar Top Companies Chart
$topCompaniesCaseCountsJson = '[]';     // For Vertical Bar Top Companies Chart
$topEngineersOwnerIdsJson = '[]';       // For Polar Area Top Owners Chart
$topEngineersSolvedCountsJson = '[]';   // For Polar Area Top Owners Chart
$lowestRatedEngineersNamesJson = '[]';  // For Vertical Bar Lowest Rated Chart
$lowestRatedEngineersAvgsJson = '[]';   // For Vertical Bar Lowest Rated Chart
$newCasesPercentage = 0;                // For Doughnut Ratio Chart
$ongoingCasesPercentage = 0;            // For Doughnut Ratio Chart
$solvedCasesPercentage = 0;             // For Doughnut Ratio Chart
$reopenedCasesPercentage = 0;           // For Doughnut Ratio Chart
$limitCompanies = 10;                   // Limit for Top Companies chart
$limitEngineers = 10;                   // Limit for Top Engineers/Owners chart


// --- Fetch Data only if Connection Exists ---
if (isset($connection)) {

    // --- Check required table existence once ---
    $check_rating_table_sql = "SHOW TABLES LIKE 'engineer_ratings'";
    $rating_table_exists_result = mysqli_query($connection, $check_rating_table_sql);
    $rating_table_exists = ($rating_table_exists_result && mysqli_num_rows($rating_table_exists_result) > 0);

    $check_users_table_sql = "SHOW TABLES LIKE 'users'";
    $users_table_exists_result = mysqli_query($connection, $check_users_table_sql);
    $users_table_exists = ($users_table_exists_result && mysqli_num_rows($users_table_exists_result) > 0);

    // --- Fetch Engineer Average Ratings (Requires 'users' and 'engineer_ratings') ---
    if ($rating_table_exists && $users_table_exists) {
        $sql_ratings = "SELECT u.full_name AS engineer_name, AVG(er.rating) AS average_rating FROM engineer_ratings er JOIN users u ON er.engineer_id = u.id WHERE u.role = 'Engineer' GROUP BY er.engineer_id, u.full_name ORDER BY average_rating DESC";
        $result_ratings = mysqli_query($connection, $sql_ratings);
        if ($result_ratings) {
            $engineerNames = [];
            $averageRatings = [];
            while ($row = mysqli_fetch_assoc($result_ratings)) {
                $engineerNames[] = $row['engineer_name'];
                $averageRatings[] = round($row['average_rating'], 2);
            }
            $engineerNamesJson = json_encode($engineerNames);
            $averageRatingsJson = json_encode($averageRatings);
        } else {
            error_log("Error fetching engineer ratings: " . mysqli_error($connection));
        }
    } else {
        error_log("Engineer Ratings chart skipped: 'users' or 'engineer_ratings' table missing.");
    }

    // --- Fetch Top Companies by Case Volume (Uses only 'cases') ---
    $sql_top_companies = "SELECT company, COUNT(*) as case_count FROM cases WHERE company IS NOT NULL AND company != '' GROUP BY company ORDER BY case_count DESC LIMIT ?";
    $stmt_top_companies = mysqli_prepare($connection, $sql_top_companies);
    if ($stmt_top_companies) {
        mysqli_stmt_bind_param($stmt_top_companies, "i", $limitCompanies);
        mysqli_stmt_execute($stmt_top_companies);
        $result_top_companies = mysqli_stmt_get_result($stmt_top_companies);
        if ($result_top_companies) {
            $topCompaniesNames = [];
            $topCompaniesCaseCounts = [];
            while ($row = mysqli_fetch_assoc($result_top_companies)) {
                $topCompaniesNames[] = $row['company'];
                $topCompaniesCaseCounts[] = $row['case_count'];
            }
            $topCompaniesNamesJson = json_encode($topCompaniesNames);
            $topCompaniesCaseCountsJson = json_encode($topCompaniesCaseCounts);
        } else {
            error_log("Error fetching top companies data: " . mysqli_error($connection));
        }
        mysqli_stmt_close($stmt_top_companies);
    } else {
        error_log("Error preparing top companies statement: " . mysqli_error($connection));
    }

    // --- Fetch Top Engineers (Case Owners) by Solved Cases (Uses only 'cases') ---
    $sql_top_engineers = "SELECT c.case_owner AS owner_identifier, COUNT(c.id) AS solved_count FROM cases c WHERE c.case_status = 'Solved' AND c.case_owner IS NOT NULL AND c.case_owner != '' AND c.case_owner != '0' GROUP BY c.case_owner ORDER BY solved_count DESC LIMIT ?";
    $stmt_top_engineers = mysqli_prepare($connection, $sql_top_engineers);
    if ($stmt_top_engineers) {
        mysqli_stmt_bind_param($stmt_top_engineers, "i", $limitEngineers);
        mysqli_stmt_execute($stmt_top_engineers);
        $result_top_engineers = mysqli_stmt_get_result($stmt_top_engineers);
        if ($result_top_engineers) {
            $topEngineersOwnerIds = [];
            $topEngineersSolvedCounts = [];
            while ($row = mysqli_fetch_assoc($result_top_engineers)) {
                $ownerId = htmlspecialchars($row['owner_identifier']);
                $topEngineersOwnerIds[] = $ownerId;
                $topEngineersSolvedCounts[] = $row['solved_count'];
            }
            $topEngineersOwnerIdsJson = json_encode($topEngineersOwnerIds);
            $topEngineersSolvedCountsJson = json_encode($topEngineersSolvedCounts);
        } else {
            error_log("Error fetching top engineers (case owners) data: " . mysqli_error($connection));
        }
        mysqli_stmt_close($stmt_top_engineers);
    } else {
        error_log("Error preparing top engineers (case owners) statement: " . mysqli_error($connection));
    }

    // --- Fetch Lowest Rated Engineers (Avg <= 3.0) (Requires 'users' and 'engineer_ratings') ---
    if ($rating_table_exists && $users_table_exists) {
        $sql_lowest_ratings = "SELECT u.full_name AS engineer_name, AVG(er.rating) AS average_rating FROM engineer_ratings er JOIN users u ON er.engineer_id = u.id WHERE u.role = 'Engineer' GROUP BY er.engineer_id, u.full_name HAVING AVG(er.rating) <= 3.0 ORDER BY average_rating ASC";
        $result_lowest_ratings = mysqli_query($connection, $sql_lowest_ratings);
        if ($result_lowest_ratings) {
            $lowestRatedEngineersNames = [];
            $lowestRatedEngineersAvgs = [];
            while ($row = mysqli_fetch_assoc($result_lowest_ratings)) {
                $lowestRatedEngineersNames[] = $row['engineer_name'];
                $lowestRatedEngineersAvgs[] = round($row['average_rating'], 2);
            }
            $lowestRatedEngineersNamesJson = json_encode($lowestRatedEngineersNames);
            $lowestRatedEngineersAvgsJson = json_encode($lowestRatedEngineersAvgs);
        } else {
            error_log("Error fetching lowest rated engineers data: " . mysqli_error($connection));
        }
    } else {
        error_log("Lowest Rated Engineers chart skipped: 'users' or 'engineer_ratings' table missing.");
    }

    // --- Fetch data for Pie Chart (Uses only 'cases') ---
    $sql_pie = "SELECT SUM(CASE WHEN case_status = 'New' THEN 1 ELSE 0 END) AS new_cases, SUM(CASE WHEN case_status = 'Waiting in Progress' AND reopen = 0 THEN 1 ELSE 0 END) AS ongoing_cases, SUM(CASE WHEN case_status = 'Solved' THEN 1 ELSE 0 END) AS solved_cases, SUM(CASE WHEN case_status = 'Waiting in Progress' AND reopen > 0 THEN 1 ELSE 0 END) AS reopened_cases FROM cases";
    $result_pie = mysqli_query($connection, $sql_pie);
    if ($result_pie) {
        $data_pie = mysqli_fetch_assoc($result_pie);
        $newCases = $data_pie['new_cases'] ?? 0;
        $ongoingCases = $data_pie['ongoing_cases'] ?? 0;
        $solvedCases = $data_pie['solved_cases'] ?? 0;
        $reopenedCases = $data_pie['reopened_cases'] ?? 0;
        $totalCases = $newCases + $ongoingCases + $solvedCases + $reopenedCases;
        $newCasesPercentage = ($totalCases > 0) ? ($newCases / $totalCases) * 100 : 0;
        $ongoingCasesPercentage = ($totalCases > 0) ? ($ongoingCases / $totalCases) * 100 : 0;
        $solvedCasesPercentage = ($totalCases > 0) ? ($solvedCases / $totalCases) * 100 : 0;
        $reopenedCasesPercentage = ($totalCases > 0) ? ($reopenedCases / $totalCases) * 100 : 0;
    } else {
        error_log("Error fetching pie chart data: " . mysqli_error($connection));
    }
} else {
    error_log("Database connection variable \$connection is not set. Cannot fetch chart data.");
    $totalNewCases = $totalNewCases ?? 0;
    $totalOngoingCases = $totalOngoingCases ?? 0;
    $totalSolvedCases = $totalSolvedCases ?? 0;
    $totalReopenedCases = $totalReopenedCases ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <style>
        .chart-container-wrapper {
            min-height: 370px;
            position: relative;
        }

        .chart-bar,
        .chart-pie,
        .chart-area,
        .chart-polar-area,
        .chart-horizontal-bar {
            height: 320px;
            width: 100%;
        }

        .no-data-message {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #858796;
            font-size: 0.9rem;
            text-align: center;
            padding: 10px;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php" ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/administrator_topbar.php" ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                    </div>

                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><a class="text-primary" href="new_cases.php">New Cases</a></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalNewCases ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-plus fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1"><a class="text-success" href="ongoing_cases.php">Ongoing Cases</a></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalOngoingCases ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-spinner fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><a class="text-info" href="solved_cases.php">Solved Cases</a></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalSolvedCases ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><a class="text-warning" href="reopened_cases.php">Reopened Cases</a></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalReopenedCases ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-exclamation-circle fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Cases Statistics (Area Chart)</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="exportDropdownCasesStats" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fas fa-download fa-sm fa-fw text-gray-400"></i> </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="exportDropdownCasesStats">
                                            <div class="dropdown-header">Export Data:</div>
                                            <a class="dropdown-item export-link" href="#" data-format="csv" data-chart="casesStats">Export as CSV</a>
                                            <a class="dropdown-item export-link" href="#" data-format="excel" data-chart="casesStats">Export as Excel</a>
                                            <div class="dropdown-divider"></div> <small class="dropdown-item text-muted">Uses current filters</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="filter-div d-flex justify-content-end">
                                    <div class="status-filter p-3"> <label for="statusFilter">Case Type:</label> <select id="statusFilter" class="form-control form-control-sm">
                                            <option value="new">New</option>
                                            <option value="ongoing">On-going</option>
                                            <option value="solved">Solved</option>
                                            <option value="reopened">Reopened</option>
                                            <option value="all" selected>All</option>
                                        </select> </div>
                                    <div class="filter-container p-3"> <label for="filterSelect">Filter by:</label> <select id="filterSelect" class="form-control form-control-sm">
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly" selected>Monthly</option>
                                            <option value="yearly">Yearly</option>
                                        </select> </div>
                                </div>
                                <div class="card-body chart-container-wrapper">
                                    <div class="chart-area"> <canvas id="myAreaChart"></canvas>
                                        <div id="areaChartNoData" class="no-data-message" style="display: none;">...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Cases Ratio (Doughnut Chart)</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="exportDropdownCasesRatio" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fas fa-download fa-sm fa-fw text-gray-400"></i> </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="exportDropdownCasesRatio">
                                            <div class="dropdown-header">Export Data:</div>
                                            <a class="dropdown-item export-link" href="#" data-format="csv" data-chart="casesRatio">Export as CSV</a>
                                            <a class="dropdown-item export-link" href="#" data-format="excel" data-chart="casesRatio">Export as Excel</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body chart-container-wrapper">
                                    <div class="chart-pie pt-4 pb-2"> <canvas id="myPieChart"></canvas>
                                        <div id="pieChartNoData" class="no-data-message" style="display: none;">...</div>
                                    </div>
                                    <div class="mt-4 text-center small" id="pieChartLegend" style="min-height: 30px;"> </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Engineer Ratings (Horizontal Bar)</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="exportDropdownEngRatings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fas fa-download fa-sm fa-fw text-gray-400"></i> </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="exportDropdownEngRatings">
                                            <div class="dropdown-header">Export Data:</div>
                                            <a class="dropdown-item export-link" href="#" data-format="csv" data-chart="engineerRatings">Export as CSV</a>
                                            <a class="dropdown-item export-link" href="#" data-format="excel" data-chart="engineerRatings">Export as Excel</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body chart-container-wrapper">
                                    <div class="chart-horizontal-bar"> <canvas id="engineerRatingChart"></canvas>
                                        <div id="engineerRatingNoData" class="no-data-message" style="display: none;">...</div>
                                    </div>
                                    <hr>
                                    <div class="text-center small">Average rating based on submitted feedback.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Top Companies (Bar Chart)</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="exportDropdownTopComp" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fas fa-download fa-sm fa-fw text-gray-400"></i> </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="exportDropdownTopComp">
                                            <div class="dropdown-header">Export Data:</div>
                                            <a class="dropdown-item export-link" href="#" data-format="csv" data-chart="topCompanies">Export as CSV</a>
                                            <a class="dropdown-item export-link" href="#" data-format="excel" data-chart="topCompanies">Export as Excel</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body chart-container-wrapper">
                                    <div class="chart-bar"> <canvas id="topCompaniesChart"></canvas>
                                        <div id="topCompaniesNoData" class="no-data-message" style="display: none;">...</div>
                                    </div>
                                    <hr>
                                    <div class="text-center small">Total number of cases logged per company.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Top Case Owners (Polar Area Chart)</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="exportDropdownTopOwners" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fas fa-download fa-sm fa-fw text-gray-400"></i> </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="exportDropdownTopOwners">
                                            <div class="dropdown-header">Export Data:</div>
                                            <a class="dropdown-item export-link" href="#" data-format="csv" data-chart="topOwners">Export as CSV</a>
                                            <a class="dropdown-item export-link" href="#" data-format="excel" data-chart="topOwners">Export as Excel</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body chart-container-wrapper">
                                    <div class="chart-polar-area"> <canvas id="topEngineersChart"></canvas>
                                        <div id="topEngineersNoData" class="no-data-message" style="display: none;">...</div>
                                    </div>
                                    <hr>
                                    <div class="text-center small">Number of cases marked 'Solved' per Case Owner identifier.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-danger">Lowest Rated Engineers (Avg ≤ 3.0)</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="exportDropdownLowestRated" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fas fa-download fa-sm fa-fw text-gray-400"></i> </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="exportDropdownLowestRated">
                                            <div class="dropdown-header">Export Data:</div>
                                            <a class="dropdown-item export-link" href="#" data-format="csv" data-chart="lowestRatings">Export as CSV</a>
                                            <a class="dropdown-item export-link" href="#" data-format="excel" data-chart="lowestRatings">Export as Excel</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body chart-container-wrapper">
                                    <div class="chart-bar"> <canvas id="lowestRatedEngineersChart"></canvas>
                                        <div id="lowestRatedEngineersNoData" class="no-data-message" style="display: none;">No engineers found with an average rating of 3.0 or below.</div>
                                    </div>
                                    <hr>
                                    <div class="text-center small">Engineers whose average feedback rating is 3.0 or less.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div> <?php include_once "../components/footer.php" ?>
        </div>
    </div> <a class="scroll-to-top rounded" href="#page-top"> <i class="fas fa-angle-up"></i> </a>
    <?php include_once "../modals/logout.php" ?>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/chart.js/Chart.min.js"></script>
    <script>
        // Chart.js Defaults & Helpers
        Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#858796';

        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        function hasChartData(dataArray) {
            return Array.isArray(dataArray) && dataArray.length > 0;
        }

        function generateColors(count) {
            const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#fd7e14', '#6f42c1', '#20c9a6', '#5a5c69', '#d1d3e2', '#4e95df', '#17a673', '#2c9faf', '#f4b600', '#e02d1b'];
            let result = [];
            for (let i = 0; i < count; i++) {
                result.push(colors[i % colors.length]);
            }
            return result;
        }

        // --- Pie Chart (Doughnut) --- [UNCHANGED TYPE]
        var pieData = [<?= floatval($newCasesPercentage) ?>, <?= floatval($ongoingCasesPercentage) ?>, <?= floatval($solvedCasesPercentage) ?>, <?= floatval($reopenedCasesPercentage) ?>];
        var ctxPie = document.getElementById("myPieChart");
        var pieNoData = document.getElementById("pieChartNoData");
        var pieLegend = document.getElementById("pieChartLegend");
        var totalPiePercentage = pieData.reduce((a, b) => a + b, 0);
        if (ctxPie && pieNoData && pieLegend) {
            if (totalPiePercentage > 0.01) {
                pieNoData.style.display = 'none';
                pieLegend.innerHTML = `<span class="mr-2"><i class="fas fa-circle text-primary"></i> New (${pieData[0].toFixed(1)}%)</span> <span class="mr-2"><i class="fas fa-circle text-success"></i> Ongoing (${pieData[1].toFixed(1)}%)</span> <span class="mr-2"><i class="fas fa-circle text-info"></i> Solved (${pieData[2].toFixed(1)}%)</span> <span class="mr-2"><i class="fas fa-circle text-warning"></i> Reopened (${pieData[3].toFixed(1)}%)</span>`;
                var myPieChart = new Chart(ctxPie, {
                    type: 'doughnut', // KEEPING DOUGHNUT
                    data: {
                        labels: ["New", "Ongoing", "Solved", "Reopened"],
                        datasets: [{
                            data: pieData,
                            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f1b600'],
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        tooltips: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyFontColor: "#858796",
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            caretPadding: 10,
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    return data.labels[tooltipItem.index] + ': ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index].toFixed(1) + '%';
                                }
                            }
                        },
                        legend: {
                            display: false
                        },
                        cutoutPercentage: 80,
                    }
                });
            } else {
                pieNoData.style.display = 'block';
                pieLegend.innerHTML = '';
            }
        }

        // --- Area Chart (Line) --- [UNCHANGED TYPE]
        var ctxArea = document.getElementById("myAreaChart");
        var areaNoData = document.getElementById("areaChartNoData");
        var myLineChart;
        if (ctxArea && areaNoData) {
            myLineChart = new Chart(ctxArea.getContext('2d'), {
                type: 'line', // KEEPING LINE
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Cases',
                        lineTension: 0.3,
                        backgroundColor: "rgba(78, 115, 223, 0.05)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: [],
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 7
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                callback: function(value) {
                                    if (Number.isInteger(value)) {
                                        return number_format(value);
                                    }
                                }
                            },
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }],
                    },
                    legend: {
                        display: true
                    },
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, chart) {
                                return chart.datasets[tooltipItem.datasetIndex].label + ': ' + number_format(tooltipItem.yLabel);
                            }
                        }
                    }
                }
            });

            function updateAreaChartData(timeFilter, statusFilter) {
                /* Fetch and update logic including showing/hiding areaNoData */
                console.log(`Workspaceing area chart data for: ${timeFilter}, ${statusFilter}`);
                areaNoData.style.display = 'none';
                fetch('../fetch/line_chart_data_technical.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            timeFilter: timeFilter,
                            statusFilter: statusFilter
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Network error (${response.status})`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Area chart data received:", data);
                        let hasData = false;
                        if (data.error) {
                            console.error("API Error:", data.error);
                        } else {
                            myLineChart.data.labels = data.labels || [];
                            myLineChart.data.datasets[0].data = data.data || [];
                            var capitalizedStatus = statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1).toLowerCase();
                            myLineChart.data.datasets[0].label = (statusFilter === 'all' ? 'All' : capitalizedStatus) + " Cases";
                            hasData = hasChartData(data.labels) && hasChartData(data.data);
                        }
                        myLineChart.update();
                        areaNoData.style.display = hasData ? 'none' : 'block';
                        if (!hasData && !data.error) {
                            areaNoData.textContent = 'No case statistics data available for selected filters.';
                        } else if (data.error) {
                            areaNoData.textContent = 'Error fetching chart data: ' + data.error;
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        areaNoData.textContent = `Error loading chart data: ${error.message}.`;
                        areaNoData.style.display = 'block';
                        if (myLineChart) {
                            myLineChart.data.labels = [];
                            myLineChart.data.datasets[0].data = [];
                            myLineChart.update();
                        }
                    });
            }
            const filterSelect = document.getElementById('filterSelect');
            const statusFilter = document.getElementById('statusFilter');
            if (filterSelect && statusFilter) {
                filterSelect.addEventListener('change', function() {
                    updateAreaChartData(this.value, statusFilter.value);
                });
                statusFilter.addEventListener('change', function() {
                    updateAreaChartData(filterSelect.value, this.value);
                });
                updateAreaChartData(filterSelect.value, statusFilter.value);
            } else {
                console.error("Area chart filters missing.");
                areaNoData.textContent = 'Chart filters missing.';
                areaNoData.style.display = 'block';
            }
        }

        // --- Engineer Performance Rating Chart (Horizontal Bar) --- [CHANGED TYPE]
        var engineerNames = <?= $engineerNamesJson ?>;
        var averageRatings = <?= $averageRatingsJson ?>;
        var ctxRating = document.getElementById("engineerRatingChart");
        var ratingNoData = document.getElementById("engineerRatingNoData");
        if (ctxRating && ratingNoData) {
            if (hasChartData(engineerNames)) {
                ratingNoData.style.display = 'none';
                var engineerRatingChart = new Chart(ctxRating.getContext('2d'), {
                    type: 'horizontalBar',
                    data: {
                        labels: engineerNames,
                        datasets: [{
                            label: "Average Rating",
                            data: averageRatings,
                            backgroundColor: generateColors(engineerNames.length),
                            borderColor: '#ffffff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                left: 10,
                                right: 25,
                                top: 10,
                                bottom: 10
                            }
                        },
                        scales: {
                            xAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    max: 5,
                                    stepSize: 1,
                                    padding: 10,
                                    callback: function(value) {
                                        return number_format(value, 1);
                                    }
                                },
                                gridLines: {
                                    color: "rgb(234, 236, 244)",
                                    zeroLineColor: "rgb(234, 236, 244)",
                                    drawBorder: false,
                                    borderDash: [2],
                                    zeroLineBorderDash: [2]
                                }
                            }],
                            yAxes: [{
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    autoSkip: false
                                }
                            }]
                        },
                        legend: {
                            display: false
                        },
                        tooltips: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyFontColor: "#858796",
                            titleMarginBottom: 10,
                            titleFontColor: '#6e707e',
                            titleFontSize: 14,
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            caretPadding: 10,
                            callbacks: {
                                title: function(tooltipItem, data) {
                                    return data.labels[tooltipItem[0].index];
                                },
                                label: function(tooltipItem, chart) {
                                    return chart.datasets[tooltipItem.datasetIndex].label + ': ' + Number(tooltipItem.xLabel).toFixed(2);
                                }
                            }
                        }
                    }
                });
            } else {
                ratingNoData.style.display = 'block';
            }
        }

        // --- Top Companies by Case Volume Chart (Vertical Bar) --- [CHANGED TYPE back to Bar]
        var topCompaniesNames = <?= $topCompaniesNamesJson ?>;
        var topCompaniesCaseCounts = <?= $topCompaniesCaseCountsJson ?>;
        var ctxTopCompanies = document.getElementById("topCompaniesChart");
        var topCompaniesNoData = document.getElementById("topCompaniesNoData");
        if (ctxTopCompanies && topCompaniesNoData) {
            if (hasChartData(topCompaniesNames)) {
                topCompaniesNoData.style.display = 'none';
                var topCompaniesChart = new Chart(ctxTopCompanies.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: topCompaniesNames,
                        datasets: [{
                            label: "Case Volume",
                            data: topCompaniesCaseCounts,
                            backgroundColor: 'rgba(28, 200, 138, 0.8)',
                            borderColor: 'rgba(28, 200, 138, 1)',
                            borderWidth: 1,
                            maxBarThickness: 60
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                left: 10,
                                right: 25,
                                top: 25,
                                bottom: 10
                            }
                        },
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: <?= $limitCompanies ?>
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    maxTicksLimit: 6,
                                    padding: 10,
                                    callback: function(value) {
                                        if (Number.isInteger(value)) {
                                            return number_format(value);
                                        }
                                    }
                                },
                                gridLines: {
                                    color: "rgb(234, 236, 244)",
                                    zeroLineColor: "rgb(234, 236, 244)",
                                    drawBorder: false,
                                    borderDash: [2],
                                    zeroLineBorderDash: [2]
                                }
                            }]
                        },
                        legend: {
                            display: false
                        },
                        tooltips: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyFontColor: "#858796",
                            titleMarginBottom: 10,
                            titleFontColor: '#6e707e',
                            titleFontSize: 14,
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            caretPadding: 10,
                            callbacks: {
                                title: function(tooltipItem, data) {
                                    return data.labels[tooltipItem[0].index];
                                },
                                label: function(tooltipItem, chart) {
                                    return chart.datasets[tooltipItem.datasetIndex].label + ': ' + number_format(tooltipItem.yLabel);
                                }
                            }
                        }
                    }
                });
            } else {
                topCompaniesNoData.style.display = 'block';
            }
        }

        // --- Top Engineers (Case Owners) by Solved Cases Chart (Polar Area) --- [CHANGED TYPE]
        var topEngineersOwnerIds = <?= $topEngineersOwnerIdsJson ?>;
        var topEngineersSolvedCounts = <?= $topEngineersSolvedCountsJson ?>;
        var ctxTopEngineers = document.getElementById("topEngineersChart");
        var topEngineersNoData = document.getElementById("topEngineersNoData");
        if (ctxTopEngineers && topEngineersNoData) {
            if (hasChartData(topEngineersOwnerIds)) {
                topEngineersNoData.style.display = 'none';
                var topEngineersChart = new Chart(ctxTopEngineers.getContext('2d'), {
                    type: 'polarArea',
                    data: {
                        labels: topEngineersOwnerIds,
                        datasets: [{
                            label: "Solved Cases",
                            data: topEngineersSolvedCounts,
                            backgroundColor: generateColors(topEngineersOwnerIds.length),
                            borderColor: 'rgba(255, 255, 255, 0.5)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: {
                            padding: 15
                        },
                        scale: {
                            ticks: {
                                beginAtZero: true,
                                backdropColor: 'transparent',
                                stepSize: Math.ceil(Math.max(...topEngineersSolvedCounts, 1) / 5) || 1
                            },
                            gridLines: {
                                color: "rgba(0, 0, 0, 0.08)"
                            },
                            angleLines: {
                                color: "rgba(0, 0, 0, 0.08)"
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12
                            }
                        },
                        tooltips: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyFontColor: "#858796",
                            titleFontColor: '#6e707e',
                            titleFontSize: 14,
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: true,
                            caretPadding: 10,
                            callbacks: {
                                title: function(tooltipItem, data) {
                                    return 'Owner: ' + data.labels[tooltipItem[0].index];
                                },
                                label: function(tooltipItem, data) {
                                    var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                    return data.datasets[tooltipItem.datasetIndex].label + ': ' + number_format(value);
                                }
                            }
                        }
                    }
                });
            } else {
                topEngineersNoData.style.display = 'block';
            }
        }

        // --- Lowest Rated Engineers Chart (Vertical Bar) --- [NEW CHART]
        var lowestRatedEngineersNames = <?= $lowestRatedEngineersNamesJson ?>;
        var lowestRatedEngineersAvgs = <?= $lowestRatedEngineersAvgsJson ?>;
        var ctxLowestEngineers = document.getElementById("lowestRatedEngineersChart");
        var lowestEngineersNoData = document.getElementById("lowestRatedEngineersNoData");
        if (ctxLowestEngineers && lowestEngineersNoData) {
            if (hasChartData(lowestRatedEngineersNames)) {
                lowestEngineersNoData.style.display = 'none';
                var lowestRatedEngineersChart = new Chart(ctxLowestEngineers.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: lowestRatedEngineersNames,
                        datasets: [{
                            label: "Average Rating",
                            data: lowestRatedEngineersAvgs,
                            backgroundColor: 'rgba(231, 74, 59, 0.8)',
                            borderColor: 'rgba(231, 74, 59, 1)',
                            borderWidth: 1,
                            maxBarThickness: 60
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                left: 10,
                                right: 25,
                                top: 25,
                                bottom: 10
                            }
                        },
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 15
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    max: 3.5,
                                    stepSize: 0.5,
                                    padding: 10,
                                    callback: function(value) {
                                        return number_format(value, 1);
                                    }
                                },
                                gridLines: {
                                    color: "rgb(234, 236, 244)",
                                    zeroLineColor: "rgb(234, 236, 244)",
                                    drawBorder: false,
                                    borderDash: [2],
                                    zeroLineBorderDash: [2]
                                }
                            }]
                        },
                        legend: {
                            display: false
                        },
                        tooltips: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyFontColor: "#858796",
                            titleMarginBottom: 10,
                            titleFontColor: '#6e707e',
                            titleFontSize: 14,
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            caretPadding: 10,
                            callbacks: {
                                title: function(tooltipItem, data) {
                                    return data.labels[tooltipItem[0].index];
                                },
                                label: function(tooltipItem, chart) {
                                    return chart.datasets[tooltipItem.datasetIndex].label + ': ' + Number(tooltipItem.yLabel).toFixed(2);
                                }
                            }
                        }
                    }
                });
            } else {
                lowestEngineersNoData.style.display = 'block';
            }
        }

        // --- Export Logic ---
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('export-link')) {
                event.preventDefault();
                const format = event.target.getAttribute('data-format');
                const chart = event.target.getAttribute('data-chart');
                let exportUrl = 'export_handler.php?format=' + encodeURIComponent(format) + '&chart=' + encodeURIComponent(chart);

                if (chart === 'casesStats') {
                    const timeFilter = document.getElementById('filterSelect') ? document.getElementById('filterSelect').value : 'monthly';
                    const statusFilter = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : 'all';
                    exportUrl += '&timeFilter=' + encodeURIComponent(timeFilter);
                    exportUrl += '&statusFilter=' + encodeURIComponent(statusFilter);
                }
                console.log("Redirecting to export URL:", exportUrl);
                window.location.href = exportUrl;
            }
        });

        // Final check for area chart update function (optional)
        if (typeof updateAreaChartData !== 'function') {
            console.error("updateAreaChartData function is not defined globally when needed.");
        }
    </script>

</body>

</html>