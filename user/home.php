<?php
session_start();
$pageTitle = "Home";
require_once "../fetch/new_cases_by_user.php";
require_once "../fetch/ongoing_cases_by_user.php";
require_once "../fetch/solved_cases_by_user.php";
require_once "../fetch/reopened_cases_by_user.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
</head>

<body id="page-top">
     <!-- Page Wrapper -->
     <div id="wrapper">
        <!-- Sidebar -->
        <?php include_once "../components/sidebar.php" ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php include_once "../components/user_topbar.php" ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                        <!-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generate
                            Report</a> -->
                    </div>


                    <!-- Content Row -->
                    <div class="row">
                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                <a class="text-primary" href="new_cases.php">New Cases</a>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $totalNewCases ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-plus fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                 <a class="text-success" href="ongoing_cases.php">Ongoing Cases</a>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $totalOngoingCases ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                <a class="text-info" href="solved_cases.php">Solved Cases</a>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $totalSolvedCases ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                <a class="text-warning" href="reopened_cases.php">Reopened Cases</a>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $totalReopenedCases ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->

                    <div class="row">
                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        Cases Statistics
                                    </h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="filter-div d-flex justify-content-end">
                                    <div class="status-filter p-3">
                                        <label for="statusFilter">Case Type:</label>
                                        <select id="statusFilter" class="form-control">
                                            <option value="new">New</option>
                                            <option value="ongoing">On-going</option>
                                            <option value="solved">Solved</option>
                                            <option value="reopened">Reopened</option>
                                            <option value="all">All</option>
                                        </select>
                                    </div>

                                    <div class="filter-container p-3">
                                        <label for="filterSelect">Filter by:</label>
                                        <select id="filterSelect" class="form-control">
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly" selected>Monthly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="myAreaChart" style="width: 100%; height: 400px;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        Cases Ratio
                                    </h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                    <!-- <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-primary"></i> Direct
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Social
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-info"></i> Referral
                                        </span>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php include_once "../components/footer.php" ?>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <?php include_once "../modals/logout.php" ?>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <!-- <script src="../js/demo/chart-area-demo.js"></script> -->
    <!-- <script src="../js/demo/chart-pie-demo.js"></script> -->

    <?php
    // Example of how to fetch the case counts from the database
    $sql = "SELECT
            SUM(CASE WHEN case_status = 'New' THEN 1 ELSE 0 END) AS new_cases,
            SUM(CASE WHEN case_status = 'Waiting in Progress' AND reopen = 0 THEN 1 ELSE 0 END) AS ongoing_cases,
            SUM(CASE WHEN case_status = 'Solved' THEN 1 ELSE 0 END) AS solved_cases,
            SUM(CASE WHEN case_status = 'Waiting in Progress' AND reopen > 0 THEN 1 ELSE 0 END) AS reopened_cases
        FROM cases WHERE case_owner = " . $_SESSION["user_id"];

    $result = mysqli_query($connection, $sql);
    $data = mysqli_fetch_assoc($result);

    $newCases = $data['new_cases'];
    $ongoingCases = $data['ongoing_cases'];
    $solvedCases = $data['solved_cases'];
    $reopenedCases = $data['reopened_cases'];

    $totalCases = $newCases + $ongoingCases + $solvedCases + $reopenedCases;

    $newCasesPercentage = ($totalCases > 0) ? ($newCases / $totalCases) * 100 : 0;
    $ongoingCasesPercentage = ($totalCases > 0) ? ($ongoingCases / $totalCases) * 100 : 0;
    $solvedCasesPercentage = ($totalCases > 0) ? ($solvedCases / $totalCases) * 100 : 0;
    $reopenedCasesPercentage = ($totalCases > 0) ? ($reopenedCases / $totalCases) * 100 : 0;
    ?>

    <script>
        // Pass the PHP variables into JavaScript
        var newCases = <?= $newCasesPercentage ?>;
        var ongoingCases = <?= $ongoingCasesPercentage ?>;
        var solvedCases = <?= $solvedCasesPercentage ?>;
        var reopenedCases = <?= $reopenedCasesPercentage ?>;

        // Pie Chart Example
        var ctx = document.getElementById("myPieChart");
        var myPieChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ["New", "Ongoing", "Solved", "Reopened"],
                datasets: [{
                    data: [newCases, ongoingCases, solvedCases, reopenedCases],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f1b600'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                aspectRatio: window.innerWidth > 1200 ? 2 : 1, // Adjust based on screen width
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
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
                        // This will append the percentage with the percent sign
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var total = dataset.data.reduce(function(acc, value) {
                                return acc + value;
                            }, 0);
                            var currentValue = dataset.data[tooltipItem.index];
                            var percentage = Math.floor((currentValue / total) * 100);

                            // Get the label of the segment (in this case, the status of the case)
                            var label = data.labels[tooltipItem.index];

                            return label + ": " + percentage + "%"; // Append % sign
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    display: true, // Show the legend
                    labels: {
                        generateLabels: function(chart) {
                            return [{
                                    text: 'New',
                                    fillStyle: '#4e73df'
                                },
                                {
                                    text: 'Ongoing',
                                    fillStyle: '#1cc88a'
                                },
                                {
                                    text: 'Solved',
                                    fillStyle: '#36b9cc'
                                },
                                {
                                    text: 'Reopened',
                                    fillStyle: '#f6c23e'
                                },
                            ];
                        }
                    },
                },
                cutoutPercentage: 80,
            },
        });
    </script>

    <script>
        // Default to Monthly and All for status
        var defaultTimeFilter = 'monthly';
        var defaultStatusFilter = 'all';

        // Create the chart
        var ctx = document.getElementById("myAreaChart").getContext('2d');
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [], // Empty labels initially
                datasets: [{
                    label: '',
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
                    data: [], // Empty data initially
                }],
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
                                return value; // Display total cases as numbers (no dollar signs)
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
                        label: function(tooltipItem) {
                            return "Cases: " + tooltipItem.yLabel; // Include status in the tooltip
                        }
                    }
                }
            }
        });

        // Set the initial values for the filters to Monthly and All
        document.getElementById('filterSelect').value = defaultTimeFilter;
        document.getElementById('statusFilter').value = defaultStatusFilter;

        // Event listener for the time-based filter (Weekly, Monthly, Yearly)
        document.getElementById('filterSelect').addEventListener('change', function() {
            var selectedFilter = this.value;
            updateChartData(selectedFilter, document.getElementById('statusFilter').value);
        });

        // Event listener for the status-based filter (New, On-going, Solved, Reopened)
        document.getElementById('statusFilter').addEventListener('change', function() {
            var selectedStatus = this.value;
            updateChartData(document.getElementById('filterSelect').value, selectedStatus);
        });

        function updateChartData(timeFilter, statusFilter) {
            // Send an AJAX request to fetch data from the server
            fetch('../fetch/line_chart_data.php', {
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
                        throw new Error('Server error: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    // Handle error in case of an API issue
                    if (data.error) {
                        console.error(data.error);
                    } else {
                        // Capitalize the first letter of the statusFilter
                        var capitalizedStatus = statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1).toLowerCase();

                        // Handle edge case for 'all' status filter
                        if (statusFilter === 'all') {
                            capitalizedStatus = ""; // You can adjust this as needed
                        }
                        var label = capitalizedStatus + " Cases";

                        // Check if the data is empty
                        if (data.labels.length === 0 || data.data.length === 0) {
                            // Handle empty data case (e.g., show a message or clear the chart)
                            alert('No data available for the selected filters');
                            myLineChart.data.labels = [];
                            myLineChart.data.datasets[0].data = [];
                            myLineChart.data.datasets[0].label = label.trim();
                            myLineChart.update();
                        } else {
                            // Update the chart with the fetched data
                            myLineChart.data.labels = data.labels;
                            myLineChart.data.datasets[0].data = data.data;
                            myLineChart.data.datasets[0].label = label.trim();
                            myLineChart.update();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        }

        // Initial load of the chart with the default filters
        updateChartData(defaultTimeFilter, defaultStatusFilter);
    </script>

</body>

</html>