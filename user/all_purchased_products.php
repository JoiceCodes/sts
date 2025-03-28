<?php 
    session_start();
    // require_once "../fetch/solved_cases.php";
    $pageTitle = "All Purchased Products";
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

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Case Number</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Product Group</th>
                                    <th>Product</th>
                                    <th>Product Version</th>
                                    <th>Severity</th>
                                    <th>Case Owner</th>
                                    <th>Company</th>
                                    <th>Last Modified</th>
                                    <th>Date & Time Opened</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    // foreach ($solvedCasesTable as $row) {
                                    //     $action = '<button type="button" class="badge btn btn-warning"><i class="bi bi-exclamation"></i> Reopen Case</button>';
                                        
                                    //     echo "<tr>";
                                    //     echo "<td>" . $row["case_number"] . "</td>";
                                    //     echo "<td>" . $row["type"] . "</td>";
                                    //     echo "<td>" . $row["subject"] . "</td>";
                                    //     echo "<td>" . $row["product_group"] . "</td>";
                                    //     echo "<td>" . $row["product"] . "</td>";
                                    //     echo "<td>" . $row["product_version"] . "</td>";
                                    //     echo "<td>" . $row["severity"] . "</td>";
                                    //     echo "<td>" . $row["case_owner"] . "</td>";
                                    //     echo "<td>" . $row["company"] . "</td>";
                                    //     echo "<td>" . $row["last_modified"] . "</td>";
                                    //     echo "<td>" . $row["datetime_opened"] . "</td>";
                                    //     echo "<td>$action</td>";
                                    //     echo "</tr>";
                                    // }
                                ?>
                            </tbody>
                        </table>
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
    <script src="../js/demo/chart-area-demo.js"></script>
    <script src="../js/demo/chart-pie-demo.js"></script>
</body>

</html>