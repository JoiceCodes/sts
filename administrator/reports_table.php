<?php
session_start();
$pageTitle = "Reports";
include("../config/database2.php");

$ongoingCaseStatus = 'Waiting in Progress'; 
$solvedStatus = 'Solved';
$reopenThreshold = 0; 
$poorPerformanceThreshold = 3.0; 

function displayStars(float $rating, int $maxStars = 5): string
{
    $output = '';
    $rating = max(0, min($rating, $maxStars)); 
    $roundedRating = round($rating * 2) / 2; // Round to nearest 0.5
    $fullStars = floor($roundedRating);
    $halfStar = ($roundedRating - $fullStars) >= 0.5;
    $emptyStars = $maxStars - $fullStars - ($halfStar ? 1 : 0);

    // Append star icons
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

$engineers = []; // For main table
$ongoingCaseCounts = []; // Key: engineer_id, Value: count
$solvedReopenedCounts = []; // Key: engineer_id, Value: count

// Data for Report Cards/Charts
$topCompanies = [];
$topEngineers = [];
$poorPerformers = []; // Holds raw query result for bottom 3
$displayablePoorPerformers = []; // Holds engineers actually meeting the threshold

// Chart Data Arrays (Prepared for JSON encoding)
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
$showPoorPerformersCard = false; // Flag to control display of the lowest rated section

// --- Data Fetching ---

// 1. Fetch Engineer Data (for Table & Performance Chart)
try {
    $sql_engineers = "SELECT u.id, u.full_name, COALESCE(AVG(er.rating), 0) AS average_rating
                      FROM users u
                      LEFT JOIN engineer_ratings er ON u.id = er.engineer_id
                      WHERE u.role = 'engineer'
                      GROUP BY u.id, u.full_name
                      ORDER BY u.full_name;";
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

// 2. Fetch Dependent Data (Counts for Table - only if engineers were fetched)
if (!empty($engineers)) {
    try { // Ongoing Counts
        $sql_ongoing_cases = "SELECT user_id, COUNT(*) as ongoing_case_count
                              FROM cases
                              WHERE user_id IS NOT NULL AND case_status = :ongoing_status
                              GROUP BY user_id;";
        $stmt_ongoing_cases = $pdo->prepare($sql_ongoing_cases);
        $stmt_ongoing_cases->bindParam(':ongoing_status', $ongoingCaseStatus, PDO::PARAM_STR);
        $stmt_ongoing_cases->execute();
        $ongoingCountsResult = $stmt_ongoing_cases->fetchAll(PDO::FETCH_ASSOC);
        foreach ($ongoingCountsResult as $row) {
            $ongoingCaseCounts[$row['user_id']] = $row['ongoing_case_count'];
        }
    } catch (\PDOException $e) {
        $caseCountError = "Error fetching ongoing case counts: " . $e->getMessage();
        error_log($caseCountError);
    }

    try { // Solved Counts
        $sql_solved_table = "SELECT user_id, COUNT(*) as solved_reopened_count
                             FROM cases
                             WHERE user_id IS NOT NULL AND reopen > :reopen_threshold
                             GROUP BY user_id;";
        $stmt_solved_table = $pdo->prepare($sql_solved_table);
        $stmt_solved_table->bindParam(':reopen_threshold', $reopenThreshold, PDO::PARAM_INT);
        $stmt_solved_table->execute();
        $solvedCountsResultTable = $stmt_solved_table->fetchAll(PDO::FETCH_ASSOC);
        foreach ($solvedCountsResultTable as $row) {
            $solvedReopenedCounts[$row['user_id']] = $row['solved_reopened_count'];
        }
    } catch (\PDOException $e) {
        $solvedCountError = "Error fetching solved/reopened counts: " . $e->getMessage();
        error_log($solvedCountError);
    }
}

// 3. Fetch Data for Report Sections & Specific Charts

// Top Company
try {
    $sql_top_company = "SELECT company, COUNT(*) as case_count
                        FROM cases
                        WHERE company IS NOT NULL AND company != ''
                        GROUP BY company
                        ORDER BY case_count DESC
                        LIMIT 5;";
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

// Top Engineer
try {
    $sql_top_engineer = "SELECT u.full_name, COUNT(c.id) as solved_count
                         FROM cases c
                         JOIN users u ON c.user_id = u.id
                         WHERE c.user_id IS NOT NULL AND c.reopen > :reopen_threshold
                         GROUP BY c.user_id, u.full_name
                         ORDER BY solved_count DESC
                         LIMIT 3;";
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

// Poor Performance Engineer
try {
    $sql_poor_perf = "
        SELECT u.id, u.full_name, COALESCE(AVG(er.rating), 0) AS average_rating
        FROM users u
        LEFT JOIN engineer_ratings er ON u.id = er.engineer_id
        WHERE u.role = 'engineer'
        GROUP BY u.id, u.full_name
        ORDER BY average_rating ASC
        LIMIT 3;
    ";
    $stmt_poor_perf = $pdo->query($sql_poor_perf);
    $poorPerformers = $stmt_poor_perf->fetchAll(PDO::FETCH_ASSOC);

    foreach ($poorPerformers as $performer) {
        if ((float)$performer['average_rating'] <= $poorPerformanceThreshold) {
            $displayablePoorPerformers[] = $performer;
            $lowestRatedData['labels'][] = $performer['full_name'];
            $lowestRatedData['data'][] = round((float)$performer['average_rating'], 1);
        }
    }
    if (!empty($displayablePoorPerformers)) {
        $showPoorPerformersCard = true;
    }
} catch (\PDOException $e) {
    $poorPerformerError = "Error fetching poor performance engineers: " . $e->getMessage();
    error_log($poorPerformerError);
}
// --- End Data Fetching ---

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <?php include_once "../components/head.php"; // Include your common head elements (like SB Admin 2 CSS) 
    ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <?php include_once "reports_style.php"; ?>
</head>

<body id="page-top">
    <div id="wrapper">

        <?php include_once "../components/sidebar.php"; // Include your sidebar 
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

            <?php include_once "../components/administrator_topbar.php" ?>


                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                        <div>
                            <button id="printReportButton" class="btn btn-sm btn-secondary shadow-sm mr-2">
                                <i class="fas fa-print fa-sm text-white-50"></i> Print Report
                            </button>
                            <span id="dataTableButtons"></span>
                        </div>
                    </div>

                    <?php if ($engineerRatingsError): ?> <div class="alert alert-danger alert-dismissible fade show" role="alert">Error fetching engineer ratings data. Details: <?= htmlspecialchars($engineerRatingsError) ?> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> <?php endif; ?>
                    <?php if ($caseCountError): ?> <div class="alert alert-warning alert-dismissible fade show" role="alert">Could not determine engineer availability accurately. Details: <?= htmlspecialchars($caseCountError) ?> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> <?php endif; ?>
                    <?php if ($solvedCountError): ?> <div class="alert alert-warning alert-dismissible fade show" role="alert">Could not load solved case counts accurately. Details: <?= htmlspecialchars($solvedCountError) ?> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> <?php endif; ?>
                    <?php if ($topCompanyError): ?> <div class="alert alert-warning alert-dismissible fade show" role="alert">Could not load Top Company data. Details: <?= htmlspecialchars($topCompanyError) ?> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> <?php endif; ?>
                    <?php if ($topEngineerError): ?> <div class="alert alert-warning alert-dismissible fade show" role="alert">Could not load Top Engineer data. Details: <?= htmlspecialchars($topEngineerError) ?> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> <?php endif; ?>
                    <?php if ($poorPerformerError): ?> <div class="alert alert-danger alert-dismissible fade show" role="alert">Error fetching lowest rated engineers. Details: <?= htmlspecialchars($poorPerformerError) ?> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div> <?php endif; ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">IT Engineers Overview</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($engineers) && !$engineerRatingsError): ?>
                                        <div class="alert alert-info">No engineer data found or an error occurred fetching ratings.</div>
                                    <?php elseif (!empty($engineers)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="comparisonTable" width="100%" cellspacing="0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Engineer Name</th>
                                                        <th style="display:none;">Avg Rating</th>
                                                        <th>Performance</th>
                                                        <th>Availability</th>
                                                        <th>Solved Cases</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($engineers as $engineer): ?>
                                                        <?php
                                                        $engineerId = $engineer['id'];
                                                        $avgRating = (float)$engineer['average_rating'];
                                                        $ongoingCount = $ongoingCaseCounts[$engineerId] ?? 0;
                                                        $isAvailable = $ongoingCount < 3; // Assuming < 3 cases means available
                                                        $availabilityText = ($caseCountError !== null) ? 'Unknown' : ($isAvailable ? 'Available' : 'Busy');
                                                        $availabilityClass = ($caseCountError !== null) ? 'availability-unknown' : ($isAvailable ? 'availability-available' : 'availability-busy');
                                                        $solvedReopenedCount = $solvedReopenedCounts[$engineerId] ?? 0;
                                                        $solvedDisplayText = ($solvedCountError !== null) ? 'Error' : $solvedReopenedCount;
                                                        $solvedClass = ($solvedCountError !== null) ? 'text-center text-danger font-italic' : 'text-center';
                                                        ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($engineer['full_name']) ?></td>
                                                            <td data-export="rating" style="display:none;"><?= number_format($avgRating, 1) ?></td>
                                                            <td data-order="<?= $avgRating ?>"><?php echo displayStars($avgRating); ?></td>
                                                            <td class="<?= $availabilityClass ?>"><?= htmlspecialchars($availabilityText) ?></td>
                                                            <td class="<?= $solvedClass ?>"><?= $solvedDisplayText ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Engineer Performance Ratings (Average)</h6>
                                </div>
                                <div class="card-body card-body-chart">
                                    <?php if ($engineerRatingsError): ?>
                                        <div class="p-3 text-danger">Error loading chart data due to rating fetch error.</div>
                                    <?php elseif (empty($engineerPerformanceData['labels'])): ?>
                                        <div class="p-3 text-muted">No performance data available for chart.</div>
                                    <?php else: ?>
                                        <div class="chart-container" style="height: 300px;">
                                            <canvas id="engineerPerformanceChart"></canvas>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Top Companies (by Case Volume)</h6>
                                </div>
                                <div class="card-body card-body-chart">
                                    <?php if ($topCompanyError): ?>
                                        <div class="p-3 text-danger">Error loading company data.</div>
                                    <?php elseif (empty($topCompanyData['labels'])): ?>
                                        <div class="p-3 text-muted">No company data found.</div>
                                    <?php else: ?>
                                        <div class="chart-container">
                                            <canvas id="topCompanyChart"></canvas>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Top Engineers (by Solved Cases)</h6>
                                </div>
                                <div class="card-body card-body-chart">
                                    <?php if ($topEngineerError): ?>
                                        <div class="p-3 text-danger">Error loading top engineer data.</div>
                                    <?php elseif (empty($topEngineerData['labels'])): ?>
                                        <div class="p-3 text-muted">No engineer solved case data found.</div>
                                    <?php else: ?>
                                        <div class="chart-container">
                                            <canvas id="topEngineerChart"></canvas>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-danger">Lowest Rated Engineers (Avg ≤ <?= number_format($poorPerformanceThreshold, 1) ?> <i class="fas fa-star text-warning"></i>)</h6>
                                </div>
                                <div class="card-body card-body-chart">
                                    <?php if ($poorPerformerError): ?>
                                        <div class="p-3 text-danger">Error loading lowest rated engineer data.</div>
                                    <?php elseif (!$showPoorPerformersCard): ?>
                                        <div class="p-3 text-muted text-center">All engineers have average ratings above <?= number_format($poorPerformanceThreshold, 1) ?> stars.</div>
                                    <?php elseif (empty($lowestRatedData['labels'])): ?>
                                        <div class="p-3 text-muted text-center">No data available for this chart, despite meeting threshold criteria.</div>
                                    <?php else: ?>
                                        <div class="chart-container">
                                            <canvas id="lowestRatedChart"></canvas>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div> <?php include_once "../components/footer.php"; // Include your footer 
                    ?>

        </div>
    </div> <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <?php include_once "../modals/logout.php"; // Include your logout modal 
    ?>

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
            // Initialize Bootstrap Tooltips
            $('body').tooltip({
                selector: '[data-toggle="tooltip"], [title]'
            });

            // Dismiss alerts functionality
            $('.alert').alert();

            // --- Initialize DataTable with Buttons ---
            if ($('#comparisonTable tbody tr').length > 0) {
                var table = $('#comparisonTable').DataTable({
                    "order": [
                        [1, "desc"]
                    ], // Order by hidden rating column
                    "pageLength": 10,
                    "lengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                        "<'row'<'col-sm-12 mt-3'B>>",
                    buttons: [{
                            extend: 'csvHtml5',
                            text: '<i class="fas fa-file-csv"></i> Export CSV',
                            className: 'btn btn-sm btn-primary shadow-sm mr-2', // Added mr-2
                            titleAttr: 'Export table data to CSV',
                            filename: 'engineer_comparison_report_<?= date("Ymd") ?>',
                            exportOptions: {
                                columns: [0, 1, 3, 4]
                            } // Indices: Name, Rating(hidden), Availability, Solved
                        },
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fas fa-file-excel"></i> Export Excel',
                            className: 'btn btn-sm btn-success shadow-sm',
                            titleAttr: 'Export table data to Excel',
                            filename: 'engineer_comparison_report_<?= date("Ymd") ?>',
                            exportOptions: {
                                columns: [0, 1, 3, 4]
                            }
                        }
                    ]
                });

                table.buttons().container().appendTo('#dataTableButtons');
            }

            // --- Custom Print Button Handler ---
            $('#printReportButton').on('click', function() {
                window.print();
            });

            // --- Chart Initialization ---
            const primaryColor = '#4e73df',
                dangerColor = '#e74a3b',
                warningColor = '#f6c23e',
                infoColor = '#36b9cc',
                successColor = '#1cc88a',
                secondaryColor = '#858796';
            Chart.defaults.font.family = "'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif";

            // 1. Engineer Performance Chart (Bar)
            <?php if (!$engineerRatingsError && !empty($engineerPerformanceData['labels'])): ?>
                const perfCtx = document.getElementById('engineerPerformanceChart').getContext('2d');
                new Chart(perfCtx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($engineerPerformanceData['labels']) ?>,
                        datasets: [{
                            label: 'Average Rating',
                            data: <?= json_encode($engineerPerformanceData['data']) ?>,
                            backgroundColor: primaryColor,
                            borderColor: primaryColor,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 5,
                                title: {
                                    display: true,
                                    text: 'Average Rating (out of 5)'
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
                    }
                });
            <?php endif; ?>

            // 2. Top Company Chart (Doughnut)
            <?php if (!$topCompanyError && !empty($topCompanyData['labels'])): ?>
                const compCtx = document.getElementById('topCompanyChart').getContext('2d');
                new Chart(compCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?= json_encode($topCompanyData['labels']) ?>,
                        datasets: [{
                            label: 'Case Volume',
                            data: <?= json_encode($topCompanyData['data']) ?>,
                            backgroundColor: [primaryColor, successColor, infoColor, warningColor, secondaryColor],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.label || ''}: ${context.raw || 0} Cases`
                                }
                            }
                        }
                    }
                });
            <?php endif; ?>

            // 3. Top Engineers Chart (Horizontal Bar)
            <?php if (!$topEngineerError && !empty($topEngineerData['labels'])): ?>
                const topEngCtx = document.getElementById('topEngineerChart').getContext('2d');
                new Chart(topEngCtx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($topEngineerData['labels']) ?>,
                        datasets: [{
                            label: 'Solved Cases',
                            data: <?= json_encode($topEngineerData['data']) ?>,
                            backgroundColor: successColor,
                            borderColor: successColor,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Solved Cases'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            <?php endif; ?>

            // 4. Lowest Rated Engineers Chart (Horizontal Bar)
            <?php if (!$poorPerformerError && $showPoorPerformersCard && !empty($lowestRatedData['labels'])): ?>
                const lowRateCtx = document.getElementById('lowestRatedChart').getContext('2d');
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
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                max: 5,
                                title: {
                                    display: true,
                                    text: 'Average Rating (out of 5)'
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
                    }
                });
            <?php endif; ?>
            // --- End Chart Initialization ---

        });
    </script>

</body>

</html>
<?php
$pdo = null; // Close the database connection
?>