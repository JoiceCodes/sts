    <?php
    session_start();
    $pageTitle = "New Cases";


    require_once "../fetch/new_cases.php";
    require_once "../fetch/engineers.php";
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
                    <?php include_once "../components/topbar.php" ?>
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

                        <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill"></i> Case assigned successfully! Go to <a href="ongoing_cases.php">On-going Cases</a>.
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table" id="table">
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
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($newCasesTable as $row) {
                                        $caseNumber = '<a href="#" class="case-number btn" data-case-id="' . $row["id"] . '" data-case-number="' . $row["case_number"] . '" data-case-owner="' . $row["case_owner"] . '">' . $row["case_number"] . '</a>';

                                        $action = '<button 
                                            type="button" 
                                            class="accept-button badge btn btn-success" 
                                            data-toggle="modal" 
                                            data-target="#assignCase"
                                            data-case-id="' . $row["id"] . '">
                                                <i class="bi bi-check"></i> 
                                                Assign Case
                                            </button>';

                                        echo "<tr>";
                                        echo "<td>" . $caseNumber . "</td>";
                                        echo "<td>" . $row["type"] . "</td>";
                                        echo "<td>" . $row["subject"] . "</td>";
                                        echo "<td>" . $row["product_group"] . "</td>";
                                        echo "<td>" . $row["product"] . "</td>";
                                        echo "<td>" . $row["product_version"] . "</td>";
                                        echo "<td>" . $row["severity"] . "</td>";
                                        echo "<td>" . $row["case_owner"] . "</td>";
                                        echo "<td>" . $row["company"] . "</td>";
                                        echo "<td>$action</td>";
                                        echo "</tr>";
                                    }
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

        <?php include_once "../modals/assign_case.php" ?>

        <!-- Bootstrap core JavaScript-->
        <script src="../vendor/jquery/jquery.min.js"></script>
        <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- Core plugin JavaScript-->
        <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

        <!-- Custom scripts for all pages-->
        <script src="../js/sb-admin-2.min.js"></script>
        <script src="../js/form_validation.js"></script>

        <!-- Page level plugins -->
        <!-- <script src="../vendor/chart.js/Chart.min.js"></script> -->

        <!-- Page level custom scripts -->
        <!-- <script src="../js/demo/chart-area-demo.js"></script>
        <script src="../js/demo/chart-pie-demo.js"></script> -->

        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>

        <script>
            new DataTable('#table');
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const assignCaseModal = document.getElementById("assignCase");
                const caseIdHidden = document.getElementById("caseId");

                document.querySelectorAll('.accept-button').forEach(item => {
                    item.addEventListener('click', function(event) {
                        caseIdHidden.value = this.getAttribute('data-case-id');
                    });
                });

                // acceptCaseModal.addEventListener("show.bs.modal", function(event) {
                //     // Get the button that triggered the modal
                //     const button = event.relatedTarget;

                //     // Get the case-id from the data attribute
                //     const caseId = button.getAttribute("data-case-id");

                //     // Set the value of the hidden input field with id "caseId"
                //     caseIdHidden.value = caseId;
                // });
            });
        </script>

    </body>

    </html>