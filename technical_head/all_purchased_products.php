<?php
session_start();

require_once "../fetch/fetch_customer_products.php";
// Include database connection or configuration if needed for fetching companies
// require_once "../config/db_connection.php"; // Ensure this is included or handled elsewhere

$pageTitle = "Purchased Products";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <style>
        .select2-container {
            width: 100% !important;
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
                        <h1 class="h3 mb-0 text-gray-800">Purchased Products</h1>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addCustomerProductModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Customer Product
                        </button>
                    </div>
                    <?php if (isset($_GET['add_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Product added successfully!
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['add_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error adding product: <?= htmlspecialchars($_GET['add_error']) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table" id="table">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Serial Number</th>
                                    <th>Company</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($customerProductsTable)) {
                                    foreach ($customerProductsTable as $row) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row["customer_name"] ?? 'N/A') . "</td>";
                                        echo "<td>" . htmlspecialchars($row["serial_number"] ?? 'N/A') . "</td>";
                                        echo "<td>" . htmlspecialchars($row["company"] ?? 'N/A') . "</td>";
                                        echo '<td>
                                                    <button type="button" class="btn btn-sm btn-info view-details-btn"
                                                        data-toggle="modal"
                                                        data-target="#viewDetailsModal"
                                                        data-product-group="' . htmlspecialchars($row["product_group"] ?? 'N/A') . '"
                                                        data-product-type="' . htmlspecialchars($row["product_type"] ?? 'N/A') . '"
                                                        data-product-version="' . htmlspecialchars($row["product_version"] ?? 'N/A') . '"
                                                        data-supported-platforms="' . htmlspecialchars($row["supported_platforms"] ?? 'N/A') . '"
                                                        data-license-type="' . htmlspecialchars($row["license_type"] ?? 'N/A') . '"
                                                        data-license-start="' . htmlspecialchars($row["license_date_start"] ?? 'N/A') . '"
                                                        data-license-end="' . htmlspecialchars($row["end_license_date"] ?? 'N/A') . '"
                                                        data-license-duration="' . htmlspecialchars($row["license_duration"] ?? 'N/A') . '"
                                                        data-added-at="' . htmlspecialchars($row["created_at"] ?? 'N/A') . '"
                                                    >View Other Details</button>
                                                </td>';
                                        echo "</tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="4" class="text-center">No customer products found.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
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

    <div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDetailsModalLabel">Product Details</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Product Group:</strong> <span id="modal-product-group"></span></p>
                    <p><strong>Type:</strong> <span id="modal-product-type"></span></p>
                    <p><strong>Version:</strong> <span id="modal-product-version"></span></p>
                    <p><strong>License Type:</strong> <span id="modal-license-type"></span></p>
                    <p><strong>License Start:</strong> <span id="modal-license-start"></span></p>
                    <p><strong>License End:</strong> <span id="modal-license-end"></span></p>
                    <p><strong>Duration:</strong> <span id="modal-license-duration"></span></p>
                    <p><strong>Added At:</strong> <span id="modal-added-at"></span></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCustomerProductModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerProductModalLabel">Add New Customer Product</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_name">Customer Name</label>
                                    <select class="form-control" id="customer_name" name="customer_id" style="width: 100%;" required>
                                        <option value="">Search for a customer...</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="serial_number">Serial Number</label>
                                    <input type="text" class="form-control" id="serial_number" name="serial_number" placeholder="Enter Serial Number" required>
                                </div>
                                <div class="form-group">
                                    <label for="product_group">Product Group</label>
                                    <input type="text" class="form-control" id="product_group" name="product_group" placeholder="Product Group" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="product_type">Product Type</label>
                                    <input type="text" class="form-control" id="product_type" name="product_type" placeholder="Product Type" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="product_version">Product Version</label>
                                    <input type="text" class="form-control" id="product_version" name="product_version" placeholder="Product Version" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="license_type">License Type</label>
                                    <input type="text" class="form-control" id="license_type" name="license_type" placeholder="License Type" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company">Company</label>
                                    <select class="form-control" id="company" name="company_id" style="width: 100%;" required>
                                        <option value="">Search for a company...</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="created_at">Added At</label>
                                    <input type="datetime-local" class="form-control" id="created_at" name="created_at" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="license_date_start">License Start Date</label>
                                    <input type="date" class="form-control" id="license_date_start" name="license_date_start" required>
                                </div>
                                <div class="form-group">
                                    <label for="end_license_date">License End Date</label>
                                    <input type="date" class="form-control" id="end_license_date" name="end_license_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="license_duration">License Duration</label>
                                    <input type="text" class="form-control" id="license_duration" name="license_duration" placeholder="Calculated automatically" readonly>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="button" id="addProductBtn">Add Product</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/chart.js/Chart.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#table').DataTable();

            // View Details Modal logic
            $('#viewDetailsModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);
                modal.find('#modal-product-group').text(button.data('product-group'));
                modal.find('#modal-product-type').text(button.data('product-type'));
                modal.find('#modal-product-version').text(button.data('product-version'));
                modal.find('#modal-supported-platforms').text(button.data('supported-platforms'));
                modal.find('#modal-license-type').text(button.data('license-type'));
                modal.find('#modal-license-start').text(button.data('license-start'));
                modal.find('#modal-license-end').text(button.data('license-end'));
                modal.find('#modal-license-duration').text(button.data('license-duration'));
                modal.find('#modal-added-at').text(button.data('added-at'));
            });

            // Select2 for Customer Name
            $('#customer_name').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#addCustomerProductModal'),
                ajax: {
                    url: "../fetch/fetch_customers.php", // Ensure this file exists and returns customers
                    method: 'GET',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2,
                placeholder: 'Search for a customer...',
                allowClear: true
            });

            // Select2 for Company
            $('#company').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#addCustomerProductModal'),
                ajax: {
                    url: "../fetch/fetch_companies.php", // New file to fetch companies
                    method: 'GET',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: 'Search for a company...',
                allowClear: true
            });

            // Function to set the current datetime for 'Added At' field
            function setCreatedAt() {
                var now = new Date();
                var year = now.getFullYear();
                var month = (now.getMonth() + 1).toString().padStart(2, '0');
                var day = now.getDate().toString().padStart(2, '0');
                var hours = now.getHours().toString().padStart(2, '0');
                var minutes = now.getMinutes().toString().padStart(2, '0');
                var formattedDateTime = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
                $('#created_at').val(formattedDateTime);
            }

            // Reset form and set Created At when modal is shown
            $('#addCustomerProductModal').on('show.bs.modal', function() {
                $('#addProductForm')[0].reset();
                $('#customer_name').val(null).trigger('change');
                $('#company').val(null).trigger('change'); // Reset company select
                setCreatedAt();
                // Clear readonly/calculated fields
                $('#product_group').val('');
                $('#product_type').val('');
                $('#product_version').val('');
                $('#license_type').val('');
                // Removed clearing product_status
                $('#license_date_start').val('');
                $('#end_license_date').val('');
                $('#license_duration').val('');
            });

            // Function to calculate License Duration
            function calculateLicenseDuration() {
                var startDate = $('#license_date_start').val();
                var endDate = $('#end_license_date').val();
                var durationInput = $('#license_duration');
                if (startDate && endDate) {
                    var start = new Date(startDate);
                    var end = new Date(endDate);
                    // Add 1 day to include the end date in the calculation
                    var endInclusive = new Date(end);
                    endInclusive.setDate(endInclusive.getDate() + 1);

                    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                        durationInput.val('Invalid Date');
                        return;
                    }
                    var timeDiff = endInclusive.getTime() - start.getTime();
                    var diffDays = Math.floor(timeDiff / (1000 * 3600 * 24));
                    if (diffDays >= 0) {
                        durationInput.val(diffDays + ' days');
                    } else {
                        durationInput.val('End date before start');
                    }
                } else {
                    durationInput.val('');
                }
            }

            // Recalculate duration when start or end date changes
            $('#license_date_start, #end_license_date').on('change', calculateLicenseDuration);

            // Auto-populate product details when serial number is entered/changed
            $('#serial_number').on('keyup', function() {
                var serialNumber = $(this).val();
                // Clear previous auto-populated fields while typing
                $('#product_group').val('');
                $('#product_type').val('');
                $('#product_version').val('');
                $('#license_type').val('');
                // Removed clearing product_status

                if (serialNumber) {
                    $.ajax({
                        url: '../fetch/fetch_product_details.php', // Ensure this file exists and works
                        method: 'POST',
                        data: {
                            serial_number: serialNumber
                        },
                        dataType: 'json',
                        success: function(data) {
                            console.log("Fetch Product Details Success:", data);
                            if (data && Object.keys(data).length > 0 && !data.error) {
                                $('#product_group').val(data.product_group || '');
                                $('#product_type').val(data.product_type || '');
                                $('#product_version').val(data.product_version || '');
                                $('#license_type').val(data.license_type || '');
                                // Removed populating product_status

                                // Autopopulate license dates if available from product details
                                $('#license_date_start').val(data.license_date_start || '');
                                $('#end_license_date').val(data.end_license_date || '');

                                // Autopopulate Created At if available from product details
                                if (data.created_at) {
                                    const dateObj = new Date(data.created_at.replace(' ', 'T'));
                                    if (!isNaN(dateObj.getTime())) {
                                        $('#created_at').val(dateObj.toISOString().substring(0, 16));
                                    } else {
                                        setCreatedAt(); // Fallback to current time
                                    }
                                } else {
                                    setCreatedAt(); // Set to current time if not provided
                                }

                                calculateLicenseDuration(); // Recalculate duration after populating dates
                            } else {
                                // Clear fields if serial number not found or error
                                $('#product_group').val('');
                                $('#product_type').val('');
                                $('#product_version').val('');
                                $('#license_type').val('');
                                // Removed clearing product_status
                                $('#license_date_start').val('');
                                $('#end_license_date').val('');
                                $('#license_duration').val('');
                                setCreatedAt(); // Reset created_at to current time
                                console.warn("Product details not found for serial number:", serialNumber);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error fetching product details:", status, error);
                            console.log("Response Text:", xhr.responseText);
                            // Clear fields on AJAX error
                            $('#product_group').val('');
                            $('#product_type').val('');
                            $('#product_version').val('');
                            $('#license_type').val('');
                            // Removed clearing product_status
                            $('#license_date_start').val('');
                            $('#end_license_date').val('');
                            $('#license_duration').val('');
                            setCreatedAt(); // Reset created_at to current time
                        }
                    });
                } else {
                    // Clear fields if serial number input is empty
                    $('#product_group').val('');
                    $('#product_type').val('');
                    $('#product_version').val('');
                    $('#license_type').val('');
                    // Removed clearing product_status
                    $('#license_date_start').val('');
                    $('#end_license_date').val('');
                    $('#license_duration').val('');
                    setCreatedAt(); // Reset created_at to current time
                }
            });

            $('#addProductBtn').on('click', function() {
                var customerId = $('#customer_name').val(); // This is the Customer ID (still useful for lookup if needed)
                var customerName = $('#customer_name option:selected').text(); // Get the selected customer name text
                var serialNumber = $('#serial_number').val();
                var companyId = $('#company').val(); // This is the Company ID
                var companyName = $('#company option:selected').text(); // Get the selected company name text


                // You might want to also pass the auto-populated product details
                // to the backend for verification or storage in the customer_products table
                // if your schema requires it. Adjust formData accordingly.
                var formData = {
                    customer_name: customerName, // Send the name
                    serial_number: serialNumber,
                    company: companyName, // Send the name
                    product_group: $('#product_group').val(),
                    product_type: $('#product_type').val(),
                    product_version: $('#product_version').val(),
                    license_type: $('#license_type').val(),
                    license_duration: $('#license_duration').val(),
                    license_date_start: $('#license_date_start').val(),
                    end_license_date: $('#end_license_date').val(),
                    created_at: $('#created_at').val()
                    // supported_platforms is still not in the form, handle as needed
                };

                // Basic validation - now check for names and other required fields
                if (!formData.customer_name || !formData.serial_number || !formData.company || !formData.license_date_start || !formData.end_license_date) {
                    alert("Please select a Customer and a Company, enter Serial Number, License Start Date, and License End Date.");
                    return;
                }

                $.ajax({
                    url: '../process/add_new_customer_product.php', // Ensure this file exists and works
                    method: 'POST',
                    data: formData,
                    dataType: 'json', // Still expect JSON response
                    success: function(response) {
                        console.log("Add Product AJAX success. Response:", response);
                        if (response.success) {
                            // Redirect with a success flag
                            window.location.href = 'all_purchased_products.php?add_success=1';
                        } else {
                            // Redirect with an error flag and message
                            var errorMessage = response.message ? encodeURIComponent(response.message) : 'Unknown error.';
                            window.location.href = 'all_purchased_products.php?add_error=' + errorMessage;
                        }
                    },
                    error: function(xhr, status, error) {
                         console.error("Add Product AJAX Error:", status, error);
                         console.log("Response Text:", xhr.responseText); // <-- Check this in console!
                         // Redirect with a generic error message
                         // Include responseText if possible for better debugging on redirect
                         var errorMessage = 'Network or server error.';
                         if (xhr.responseText) {
                             errorMessage += ' Details: ' + xhr.responseText.substring(0, 200); // Limit length
                         }
                         window.location.href = 'all_purchased_products.php?add_error=' + encodeURIComponent(errorMessage);
                     }
                });
            });
        });
    </script>
</body>

</html>