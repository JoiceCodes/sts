<?php
session_start();
require_once "../config/database.php"; // Adjust path if needed
$pageTitle = "Settings";

// --- Administrator Access Check ---
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Administrator') {
    // Optional: Set a flash message for the user
    // $_SESSION['error_message'] = "Access Denied: You do not have permission to view this page.";
    header('Location: home.php'); // Redirect non-admins
    exit;
}

$settings = [];
$success_message = '';
$error_message = '';

// --- Load Existing Settings Function ---
function load_settings($connection)
{
    $settings = [];
    if (isset($connection) && $connection instanceof mysqli) {
        $result = $connection->query("SELECT setting_key, setting_value FROM settings");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            $result->free();
        } else {
            error_log("Error loading settings: " . $connection->error);
            // Set an error message for the user if desired
        }
    }
    // Ensure default keys exist even if not in DB yet
    $default_keys = ['smtp_host', 'smtp_port', 'smtp_secure', 'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name'];
    foreach ($default_keys as $key) {
        if (!isset($settings[$key])) {
            $settings[$key] = ''; // Default to empty string
        }
    }
    // Pre-fill Gmail defaults if empty
    if (empty($settings['smtp_host'])) $settings['smtp_host'] = 'smtp.gmail.com';
    if (empty($settings['smtp_port'])) $settings['smtp_port'] = '587';
    if (empty($settings['smtp_secure'])) $settings['smtp_secure'] = 'TLS'; // Often represented as PHPMailer::ENCRYPTION_STARTTLS

    return $settings;
}

// --- Save Settings Function ---
function save_setting($connection, $key, $value)
{
    if (isset($connection) && $connection instanceof mysqli) {
        // Use REPLACE INTO or INSERT ... ON DUPLICATE KEY UPDATE for simplicity
        $stmt = $connection->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        if ($stmt) {
            $stmt->bind_param("ss", $key, $value);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } else {
            error_log("Error preparing save setting statement for key '$key': " . $connection->error);
            return false;
        }
    }
    return false;
}

// --- Handle Form Submission (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($connection) && $connection instanceof mysqli) {
        $settings_to_save = [
            'smtp_username'   => trim($_POST['smtp_username'] ?? ''),
            // Only update password if a new one is provided
            'smtp_password'   => (!empty(trim($_POST['smtp_password']))) ? trim($_POST['smtp_password']) : null,
            'smtp_from_email' => trim($_POST['smtp_from_email'] ?? ''),
            'smtp_from_name'  => trim($_POST['smtp_from_name'] ?? ''),
            // Optionally save host/port/secure if you make them editable
            'smtp_host'       => trim($_POST['smtp_host'] ?? 'smtp.gmail.com'),
            'smtp_port'       => trim($_POST['smtp_port'] ?? '587'),
            'smtp_secure'     => trim($_POST['smtp_secure'] ?? 'TLS')
        ];

        $all_saved = true;
        mysqli_begin_transaction($connection); // Start transaction

        foreach ($settings_to_save as $key => $value) {
            // Special handling for password: don't save if it wasn't provided in the form
            if ($key === 'smtp_password' && $value === null) {
                // Skip saving password if the input field was empty
                // It will retain its previous value in the database
                continue;
            }
            if (!save_setting($connection, $key, $value)) {
                $all_saved = false;
                $error_message = "Failed to save setting: " . htmlspecialchars($key);
                error_log("Failed transaction saving setting: " . $key);
                break; // Stop saving if one fails
            }
        }

        if ($all_saved) {
            mysqli_commit($connection); // Commit transaction
            $success_message = "SMTP settings saved successfully!";
        } else {
            mysqli_rollback($connection); // Rollback on error
            // Error message is already set
        }
    } else {
        $error_message = "Database connection error. Settings not saved.";
        error_log($error_message);
    }
}

// --- Load current settings for display ---
$settings = load_settings($connection);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php"; // Contains <meta>, <title> using $pageTitle, CSS links 
    ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php"; // Contains the sidebar navigation 
        ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/administrator_topbar.php"; // Or your generic topbar 
                ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                    </div>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($success_message) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error_message) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>


                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Outgoing Email (SMTP) Configuration</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Configure the settings used by the system to send emails (e.g., notifications). For Gmail, use your email address and generate an <a href="https://myaccount.google.com/apppasswords" target="_blank">App Password</a>.</p>

                            <form action="settings.php" method="POST">
                                <?php // echo generate_csrf_token_input(); 
                                ?>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="smtp_host">SMTP Host</label>
                                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host']) ?>" readonly>
                                        <small class="form-text text-muted">Typically 'smtp.gmail.com' for Gmail.</small>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="smtp_port">SMTP Port</label>
                                        <input type="text" class="form-control" id="smtp_port" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port']) ?>" readonly>
                                        <small class="form-text text-muted">Usually 587 for TLS.</small>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="smtp_secure">Encryption</label>
                                        <input type="text" class="form-control" id="smtp_secure" name="smtp_secure" value="<?= htmlspecialchars($settings['smtp_secure']) ?>" readonly>
                                        <small class="form-text text-muted">Usually 'TLS'.</small>
                                    </div>
                                </div>

                                <hr>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="smtp_username">SMTP Username</label>
                                        <input type="email" class="form-control" id="smtp_username" name="smtp_username" value="<?= htmlspecialchars($settings['smtp_username']) ?>" required placeholder="e.g., your.email@gmail.com">
                                        <small class="form-text text-muted">Your full Gmail address.</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="smtp_password">SMTP Password (App Password)</label>
                                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" placeholder="Enter new App Password to update">
                                        <small class="form-text text-muted">Leave blank to keep the current password. <a href="https://myaccount.google.com/apppasswords" target="_blank">Generate here</a>.</small>
                                    </div>
                                </div>

                                <hr>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="smtp_from_email">'From' Email Address</label>
                                        <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" value="<?= htmlspecialchars($settings['smtp_from_email']) ?>" required placeholder="Usually same as Username">
                                        <small class="form-text text-muted">Email address system emails will appear to be sent from.</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="smtp_from_name">'From' Name</label>
                                        <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" value="<?= htmlspecialchars($settings['smtp_from_name']) ?>" required placeholder="e.g., Support Team">
                                        <small class="form-text text-muted">The name system emails will appear to be sent from.</small>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Save SMTP Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once "../components/footer.php"; ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include_once "../modals/logout.php"; ?>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="../js/sb-admin-2.min.js"></script>

    <?php
    // Clear flash messages after displaying them
    if (isset($_SESSION['success_message'])) unset($_SESSION['success_message']);
    if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);
    ?>

</body>

</html>