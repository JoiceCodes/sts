<?php
session_start();
$pageTitle = "Products";
require_once "../fetch/products.php";
require_once "../fetch/product_categories.php";
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
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProduct">+ New Product</button>
                    </div>

                    <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> Product status updated successfully!
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= $pageTitle ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="table">
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
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($products as $row) {
                                            if ($row["status"] == "Active") {
                                                $status = '<button 
                                        type="button" 
                                        class="product-action-btn btn border border-success text-success" 
                                        data-toggle="modal" 
                                        data-target="#productAction"
                                        data-bs-product-status="' . $row["status"] . '"
                                        data-bs-product-id="' . $row["id"] . '"
                                        data-bs-action="deactivate">' . $row["status"] . '
                                        </button>';
                                            } else if ($row["status"] == "Deactivated") {
                                                $status = '<button 
                                        type="button" 
                                        class="product-action-btn btn border border-warning text-warning" 
                                        data-toggle="modal" 
                                        data-target="#productAction"
                                        data-bs-product-status="' . $row["status"] . '"
                                        data-bs-product-id="' . $row["id"] . '"
                                        data-bs-action="activate">' . $row["status"] . '
                                        </button>';
                                            }

                                            echo "<tr>";
                                            echo "<td>" . $row["product_name"] . "</td>";
                                            echo "<td>" . $row["product_category"] . "</td>";
                                            echo "<td>" . $row["product_type"] . "</td>";
                                            echo "<td>" . $row["product_version"] . "</td>";
                                            echo "<td>" . $row["license_type"] . "</td>";
                                            echo "<td>" . $row["serial_number"] . "</td>";
                                            echo "<td>" . $row["supported_platforms"] . "</td>";
                                            echo "<td>" . $row["license_duration"] . "</td>";
                                            echo "<td>" . $row["created_at"] . "</td>";
                                            echo "<td>" . $status . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
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

    <?php include_once "../modals/add_product.php" ?>
    <?php include_once "../modals/supported_platforms.php" ?>
    <?php include_once "../modals/product_action.php" ?>


    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../js/form_validation.js"></script>

    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>

    <script>
        new DataTable('#table');
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const productActionModal = document.getElementById("productAction");
            const productActionModalTitle = document.getElementById("productActionModalTitle");
            const productActionModalBody = document.getElementById("productActionModalBody");
            const productIdHidden = document.getElementById("productId");
            const actionHidden = document.getElementById("action");

            document.querySelectorAll('.product-action-btn').forEach(item => {
                item.addEventListener('click', function(event) {
                    if (this.getAttribute('data-bs-product-status') === "Active") {
                        productActionModalTitle.textContent = "Product Deactivation";
                        productActionModalBody.textContent = "Are you sure you want to deactivate this product?";
                    } else if (this.getAttribute('data-bs-product-status') === "Deactivated") {
                        productActionModalTitle.textContent = "Product Activation";
                        productActionModalBody.textContent = "Are you sure you want to activate this product?";
                    }
                    productId.value = this.getAttribute("data-bs-product-id");
                    actionHidden.value = this.getAttribute("data-bs-action");
                });
            });
        });
    </script>
</body>

</html>