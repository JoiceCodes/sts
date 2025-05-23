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

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        body{
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }
    </style>

</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-6 border bg-login-image d-flex justify-content-center align-items-center"><img src="brand.jpg" class="img-fluid p-3" alt=""></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Welcome to i-Secure Networks and Business Solutions, Inc.!</h1>
                                </div>

                                <?php if (isset($_GET["password_reset"]) && $_GET["password_reset"] === "1"): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="bi bi-check-circle-fill"></i> Your new password has been set successfully!
                                    </div>
                                <?php elseif (isset($_GET["register"]) && $_GET["register"] === "1"): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="bi bi-check-circle-fill"></i> You have successfully created your account!
                                    </div>
                                <?php endif; ?>

                                <form class="user needs-validation" novalidate action="process/login.php" method="post">
                                    <?php if (isset($_GET["rate"]) && $_GET["rate"] === "1"): ?>
                                        <input type="hidden" name="rate" value="<?= htmlspecialchars($_GET["rate"]) // Added htmlspecialchars for security ?>">
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label for="username">Username:</label>
                                        <input type="text" name="username" class="form-control form-control-user"
                                               id="username" aria-describedby="usernameHelp"
                                               placeholder="Enter Username..." required>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword">Password:</label>
                                        <input type="password" name="password" class="form-control form-control-user"
                                               id="exampleInputPassword" placeholder="Password" required>
                                    </div>
                                    <div class="form-group">
                                        
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-user btn-block">Login</button>
                                    <hr>
                                </form>
                                <div class="text-center">
                                        <a class="small" href="register.php">Create an Account!</a>
                                    </div>
                                <div class="text-center">
                                    <a class="small" href="forgot-password.php">Forgot Password?</a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

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