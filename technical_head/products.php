<?php
session_start();
$pageTitle = "Products";
require_once "../fetch/products.php";
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
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProduct">+ New Product</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Version</th>
                                    <th>License Type</th>
                                    <th>Serial Number</th>
                                    <th>Support Platform(s)</th>
                                    <th>License Duration</th>
                                    <th>Added on</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($products as $row) {
                                    $viewPlatforms = '<button 
                                    data-bs-product-id="' . $row["id"] . '"
                                    data-bs-product-name="' . $row["product_name"] . '"
                                    type="button" 
                                    class="view-button btn btn-primary"
                                    data-toggle="modal"
                                    data-target="#supportedPlatforms">
                                    <i class="bi bi-eye-fill"></i> 
                                    View
                                    </button>';

                                    echo "<tr>";
                                    echo "<td>" . $row["product_name"] . "</td>";
                                    echo "<td>" . $row["product_category"] . "</td>";
                                    echo "<td>" . $row["product_type"] . "</td>";
                                    echo "<td>" . $row["product_version"] . "</td>";
                                    echo "<td>" . $row["license_type"] . "</td>";
                                    echo "<td>" . $row["serial_number"] . "</td>";
                                    echo "<td>" . $viewPlatforms . "</td>";
                                    echo "<td>" . $row["license_duration"] . "</td>";
                                    echo "<td>" . $row["created_at"] . "</td>";
                                    echo "<td></td>";
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

    <?php include_once "../modals/add_product.php" ?>
    <?php include_once "../modals/supported_platforms.php" ?>


    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../js/form_validation.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const assignCaseModal = document.getElementById("supportedPlatforms");
            const modalTitle = document.getElementById("productName");

            document.querySelectorAll('.view-button').forEach(item => {
                item.addEventListener('click', function(event) {
                    modalTitle.textContent = this.getAttribute('data-bs-product-name');
                });
            });
        });
    </script>
</body>

</html>