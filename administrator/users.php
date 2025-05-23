<?php
session_start();
$pageTitle = "Users";
require_once "../fetch/users.php";
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
                <?php include_once "../components/administrator_topbar.php" ?>
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

                    <div class="my-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newUser">+ New User</button>
                    </div>

                    <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> Account status updated successfully!
                        </div>
                    <?php elseif (isset($_GET["add"]) && $_GET["add"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> Account added successfully!
                        </div>
                    <?php elseif (isset($_GET["empty"]) && $_GET["empty"] === "1"): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-x-circle-fill"></i> Inputs are empty!
                        </div>
                    <?php elseif (isset($_GET["no_match"]) && $_GET["no_match"] === "1"): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-x-circle-fill"></i> Passwords do not match!
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><?= $pageTitle ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Username</th>
                                            <th>Added on</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($users as $row) {
                                            switch ($row["account_status"]) {
                                                case "Active":
                                                    $action = '<button 
                                            data-bs-user-id="' . $row["id"] . '"
                                            data-bs-user-status="' . $row["account_status"] . '"
                                            data-bs-action="deactivate"
                                            data-toggle="modal" 
                                            data-target="#userAction" 
                                            type="button" 
                                            class="user-action-btn btn btn-warning">
                                            <i class="bi bi-exclamation-circle-fill"></i> 
                                            Deactivate
                                            </button>';
                                                    break;
                                                case "Deactivated":
                                                    $action = '<button 
                                            data-bs-user-id="' . $row["id"] . '"
                                            data-bs-user-status="' . $row["account_status"] . '"
                                            data-bs-action="activate"
                                            data-toggle="modal" 
                                            data-target="#userAction" 
                                            type="button" 
                                            class="user-action-btn btn btn-success">
                                            <i class="bi bi-check-circle-fill"></i> 
                                            Activate
                                            </button>';
                                                    break;
                                            }

                                            echo "<tr>";
                                            echo "<td>" . $row["full_name"] . "</td>";
                                            echo "<td>" . $row["email"] . "</td>";
                                            echo "<td>" . $row["username"] . "</td>";
                                            echo "<td>" . $row["created_at"] . "</td>";
                                            echo "<td>$action</td>";
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

    <?php include_once "../modals/user_action.php" ?>
    <?php include_once "../modals/add_user.php" ?>



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
            const userActionModal = document.getElementById("userAction");
            const userActionModalTitle = document.getElementById("userActionModalTitle");
            const userActionModalBody = document.getElementById("userActionModalBody");
            const userIdHidden = document.getElementById("userId");
            const actionHidden = document.getElementById("action");

            document.querySelectorAll('.user-action-btn').forEach(item => {
                item.addEventListener('click', function(event) {
                    if (this.getAttribute('data-bs-user-status') === "Active") {
                        userActionModalTitle.textContent = "Account Deactivation";
                        userActionModalBody.textContent = "Are you sure you want to deactivate this user?";
                    } else if (this.getAttribute('data-bs-user-status') === "Deactivated") {
                        userActionModalTitle.textContent = "Account Activation";
                        userActionModalBody.textContent = "Are you sure you want to activate this user?";
                    }
                    userIdHidden.value = this.getAttribute("data-bs-user-id");
                    actionHidden.value = this.getAttribute("data-bs-action");
                });
            });
        });
    </script>
</body>

</html>