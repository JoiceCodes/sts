<?php 
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php?rate=1");
    exit;
}

if (isset($_GET["id"])) {
    $engineerId = base64_decode($_GET["id"]);

    $getEngineer = mysqli_prepare($connection, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($getEngineer, "i", $engineerId);
    mysqli_stmt_execute($getEngineer);
    $getEngineerResult = mysqli_stmt_get_result($getEngineer);
    if (mysqli_num_rows($getEngineerResult) > 0) {
        $row = mysqli_fetch_assoc($getEngineerResult);
        $engineerName = $row["full_name"];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>i-Secure Networks and Business Solutions, Inc.</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .star-rating {
            display: flex;
            justify-content: center;
            font-size: 2em;
        }

        .star-rating input[type="radio"] {
            display: none;
        }

        .star-rating label {
            color: lightgray;
            cursor: pointer;
        }

        .star-rating input[type="radio"]:checked ~ label {
            color: gold;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: gold;
        }
    </style>

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 border bg-login-image d-flex justify-content-center align-items-center">
                                <img src="brand.jpg" class="img-fluid p-3" alt="">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Engineer Performance Rating</h1>
                                    </div>
                                    <form class="user needs-validation" novalidate action="process/rate_engineer.php" method="post">
                                        <input type="hidden" name="engineer_id" id="engineerId" value="<?= $engineerId ?>">
                                        <div class="form-group">
                                            <input type="text" value="<?= $engineerName ?>" class="form-control form-control-user" id="username" readonly>
                                        </div>
                                        <div class="form-group">
                                            <!-- Star Rating -->
                                            <label for="rating">Rate the Engineer:</label>
                                            <div class="star-rating">
                                                <input type="radio" id="star5" name="rating" value="5">
                                                <label for="star5">&#9733;</label>
                                                <input type="radio" id="star4" name="rating" value="4">
                                                <label for="star4">&#9733;</label>
                                                <input type="radio" id="star3" name="rating" value="3">
                                                <label for="star3">&#9733;</label>
                                                <input type="radio" id="star2" name="rating" value="2">
                                                <label for="star2">&#9733;</label>
                                                <input type="radio" id="star1" name="rating" value="1">
                                                <label for="star1">&#9733;</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">Submit</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (() => {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            const forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>

</body>

</html>
