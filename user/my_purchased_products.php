<?php
session_start();

// Check if the user is logged in and is a 'User'
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "User") {
    // Redirect to login or an unauthorized page if not a logged-in user
    header("Location: ../login.php"); // Adjust the redirect path as needed
    exit();
}

$pageTitle = "Purchased Products"; // Set the page title appropriately

// --- Database Connection ---
// Adjust the path to your database connection file
require_once '../config/database.php'; // Assuming your database connection is in db.php

$purchasedProducts = []; // Initialize an empty array to hold the results
$userFullName = $_SESSION["user_full_name"]; // Get the current user's full name
$userId = $_SESSION["user_id"]; // Get the user's ID - potentially useful

// --- Fetch Purchased Products for the User (Basic Info for Table) ---
// We only fetch columns needed for the main table view, plus the ID
$sql = "SELECT id, product_type, product_version, license_type, end_license_date
        FROM customer_products
        WHERE customer_name = ?"; // Or ideally filter by user ID if linked in customer_products table

$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $userFullName); // Bind the user's full name
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $purchasedProducts[] = $row; // Store the fetched rows
    }
}

$stmt->close();

// Helper function for safe date formatting in PHP
function format_date_php($date_string) {
    if (empty($date_string) || $date_string === '0000-00-00' || $date_string === null) {
        return 'N/A'; // Or return an empty string '', depending on preference
    }
    try {
        $date_obj = new DateTime($date_string);
        return $date_obj->format('m/d/Y'); // Format as MM/DD/YYYY
    } catch (Exception $e) {
        // Log error or handle invalid date format from DB if necessary
        // For display, maybe return the original string or an error indicator
        return htmlspecialchars($date_string) . ' (Invalid)';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <style>
        /* Removed pointer cursor from table rows */
        #table tbody tr {
            cursor: default; /* Or inherit */
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once "../components/sidebar.php" ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once "../components/administrator_topbar.php" ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) // Escape page title ?></h1>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($pageTitle) // Escape title again ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Version</th>
                                            <th>License Type</th>
                                            <th>License End</th>
                                            <th>Actions</th> </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!empty($purchasedProducts)) {
                                            foreach ($purchasedProducts as $row) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row["product_type"]) . "</td>";
                                                echo "<td>" . htmlspecialchars($row["product_version"]) . "</td>";
                                                echo "<td>" . htmlspecialchars($row["license_type"]) . "</td>";
                                                // Format the end license date here
                                                echo "<td>" . format_date_php($row["end_license_date"]) . "</td>";
                                                // Add the button column
                                                echo "<td>";
                                                echo "<button class='btn btn-info btn-sm view-details-btn' data-id='" . htmlspecialchars($row["id"]) . "'>";
                                                echo "View Details";
                                                echo "</button>";
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            // Corrected colspan to 5 (Type, Version, License Type, License End, Actions)
                                            echo "<tr><td colspan='5' class='text-center'>No purchased products found for your account.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
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

    <div class="modal fade" id="purchasedProductDetailsModal" tabindex="-1" role="dialog" aria-labelledby="purchasedProductDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchasedProductDetailsModalLabel">Purchased Product Details</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="modalProductDetailsContent">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="../js/sb-admin-2.min.js"></script>

    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>

    <script>
        // Simple HTML escaping function (good practice for dynamically added content)
        function htmlspecialchars(str) {
            if (typeof str !== 'string') return str; // Return non-strings as is
            return str.replace(/&/g, "&amp;")
                      .replace(/</g, "&lt;")
                      .replace(/>/g, "&gt;")
                      .replace(/"/g, "&quot;")
                      .replace(/'/g, "&#039;");
        }

        // --- NEW: JavaScript Date Formatting Function ---
        function formatDateMMDDYYYY(dateString) {
            if (!dateString || dateString === '0000-00-00') {
                return 'N/A'; // Handle null, empty, or zero-date strings
            }
            try {
                // Create a Date object. Important: If dateString is just YYYY-MM-DD,
                // adding 'T00:00:00' helps avoid potential timezone issues
                // where it might interpret it as UTC and shift the date.
                const dateObj = new Date(dateString.includes(' ') ? dateString : dateString + 'T00:00:00');

                // Check if the date object is valid
                if (isNaN(dateObj.getTime())) {
                    // Optional: Log the invalid date string for debugging
                    // console.warn('Invalid date string encountered:', dateString);
                    return htmlspecialchars(dateString) + ' (Invalid)'; // Show original + mark as invalid
                }
                // Get month, day, year - add 1 to month because it's 0-indexed
                const month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
                const day = dateObj.getDate().toString().padStart(2, '0');
                const year = dateObj.getFullYear();
                return `${month}/${day}/${year}`; // Format as MM/DD/YYYY
            } catch (e) {
                // Optional: Log the error
                // console.error('Error formatting date:', dateString, e);
                 return htmlspecialchars(dateString) + ' (Error)'; // Show original + mark error
            }
        }
        // --- End New Function ---


        $(document).ready(function() {
            // Initialize DataTables on the table with id="table"
            // Add column definition to prevent sorting/searching on the Actions column (last column, index 4)
            $('#table').DataTable({
                "columnDefs": [
                    // Target the 5th column (index 4) which is 'Actions'
                    { "orderable": false, "targets": 4 },
                    { "searchable": false, "targets": 4 }
                 ]
            });

            // Add click event listener to the 'view-details-btn' class within the table body
            $('#table tbody').on('click', '.view-details-btn', function() {
                var productId = $(this).data('id'); // Get the product ID from the button's data-id attribute

                if (productId) {
                    // Set loading text in modal body
                    $('#modalProductDetailsContent').html('Loading...');
                    // Show the modal immediately
                    $('#purchasedProductDetailsModal').modal('show');

                    // Make AJAX request to fetch full details
                    $.ajax({
                        url: '../fetch/fetch_purchased_product_details.php', // The new PHP file we created
                        method: 'GET',
                        data: { product_id: productId },
                        dataType: 'json',
                        success: function(response) {
                            let detailsHtml = '';
                            if (response && response.success && response.product) {
                                const product = response.product;
                                // Build HTML content for the modal body
                                // Use htmlspecialchars for safety and formatDateMMDDYYYY for dates
                                detailsHtml += '<p><strong>Category:</strong> ' + htmlspecialchars(product.product_category) + '</p>';
                                detailsHtml += '<p><strong>Type:</strong> ' + htmlspecialchars(product.product_type) + '</p>';
                                detailsHtml += '<p><strong>Version:</strong> ' + htmlspecialchars(product.product_version) + '</p>';
                                detailsHtml += '<p><strong>Serial Number:</strong> ' + htmlspecialchars(product.serial_number) + '</p>';
                                detailsHtml += '<p><strong>License Type:</strong> ' + htmlspecialchars(product.license_type) + '</p>';
                                detailsHtml += '<p><strong>License Duration:</strong> ' + htmlspecialchars(product.license_duration) + '</p>';
                                // --- Format dates using the new JS function ---
                                detailsHtml += '<p><strong>License Start:</strong> ' + formatDateMMDDYYYY(product.license_date_start) + '</p>';
                                detailsHtml += '<p><strong>License End:</strong> ' + formatDateMMDDYYYY(product.end_license_date) + '</p>';
                                // Assuming created_at might be a full datetime, formatDateMMDDYYYY should handle it
                                detailsHtml += '<p><strong>Purchased On:</strong> ' + formatDateMMDDYYYY(product.created_at) + '</p>';
                                // --- End Date Formatting ---
                                detailsHtml += '<p><strong>Company:</strong> ' + htmlspecialchars(product.company) + '</p>';
                                // Add any other fields you want from the customer_products table

                                $('#modalProductDetailsContent').html(detailsHtml); // Update modal content
                            } else {
                                $('#modalProductDetailsContent').html('<p>Error loading details or product not found.</p>');
                                console.error('Error fetching product details:', response.message || 'Unknown error');
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#modalProductDetailsContent').html('<p>An error occurred while fetching details.</p>');
                            console.error('AJAX Error:', status, error);
                        }
                    });
                }
            });
        });

    </script>

</body>

</html>