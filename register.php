<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>STS - Register</title>
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
        /* Add styles for Bootstrap validation states if not fully covered by sb-admin-2 */
        .needs-validation .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }
         /* Ensure feedback text is visible */
        .invalid-feedback {
            display: none; /* Hidden by default */
            width: 100%;
            margin-top: 0.25rem;
            font-size: 80%;
            color: #e74a3b; /* Default Bootstrap danger color */
        }
        /* Styles for invalid state */
        .was-validated .form-control:invalid {
            border-color: #e74a3b;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23e74a3b' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23e74a3b' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
       .was-validated .form-control:invalid ~ .invalid-feedback {
           display: block; /* Show feedback when invalid and validated */
       }
       /* Styles for valid state */
        .was-validated .form-control:valid {
            border-color: #1cc88a; /* Default Bootstrap success color */
             background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%231cc88a' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

    </style>

</head>

<body>

    <div class="container">

        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-lg-5 border bg-login-image d-flex justify-content-center align-items-center"><img src="brand.jpg" class="img-fluid p-3" alt=""></div>
                    <div class="col-lg-7">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                            </div>

                            <?php if (isset($_GET["error"])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php
                                        // Example server-side error messages
                                        $serverError = "An error occurred during registration.";
                                        if ($_GET["error"] === "1") $serverError = "Passwords do not match. (Server Check)";
                                        if ($_GET["error"] === "2") $serverError = "Username or Email already exists.";
                                        if ($_GET["error"] === "3") $serverError = "Password does not meet server security requirements.";
                                        // Add more error codes as needed from your process/register.php script
                                        echo htmlspecialchars($serverError);
                                    ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                             <?php if (isset($_GET["success"])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Registration successful! Please <a href="index.php" class="alert-link">login</a>.
                                     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                         <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>


                            <form class="user needs-validation" novalidate action="process/register.php" method="post">
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <label for="exampleFirstName">First Name</label>
                                        <input type="text" name="first_name" required class="form-control form-control-user" id="exampleFirstName"
                                            placeholder="First Name">
                                        <div class="invalid-feedback">
                                            Please enter your first name.
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="exampleLastName">Last Name</label>
                                        <input type="text" name="last_name" required class="form-control form-control-user" id="exampleLastName"
                                            placeholder="Last Name">
                                        <div class="invalid-feedback">
                                            Please enter your last name.
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputUsername">Username</label>
                                    <input type="text" name="username" required class="form-control form-control-user" id="exampleInputUsername"
                                        placeholder="Username">
                                    <div class="invalid-feedback">
                                        Please choose a username.
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail">Email Address</label>
                                    <input type="email" name="email" required class="form-control form-control-user" id="exampleInputEmail"
                                        placeholder="Email Address">
                                    <div class="invalid-feedback">
                                        Please enter a valid email address.
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <label for="exampleInputPassword">Password</label>
                                        <input type="password" name="password" required class="form-control form-control-user"
                                            id="exampleInputPassword" placeholder="Password" aria-describedby="passwordHelpBlock">
                                        <div class="invalid-feedback" id="passwordFeedback">
                                            Password is required and must meet complexity requirements.
                                        </div>
                                        </div>
                                    <div class="col-sm-6">
                                        <label for="exampleRepeatPassword">Repeat Password</label>
                                        <input type="password" name="repeat_password" required class="form-control form-control-user"
                                            id="exampleRepeatPassword" placeholder="Repeat Password">
                                        <div class="invalid-feedback" id="repeatPasswordFeedback">
                                            Please repeat the password. Passwords must match.
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block">Register Account</button>
                            </form>
                            <hr>
                            <div class="text-center">
                                <a class="small" href="forgot-password.php">Forgot Password?</a>
                            </div>
                            <div class="text-center">
                                <a class="small" href="index.php">Already have an account? Login!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>

    <script src="js/form_validation.js"></script>

</body>

</html>