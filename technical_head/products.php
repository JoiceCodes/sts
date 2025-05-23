<?php
session_start();
$pageTitle = "Products";
require_once "../fetch/products.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
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
                                            <th>ID</th>
                                            <th>Product Group</th>
                                            <th>Product Type</th>
                                            <th>Version</th>
                                            <th>License Type</th>
                                            <th>Serial Number</th>
                                            <th>Status</th>
                                            <th>Added on</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!empty($products)) { // Check if $products is not empty
                                            foreach ($products as $row) {
                                                $status = ''; // Initialize status variable
                                                if (isset($row["status"])) { // Check if status exists
                                                    if ($row["status"] == "Active") {
                                                        $status = '<button
                                                        type="button"
                                                        class="product-action-btn btn border border-success text-success"
                                                        data-toggle="modal"
                                                        data-target="#productAction"
                                                        data-bs-product-status="' . htmlspecialchars($row["status"]) . '"
                                                        data-bs-product-id="' . htmlspecialchars($row["id"]) . '"
                                                        data-bs-action="deactivate">' . htmlspecialchars($row["status"]) . '
                                                        </button>';
                                                    } else if ($row["status"] == "Deactivated") {
                                                        $status = '<button
                                                        type="button"
                                                        class="product-action-btn btn border border-warning text-warning"
                                                        data-toggle="modal"
                                                        data-target="#productAction"
                                                        data-bs-product-status="' . htmlspecialchars($row["status"]) . '"
                                                        data-bs-product-id="' . htmlspecialchars($row["id"]) . '"
                                                        data-bs-action="activate">' . htmlspecialchars($row["status"]) . '
                                                        </button>';
                                                    } else {
                                                        // Handle other statuses if necessary, or just display the status text
                                                        $status = htmlspecialchars($row["status"]);
                                                    }
                                                } else {
                                                    $status = "N/A"; // Display N/A if status is not set
                                                }


                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row["id"] ?? 'N/A') . "</td>"; // Display ID
                                                echo "<td>" . htmlspecialchars($row["product_group"] ?? 'N/A') . "</td>"; // Display Product Group
                                                echo "<td>" . htmlspecialchars($row["product_type"] ?? 'N/A') . "</td>"; // Display Product Type
                                                echo "<td>" . htmlspecialchars($row["product_version"] ?? 'N/A') . "</td>"; // Display Version
                                                echo "<td>" . htmlspecialchars($row["license_type"] ?? 'N/A') . "</td>"; // Display License Type
                                                echo "<td>" . htmlspecialchars($row["serial_number"] ?? 'N/A') . "</td>"; // Display Serial Number
                                                echo "<td>" . $status . "</td>"; // Display Status (button or text)
                                                echo "<td>" . htmlspecialchars($row["created_at"] ?? 'N/A') . "</td>"; // Display Created At (Added on)
                                                echo "</tr>";
                                            }
                                        } else {
                                            // Optional: Display a row indicating no products found
                                            echo '<tr><td colspan="8" class="text-center">No products found.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once "../components/footer.php" ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include_once "../modals/logout.php" ?>

    <?php include_once "../modals/add_product.php" ?>
    <?php include_once "../modals/supported_platforms.php" ?>
    <?php include_once "../modals/product_action.php" ?>


    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

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
                    productIdHidden.value = this.getAttribute("data-bs-product-id"); // Corrected variable name
                    actionHidden.value = this.getAttribute("data-bs-action");
                });
            });
        });
    </script>
</body>

</html>