<?php
session_start();
$pageTitle = "Gmail App Password Settings";

// Include database configuration
require_once "../config/database.php";

// Initialize variables for form values and messages
$gmailAppPassword = "";
$message = "";
$messageType = ""; // success or error

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gmailAppPassword = $_POST["gmail_app_password"];

    // Basic validation (you should add more robust validation)
    if (empty($gmailAppPassword)) {
        $message = "Gmail App Password is required.";
        $messageType = "error";
    } else {
        // Use the $connection variable from database.php
        if (!$connection) {
            $message = "Database connection failed.";
            $messageType = "error";
        } else {
            // Prepare and execute SQL query to insert or update the password
            $stmt = $connection->prepare("REPLACE INTO gmail_app_password (id, user_id, app_password) VALUES (1, ?, ?)");
            $stmt->bind_param("ss", $_SESSION["user_id"],$gmailAppPassword); // "s" indicates a string

            if ($stmt->execute()) {
                $message = "Gmail App Password saved successfully.";
                $messageType = "success";
            } else {
                $message = "Error saving password: " . $stmt->error;
                $messageType = "error";
            }

        }
    }
} else {
    // Load password from database if it exists.
    if ($connection) {
        $result = $connection->query("SELECT app_password FROM gmail_app_password WHERE id = 1");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $gmailAppPassword = $row["app_password"];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <style>
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            background-color: #ffffff;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-container label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            color: #555;
        }

        .form-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-container input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-container .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .form-container .btn-primary:hover {
            background-color: #0056b3;
        }

        .form-container .alert {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 6px;
            font-size: 16px;
        }

        .form-container .alert-success {
            background-color: #e6f7e9;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .form-container .alert-danger {
            background-color: #fce8e6;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        .form-container .app-password-link {
            text-align: center;
            margin-top: 15px;
        }

        .form-container .app-password-link a {
            color: #007bff;
            text-decoration: none;
        }

        .form-container .app-password-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php" ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/user_topbar.php" ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-container">
                                <h2>Gmail App Password Configuration</h2>
                                <?php if (!empty($message)) : ?>
                                    <div class="alert alert-<?= ($messageType == 'success') ? 'success' : 'danger'; ?>">
                                        <?= $message ?>
                                    </div>
                                <?php endif; ?>
                                <form method="post">
                                    <div class="form-group">
                                        <label for="gmail_app_password">Gmail App Password:</label>
                                        <input type="password" class="form-control" id="gmail_app_password" name="gmail_app_password" value="<?= $gmailAppPassword ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Password</button>
                                </form>
                                <div class="app-password-link">
                                    <a href="https://myaccount.google.com/apppasswords" target="_blank">Generate Gmail App Password</a>
                                </div>
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

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="../js/sb-admin-2.min.js"></script>

    <script src="../vendor/chart.js/Chart.min.js"></script>

    <script src="../js/demo/chart-area-demo.js"></script>
    <script src="../js/demo/chart-pie-demo.js"></script>
</body>

</html>