<?php
session_start();
require_once "../config/database.php";
$pageTitle = "Settings";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Administrator') {
    header('Location: home.php');
    exit;
}

$admin_user_id = $_SESSION['user_id'] ?? null;
if (!$admin_user_id) {
    error_log("CRITICAL: Admin user_id not found in session on settings page.");
    $error_message = "Error: Your user session is invalid. Cannot save settings.";
}

$user_email = $_SESSION['user_email'] ?? null;
$settings = [];
$success_message = '';
$error_message = $error_message ?? '';

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
        }
    }
    $default_keys = ['smtp_host', 'smtp_port', 'smtp_secure', 'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name'];
    foreach ($default_keys as $key) {
        if (!isset($settings[$key])) {
            $settings[$key] = '';
        }
    }
    if (empty($settings['smtp_host'])) $settings['smtp_host'] = 'smtp.gmail.com';
    if (empty($settings['smtp_port'])) $settings['smtp_port'] = '587';
    if (empty($settings['smtp_secure'])) $settings['smtp_secure'] = 'TLS';

    return $settings;
}

function save_setting($connection, $key, $value)
{
    if (isset($connection) && $connection instanceof mysqli) {
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

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error_message) {
    if (isset($connection) && $connection instanceof mysqli && $admin_user_id) {

        $submitted_password = trim($_POST['smtp_password'] ?? '');
        $new_password_provided = !empty($submitted_password);

        $settings_to_save = [
            'smtp_username'   => trim($_POST['smtp_username'] ?? ''),
            'smtp_password'   => $new_password_provided ? $submitted_password : null,
            'smtp_from_email' => trim($_POST['smtp_from_email'] ?? ''),
            'smtp_from_name'  => trim($_POST['smtp_from_name'] ?? ''),
            'smtp_host'       => trim($_POST['smtp_host'] ?? 'smtp.gmail.com'),
            'smtp_port'       => trim($_POST['smtp_port'] ?? '587'),
            'smtp_secure'     => trim($_POST['smtp_secure'] ?? 'TLS')
        ];

        $all_saved_successfully = true;
        mysqli_begin_transaction($connection);

        foreach ($settings_to_save as $key => $value) {
            if ($key === 'smtp_password' && $value === null) {
                continue;
            }
            if (!save_setting($connection, $key, $value)) {
                $all_saved_successfully = false;
                $error_message = "Failed to save setting: " . htmlspecialchars($key);
                error_log("Failed transaction saving setting: " . $key . " Error: " . $connection->error);
                break;
            }
        }

        if ($all_saved_successfully && $new_password_provided) {
            $stmt_app_pass = $connection->prepare(
                "INSERT INTO gmail_app_password (user_id, app_password) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE app_password = VALUES(app_password)"
            );

            if ($stmt_app_pass) {
                $stmt_app_pass->bind_param("is", $admin_user_id, $submitted_password);
                if (!$stmt_app_pass->execute()) {
                    $all_saved_successfully = false;
                    $error_message = "Failed to save the App Password reference for your user.";
                    error_log("Failed to save to gmail_app_password for user_id {$admin_user_id}: " . $stmt_app_pass->error);
                }
                $stmt_app_pass->close();
            } else {
                $all_saved_successfully = false;
                $error_message = "Database error preparing to save the App Password reference.";
                error_log("Error preparing gmail_app_password statement: " . $connection->error);
            }
        }

        if ($all_saved_successfully) {
            mysqli_commit($connection);
            $success_message = "SMTP settings saved successfully!";
            $_POST['smtp_password'] = '';
        } else {
            mysqli_rollback($connection);
            if (empty($error_message)) {
                $error_message = "An unexpected error occurred while saving settings. Changes were rolled back.";
            }
        }
    } elseif (!$admin_user_id) {
        $error_message = "Error: Your user session is invalid. Cannot save settings.";
    } else {
        $error_message = "Database connection error. Settings not saved.";
        error_log($error_message . (isset($connection) ? " Errno: " . $connection->connect_errno : " Connection object null"));
    }
}

$settings = load_settings($connection);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php"; ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/administrator_topbar.php"; ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                    </div>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($success_message) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
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
                            <p class="text-muted small">Configure the settings used by the system to send emails (e.g., notifications). For Gmail, use your email address and generate an <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer">App Password</a>.</p>
                            <p class="text-info small"><i class="fas fa-info-circle"></i> Saving these settings will update the global SMTP configuration used by the system. If you provide a new App Password, it will be stored securely linked to your user account in a separate reference table.</p>

                            <form action="settings.php" method="POST" autocomplete="off">

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
                                        <input type="email" class="form-control" id="smtp_username" name="smtp_username" value="<?= htmlspecialchars($settings['smtp_username']) ?>" required placeholder="e.g., your.email@gmail.com" autocomplete="off">
                                        <small class="form-text text-muted">Your full Gmail address.</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="smtp_password">SMTP Password (App Password)</label>
                                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" placeholder="Enter new App Password to update" autocomplete="new-password">
                                        <small class="form-text text-muted">Leave blank to keep the current password saved in the main settings. <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer">Generate here</a>.</small>
                                    </div>
                                </div>

                                <hr>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="smtp_from_email">'From' Email Address</label>
                                        <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" value="<?= htmlspecialchars($settings['smtp_from_email']) ?>" required placeholder="Usually same as Username" autocomplete="off">
                                        <small class="form-text text-muted">Email address system emails will appear to be sent from.</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="smtp_from_name">'From' Name</label>
                                        <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" value="<?= htmlspecialchars($settings['smtp_from_name']) ?>" required placeholder="e.g., Support Team" autocomplete="off">
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

</body>

</html>