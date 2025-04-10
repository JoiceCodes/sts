<?php
session_start();
$pageTitle = "Reports";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php" ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
            <?php include_once "../components/administrator_topbar.php" ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>

                        <a href="reports_table.php" class="btn btn-primary"><i class="bi bi-view-list"></i> Table View</a>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <!-- Total Cases Solved Comparison Chart -->
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Total Cases Solved</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="totalCasesChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Average Response Time Comparison Chart -->
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Average Response Time</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="responseTimeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section (Continued) -->
                    <div class="row">
                        <!-- Average Resolution Time Comparison Chart -->
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Average Resolution Time</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="resolutionTimeChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Average Customer Feedback Comparison Chart -->
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Average Customer Feedback</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="customerFeedbackChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php include_once "../components/footer.php" ?>
        </div>
    </div>

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

    <!-- Chart.js initialization -->
    <script>
        // Static values for engineer performance (replace with dynamic data later)
        const engineer1Data = {
            totalCases: 50,
            responseTime: 30,
            resolutionTime: 2,
            customerFeedback: 4.5
        };

        const engineer2Data = {
            totalCases: 40,
            responseTime: 35,
            resolutionTime: 3,
            customerFeedback: 4.2
        };

        // Total Cases Solved Chart
        const totalCasesChartCtx = document.getElementById('totalCasesChart').getContext('2d');
        new Chart(totalCasesChartCtx, {
            type: 'bar',
            data: {
                labels: ['Engineer 1', 'Engineer 2'],
                datasets: [{
                    label: 'Total Cases Solved',
                    data: [engineer1Data.totalCases, engineer2Data.totalCases],
                    backgroundColor: ['#4e73df', '#1cc88a'],
                    borderColor: ['#4e73df', '#1cc88a'],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Average Response Time Chart
        const responseTimeChartCtx = document.getElementById('responseTimeChart').getContext('2d');
        new Chart(responseTimeChartCtx, {
            type: 'bar',
            data: {
                labels: ['Engineer 1', 'Engineer 2'],
                datasets: [{
                    label: 'Average Response Time (mins)',
                    data: [engineer1Data.responseTime, engineer2Data.responseTime],
                    backgroundColor: ['#4e73df', '#1cc88a'],
                    borderColor: ['#4e73df', '#1cc88a'],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Average Resolution Time Chart
        const resolutionTimeChartCtx = document.getElementById('resolutionTimeChart').getContext('2d');
        new Chart(resolutionTimeChartCtx, {
            type: 'bar',
            data: {
                labels: ['Engineer 1', 'Engineer 2'],
                datasets: [{
                    label: 'Average Resolution Time (hrs)',
                    data: [engineer1Data.resolutionTime, engineer2Data.resolutionTime],
                    backgroundColor: ['#4e73df', '#1cc88a'],
                    borderColor: ['#4e73df', '#1cc88a'],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Average Customer Feedback Chart
        const customerFeedbackChartCtx = document.getElementById('customerFeedbackChart').getContext('2d');
        new Chart(customerFeedbackChartCtx, {
            type: 'bar',
            data: {
                labels: ['Engineer 1', 'Engineer 2'],
                datasets: [{
                    label: 'Average Customer Feedback (1-5)',
                    data: [engineer1Data.customerFeedback, engineer2Data.customerFeedback],
                    backgroundColor: ['#4e73df', '#1cc88a'],
                    borderColor: ['#4e73df', '#1cc88a'],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>

</html>