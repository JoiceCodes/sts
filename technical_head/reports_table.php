<?php
session_start();
$pageTitle = "Reports";
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
                <?php include_once "../components/topbar.php" ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                        
                        <div class="dropdown">
                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Dropdown link
                            </a>

                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Reports Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Comparison of IT Engineers</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="comparisonTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Engineer Name</th>
                                            <th>Performance (Star Rating)</th>
                                            <th>Total Cases Solved</th>
                                            <th>Availability</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Engineer 1 -->
                                        <tr>
                                            <td>John Doe</td>
                                            <td>
                                                <!-- Star Rating for Performance -->
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star-half-alt"></i>
                                                <i class="far fa-star"></i>
                                            </td>
                                            <td>150</td>
                                            <td>Available</td>
                                        </tr>
                                        <!-- Engineer 2 -->
                                        <tr>
                                            <td>Jane Smith</td>
                                            <td>
                                                <!-- Star Rating for Performance -->
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="far fa-star"></i>
                                            </td>
                                            <td>180</td>
                                            <td>Busy (5 Tasks)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- End of Reports Table -->
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="https://cdn.datatables.net/2.2.2/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.min.js"></script>

    <!-- Initialize DataTable -->
    <script>
        $(document).ready(function() {
            $('#comparisonTable').DataTable();
        });
    </script>

</body>

</html>