<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>STS - Reset Password</title>
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
         /* Add styles for Bootstrap validation states if needed */
        .needs-validation .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }
        .invalid-feedback {
            display: none; /* Hidden by default */
            width: 100%;
            margin-top: 0.25rem;
            font-size: 80%;
            color: #e74a3b;
        }
        .was-validated .form-control:invalid {
            border-color: #e74a3b;
            /* Add SVG background if desired */
        }
       .was-validated .form-control:invalid ~ .invalid-feedback {
           display: block;
       }
        .was-validated .form-control:valid {
            border-color: #1cc88a;
             /* Add SVG background if desired */
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
                                        <h1 class="h4 text-gray-900 mb-2">Reset Your Password</h1>
                                        <p class="mb-4">Please enter the OTP sent to your email address and set your new password below.</p>
                                    </div>

                                    <?php if (isset($_GET["error"])): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?php
                                                // Example error messages based on codes
                                                $errorMessage = "An unknown error occurred.";
                                                switch ($_GET["error"]) {
                                                    case '1': $errorMessage = "Invalid or expired OTP."; break;
                                                    case '2': $errorMessage = "Passwords do not match."; break;
                                                    case '3': $errorMessage = "Password does not meet security requirements."; break;
                                                    case '4': $errorMessage = "Could not find user associated with this request."; break;
                                                    case '5': $errorMessage = "Database error during password update."; break;
                                                    // Add more specific errors as needed
                                                }
                                                echo htmlspecialchars($errorMessage);
                                            ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                              <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                     <?php if (isset($_GET["success"])): ?>
                                         <div class="alert alert-success alert-dismissible fade show" role="alert">
                                             Password has been reset successfully! You can now <a href="index.php">login</a>.
                                              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                             </button>
                                         </div>
                                     <?php endif; ?>


                                    <form class="user needs-validation" novalidate action="process/reset_password_with_otp.php" method="post">

                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                                        <div class="form-group">
                                             <label for="otpInput">One-Time Password (OTP)</label>
                                            <input type="text" name="otp" required class="form-control form-control-user"
                                                id="otpInput" placeholder="Enter OTP from email...">
                                            <div class="invalid-feedback">
                                                Please enter the OTP.
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="newPassword">New Password</label>
                                            <input type="password" name="new_password" required class="form-control form-control-user"
                                                id="newPassword" placeholder="Enter New Password">
                                             <div class="invalid-feedback" id="newPasswordFeedback">
                                                Please enter a new password.
                                            </div>
                                        </div>

                                        <div class="form-group">
                                             <label for="repeatNewPassword">Repeat New Password</label>
                                            <input type="password" name="repeat_password" required class="form-control form-control-user"
                                                id="repeatNewPassword" placeholder="Repeat New Password">
                                             <div class="invalid-feedback" id="repeatNewPasswordFeedback">
                                                Please repeat the new password. Passwords must match.
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">Set New Password</button>
                                    </form>
                                    <hr>
                                     <div class="text-center">
                                        <a class="small" href="index.php">Back to Login</a>
                                    </div>
                                </div>
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