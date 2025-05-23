<?php
session_start();
$pageTitle = "Companies";
require_once "../fetch/companies.php"; // Fetches distinct company names into $companies
// No other fetches needed for the main page

// Check for add company errors/success from redirect
$addCompanySuccess = isset($_GET["addSuccess"]) && $_GET["addSuccess"] === "1";
$addCompanyError = isset($_GET["addError"]) ? htmlspecialchars($_GET["addError"]) : '';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <style>
        /* Optional: Add some styling for cards */
        .company-card {
            margin-bottom: 1.5rem;
        }

        /* Styling for product cards inside the modal */
        .product-modal-card {
            margin-bottom: 1rem;
            /* Space between product cards */
        }

        .product-modal-card .card-body {
            padding: 0.75rem;
            /* Smaller padding for modal cards */
        }

        .product-modal-card .card-title {
            margin-bottom: 0.5rem;
            font-size: 1rem;
            /* Smaller title font size */
            font-weight: bold;
        }

        .product-modal-card .card-text {
            font-size: 0.9rem;
            /* Smaller text font size */
            margin-bottom: 0.3rem;
            /* Space between text lines */
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
                        <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addCompanyModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Company
                        </button>
                    </div>

                    <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): // Existing success message 
                    ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> Operation successful!
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($addCompanySuccess): // New add company success message 
                    ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> Company added successfully!
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorMessage)): // Display database error from fetch/companies.php 
                    ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-x-circle-fill"></i> Error fetching companies: <?= htmlspecialchars($errorMessage) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($addCompanyError)): // New add company error message 
                    ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-x-circle-fill"></i> Error adding company: <?= $addCompanyError ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>


                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= $pageTitle ?> List</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                // Check if $companies array exists and is not empty
                                if (!empty($companies)) {
                                    // Loop through each company and display it as a card
                                    foreach ($companies as $companyName) {
                                        // Assuming $companies is a simple array of company names (strings)
                                        // Use urlencode for company name in data attributes for safety
                                        // $encodedCompanyName = urlencode($companyName); // Not strictly needed if passing via data attribute

                                        echo '<div class="col-xl-3 col-md-6 company-card">';
                                        echo '<div class="card border-left-primary shadow h-100 py-2">';
                                        echo '<div class="card-body">';
                                        echo '<div class="row no-gutters align-items-center mb-3">'; // Added mb-3 for space below company name
                                        echo '<div class="col mr-2">';
                                        echo '<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">';
                                        echo 'Company';
                                        echo '</div>';
                                        echo '<div class="h5 mb-0 font-weight-bold text-gray-800">' . htmlspecialchars($companyName) . '</div>';
                                        echo '</div>';
                                        echo '</div>';

                                        // Add the "View Products" button
                                        echo '<a href="#" class="btn btn-sm btn-secondary view-products-btn"
                                                data-toggle="modal"
                                                data-target="#productsModal"
                                                data-company-name="' . htmlspecialchars($companyName) . '">'; // Pass the company name as data attribute
                                        echo 'View Purchased Products';
                                        echo '</a>';

                                        echo '</div>'; // card-body
                                        echo '</div>'; // card
                                        echo '</div>'; // col
                                    }
                                } elseif (empty($errorMessage) && empty($addCompanyError)) { // Only show "No companies found" if there wasn't a DB error
                                    // Display a message if no companies are found
                                    echo '<div class="col-12">';
                                    echo '<p>No companies found.</p>';
                                    echo '</div>';
                                }
                                ?>
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

    <div class="modal fade" id="productsModal" tabindex="-1" role="dialog" aria-labelledby="productsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productsModalLabel">Products for <span id="companyNameInModal"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="productsListInModal" class="row">
                        <div class="col-12 text-center">Loading...</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCompanyModal" tabindex="-1" role="dialog" aria-labelledby="addCompanyModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCompanyModalLabel">Add New Company</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addCompanyForm" action="../process/add_company.php" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="companyName">Company Name</label>
                            <input type="text" class="form-control" id="companyName" name="company_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Company</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            // --- Existing Products Modal Logic ---
            var productsModal = $('#productsModal');
            var modalTitleSpan = $('#companyNameInModal');
            var productsListDiv = $('#productsListInModal');

            productsModal.on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var companyName = button.data('company-name');

                modalTitleSpan.text(companyName);

                productsListDiv.html('<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

                $.ajax({
                    url: '../fetch/fetch_company_products.php',
                    method: 'GET',
                    data: {
                        company: companyName
                    },
                    success: function(response) {
                        // Assuming fetch_company_products.php returns HTML cards directly
                        if (response.trim() === '') {
                            productsListDiv.html('<div class="col-12 text-center"><p>No products found for this company.</p></div>');
                        } else {
                            productsListDiv.html(response);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX error fetching products:", textStatus, errorThrown);
                        productsListDiv.html('<div class="col-12 text-danger">Error loading products. Please try again.</div>');
                    }
                });
            });

            productsModal.on('hidden.bs.modal', function() {
                productsListDiv.html('');
                modalTitleSpan.text('');
            });

            // --- New Add Company Modal Logic ---
            var addCompanyModal = $('#addCompanyModal');
            var addCompanyForm = $('#addCompanyForm');

            // Optional: Clear form when modal is hidden
            addCompanyModal.on('hidden.bs.modal', function() {
                addCompanyForm[0].reset(); // Reset the form fields
                // Optionally clear any client-side validation messages here
            });

            // Client-side form validation (basic)
            addCompanyForm.on('submit', function(event) {
                var companyNameInput = $('#companyName');
                if (companyNameInput.val().trim() === '') {
                    alert('Company Name cannot be empty.'); // Simple alert
                    event.preventDefault(); // Prevent form submission
                    // For a better UI, you would display validation errors next to the field
                }
                // If validation passes, the form will submit normally via POST
            });

            // Automatically hide success/error alerts after a few seconds
            $('.alert-dismissible').fadeTo(5000, 500).slideUp(500, function() {
                $(this).remove();
            });

        });
    </script>

</body>

</html>