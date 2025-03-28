<?php 
    session_start();
    $pageTitle = "Knowledge Base";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <style>
        .card-header {
            background-color: #4e73df;
            color: white;
            font-weight: bold;
        }

        .card-body {
            background-color: #f8f9fc;
        }

        .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
            transition: 0.3s ease-in-out;
        }

        .card-header a {
            color: white;
            text-decoration: none;
        }

        .card-header a:hover {
            color: #f8f9fc;
            text-decoration: underline;
        }

        .list-group-item a {
            color: #5a5c69;
            text-decoration: none;
        }

        .list-group-item a:hover {
            color: #007bff;
            text-decoration: underline;
        }

        .container-fluid {
            padding-top: 20px;
        }

        .scroll-to-top {
            background-color: #007bff;
            color: white;
        }
    </style>
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
                    </div>

                    <!-- Cards Section -->
                    <div class="row">
                        <!-- Progress Cards -->
                        <div class="col-md-3 mb-4">
                            <div class="card">
                                <div class="card-header">Progress</div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><a href="https://www.progress.com/sitefinity" target="_blank">Sitefinity</a></li>
                                        <li class="list-group-item"><a href="https://www.telerik.com/kendo-ui" target="_blank">Kendo UI</a></li>
                                        <li class="list-group-item"><a href="https://www.whatsupgold.com/" target="_blank">WhatsUp Gold</a></li>
                                        <li class="list-group-item"><a href="https://www.ipswitch.com/moveit" target="_blank">MOVEit</a></li>
                                        <li class="list-group-item"><a href="https://www.progress.com/openedge" target="_blank">OpenEdge</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Neverfail Cards -->
                        <div class="col-md-3 mb-4">
                            <div class="card">
                                <div class="card-header">Neverfail</div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><a href="https://www.neverfail.com/continuity-engine/" target="_blank">Continuity Engine</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sophos Cards -->
                        <div class="col-md-3 mb-4">
                            <div class="card">
                                <div class="card-header">Sophos</div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><a href="https://www.sophos.com/en-us/products/intercept-x.aspx" target="_blank">Intercept X</a></li>
                                        <li class="list-group-item"><a href="https://www.sophos.com/en-us/products/xg-firewall.aspx" target="_blank">XG Firewall</a></li>
                                        <li class="list-group-item"><a href="https://www.sophos.com/en-us/products/sophos-central.aspx" target="_blank">Sophos Central</a></li>
                                        <li class="list-group-item"><a href="https://www.sophos.com/en-us/products/phish-threat.aspx" target="_blank">Phish Threat</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Trustwave Cards -->
                        <div class="col-md-3 mb-4">
                            <div class="card">
                                <div class="card-header">Trustwave</div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><a href="https://www.trustwave.com/en-us/services/managed-security-services/" target="_blank">Managed Security Services</a></li>
                                        <li class="list-group-item"><a href="https://www.trustwave.com/en-us/services/spiderlabs/" target="_blank">SpiderLabs</a></li>
                                        <li class="list-group-item"><a href="https://www.trustwave.com/en-us/services/secure-email-gateway/" target="_blank">Secure Email Gateway</a></li>
                                        <li class="list-group-item"><a href="https://www.trustwave.com/en-us/services/database-security/" target="_blank">Database Security</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- SecPod Cards -->
                        <div class="col-md-3 mb-4">
                            <div class="card">
                                <div class="card-header">SecPod</div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><a href="https://www.secpod.com/sanernow/" target="_blank">SanerNow</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /.row -->
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
    <script src="../js/demo/chart-area-demo.js"></script>
    <script src="../js/demo/chart-pie-demo.js"></script>
</body>

</html>
