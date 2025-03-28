<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
$pageTitle = "Solved Cases";
require_once "../fetch/solved_cases_table_user.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
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

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= $pageTitle ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="table">
                                    <thead>
                                        <tr>
                                            <th>Case Number</th>
                                            <th>Type</th>
                                            <th>Subject</th>
                                            <th>Contact Name</th>
                                            <th>Product Group</th>
                                            <th>Product</th>
                                            <th>Product Version</th>
                                            <th>Severity</th>
                                            <th>Company</th>
                                            <!-- <th>Last Modified</th> -->
                                            <th>Date & Time Opened</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($ongoingCasesTable as $row) {
                                            $caseNumber = '<a href="#" class="case-number btn" data-case-id="' . $row["id"] . '" data-case-number="' . $row["case_number"] . '" data-case-owner="' . $_SESSION["user_id"] . '">' . $row["case_number"] . '</a>';


                                            echo "<tr>";
                                            echo "<td>" . $caseNumber . "</td>";
                                            echo "<td>" . $row["type"] . "</td>";
                                            echo "<td>" . $row["subject"] . "</td>";
                                            echo "<td>" . $row["contact_name"] . "</td>";;
                                            echo "<td>" . $row["product_group"] . "</td>";
                                            echo "<td>" . $row["product"] . "</td>";
                                            echo "<td>" . $row["product_version"] . "</td>";
                                            echo "<td>" . $row["severity"] . "</td>";
                                            echo "<td>" . $row["company"] . "</td>";
                                            // echo "<td>" . $row["last_modified"] . "</td>";
                                            echo "<td>" . $row["datetime_opened"] . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

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

    <?php include_once "../modals/reopen_case.php" ?>

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

    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>

    <script>
        new DataTable('#table');
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const reopenCaseModal = document.getElementById("reopencase");
            const caseNumberHidden = document.getElementById("caseNumber");

            document.querySelectorAll('.reopen-case-btn').forEach(item => {
                item.addEventListener('click', function(event) {
                    caseNumberHidden.value = this.getAttribute("data-bs-case-number");
                });
            });
        });
    </script>
</body>

</html>