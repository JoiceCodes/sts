<?php 
    session_start();
    // require_once "../fetch/solved_cases.php";
    $pageTitle = "About";
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
                <?php include_once "../components/administrator_topbar.php" ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                    </div>

                    <!-- About Section -->
                    <div class="row">
                        <div class="col-lg-6">
                            <h3 class="h4 text-gray-800 mb-4">Our Mission</h3>
                            <p>
                                At i-Secure Networks and Business Solutions, we are dedicated to providing top-notch cybersecurity and IT solutions to help businesses safeguard their data, networks, and operations. Our mission is to empower companies by ensuring they can operate in a safe and secure digital environment.
                            </p>
                            <p>
                                We believe that cybersecurity is essential for the modern business world, and our goal is to provide tailored solutions that meet the unique needs of each client.
                            </p>
                        </div>
                        <div class="col-lg-6">
                            <h3 class="h4 text-gray-800 mb-4">Our Values</h3>
                            <ul>
                                <li><strong>Integrity:</strong> We uphold the highest standards of honesty and fairness in all our actions.</li>
                                <li><strong>Innovation:</strong> We strive for continuous improvement and innovative solutions in the cybersecurity space.</li>
                                <li><strong>Collaboration:</strong> We work closely with our clients, offering personalized services that meet their specific needs.</li>
                                <li><strong>Excellence:</strong> We are committed to providing exceptional service and solutions that exceed client expectations.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Contact Section -->
                    <div class="row mt-5">
                        <div class="col-lg-12">
                            <h3 class="h4 text-gray-800 mb-4">Contact Us</h3>
                            <p>If you have any questions or would like to learn more about our services, feel free to reach out to us!</p>
                            <ul>
                                <li><strong>Email:</strong> support@isecure.com</li>
                                <li><strong>Phone:</strong> +1 (123) 456-7890</li>
                                <li><strong>Address:</strong> 1018 Cityland Shaw Tower, Shaw Blvd. corner St. Francis St., Mandaluyong, Philippines</li>
                            </ul>
                        </div>
                    </div>

                    <!-- End About Section -->
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
