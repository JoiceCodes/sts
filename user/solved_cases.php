<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
$pageTitle = "Solved Cases";
require_once "../fetch/solved_cases_table_user.php"; // Ensure this fetches last_modified if needed in modal
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars($pageTitle) ?> - Your App Name</title>

    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">

    <style>
        #detailsModal .modal-body p { margin-bottom: 0.6rem; line-height: 1.4; }
        #detailsModal .modal-body strong { display: inline-block; width: 140px; margin-right: 10px; color: #5a5c69; }
        #detailsModal .modal-body span { color: #6e707e; }

        #table th,
        #table td {
             padding: 0.75rem;
             vertical-align: middle;
             text-align: center;
        }
        #table th {
             white-space: nowrap;
         }
        #table .details-col {
             width: 1%;
             white-space: nowrap;
        }
        .btn-view-details {
             padding: 0.25rem 0.5rem;
             font-size: 0.8rem;
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
                        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($pageTitle) ?></h1>
                    </div>

                     <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fas fa-check-circle mr-2"></i> Operation successful!<button type="button" class="close" data-dismiss="alert">&times;</button></div>
                    <?php endif; ?>
                     <?php if (isset($_GET["error"])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-triangle mr-2"></i> An error occurred: <?= htmlspecialchars($_GET["error"]) ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($pageTitle) ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="solvedCasesTable"  width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Case Number</th>
                                            <th>Severity</th>
                                            <th>Company</th>
                                            <th>Date Opened</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $casesData = $solvedCasesTable ?? $ongoingCasesTable ?? [];

                                        if (isset($casesData) && is_array($casesData)) {
                                            foreach ($casesData as $row) {
                                                $viewButton = '<button type="button" class="btn btn-info btn-sm btn-view-details" '
                                                    . ' data-toggle="modal"'
                                                    . ' data-target="#detailsModal"'
                                                    . ' data-case-number="' . htmlspecialchars($row["case_number"] ?? 'N/A') . '"'
                                                    . ' data-type="' . htmlspecialchars($row["type"] ?? 'N/A') . '"'
                                                    . ' data-subject="' . htmlspecialchars($row["subject"] ?? 'N/A') . '"'
                                                    . ' data-contact-name="' . htmlspecialchars($row["contact_name"] ?? 'N/A') . '"'
                                                    . ' data-product-group="' . htmlspecialchars($row["product_group"] ?? 'N/A') . '"'
                                                    . ' data-product="' . htmlspecialchars($row["product"] ?? 'N/A') . '"'
                                                    . ' data-product-version="' . htmlspecialchars($row["product_version"] ?? 'N/A') . '"'
                                                    . ' data-last-modified="' . htmlspecialchars($row["last_modified"] ?? 'N/A') . '"'
                                                    . '><i class="fas fa-eye"></i> View</button>';

                                                    $reopenButton = '<button
                                                    data-bs-case-number="' . htmlspecialchars($row["case_number"]) . '"
                                                    type="button"
                                                    class="reopen-case-btn btn btn-warning btn-sm"
                                                    data-toggle="modal"
                                                    data-target="#reopenCase">
                                                    <i class="bi bi-folder-symlink"></i>
                                                    Reopen
                                                    </button>';

                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row["case_number"] ?? 'N/A') . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["severity"] ?? 'N/A') . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["company"] ?? 'N/A') . "</td>";
                                                    
                                                    // Format datetime_opened to mm/dd/yyyy
                                                    if (!empty($row["datetime_opened"])) {
                                                        $date = date('m/d/Y', strtotime($row["datetime_opened"]));
                                                    } else {
                                                        $date = 'N/A';
                                                    }
                                                    echo "<td>" . htmlspecialchars($date) . "</td>";
                                                    
                                                    echo "<td class='actions-col'>" . $viewButton . "&nbsp;" . $reopenButton . "</td>";
                                                    
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo '<tr><td colspan="5" class="text-center text-muted">No solved cases found.</td></tr>'; // Adjusted colspan
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
    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <?php include_once "../modals/logout.php" ?>
    <?php include_once "../modals/reopen_case.php" ?>

    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Case Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p><strong>Case Number:</strong> <span id="modalDetailCaseNumber"></span></p>
                    <p><strong>Company:</strong> <span id="modalDetailCompany"></span></p>
                    <p><strong>Contact Name:</strong> <span id="modalDetailContactName"></span></p>
                    <hr class="my-2">
                    <p><strong>Severity:</strong> <span id="modalDetailSeverity"></span></p>
                    <p><strong>Type:</strong> <span id="modalDetailType"></span></p>
                    <p><strong>Subject:</strong> <span id="modalDetailSubject"></span></p>
                    <hr class="my-2">
                    <p><strong>Product Group:</strong> <span id="modalDetailProductGroup"></span></p>
                    <p><strong>Product:</strong> <span id="modalDetailProduct"></span></p>
                    <p><strong>Product Version:</strong> <span id="modalDetailProductVersion"></span></p>
                    <hr class="my-2">
                    <p><strong>Date Opened:</strong> <span id="modalDetailDateOpened"></span></p>
                    <p><strong>Last Modified:</strong> <span id="modalDetailLastModified"></span></p>
                 </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/chart.js/Chart.min.js"></script>
    <script src="../js/demo/chart-area-demo.js"></script>
    <script src="../js/demo/chart-pie-demo.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#table').DataTable({
                "order": [[ 3, "desc" ]], // Order by Date Opened (now 4th column, index 3)
                "columnDefs": [
                    { "orderable": false, "targets": 4 } // Disable sorting on Details button (now 5th column, index 4)
                ]
            });

            const reopenCaseModalEl = document.getElementById("reopencase");
            if(reopenCaseModalEl) {
                const caseNumberHidden = document.getElementById("caseNumber");
                 document.querySelectorAll('.reopen-case-btn').forEach(item => {
                    item.addEventListener('click', function(event) {
                       if(caseNumberHidden) caseNumberHidden.value = this.getAttribute("data-bs-case-number");
                    });
                 });
            }

            $('#solvedCasesTable tbody').on('click', '.reopen-case-btn', function() {
                 // Assuming your reopen_case.php modal has an input with id="caseNumber"
                 const caseNumberInput = document.getElementById("caseNumber"); // Get the hidden input in reopen modal
                 if(caseNumberInput) {
                     caseNumberInput.value = $(this).data("bs-case-number");
                 } else {
                     console.error("Could not find #caseNumber input in reopen modal.");
                 }
                 // The data-toggle="modal" on the button handles showing the #reopenCase modal
             });

            $('#detailsModal').on('show.bs.modal', function (event) {
                var triggerButton = $(event.relatedTarget);

                var caseNumFromData = triggerButton.data('case-number') || 'N/A';
                var type = triggerButton.data('type') || 'N/A';
                var subject = triggerButton.data('subject') || 'N/A';
                var contactName = triggerButton.data('contact-name') || 'N/A';
                var productGroup = triggerButton.data('product-group') || 'N/A';
                var product = triggerButton.data('product') || 'N/A';
                var productVersion = triggerButton.data('product-version') || 'N/A';
                var lastModified = triggerButton.data('last-modified') || 'N/A';

                var row = triggerButton.closest('tr');
                // Indices: CaseNum(0), Sev(1), Comp(2), DateOpen(3)
                var caseNumber = row.find('td:eq(0)').text().trim() || 'N/A';
                var severity = row.find('td:eq(1)').text().trim() || 'N/A';
                var company = row.find('td:eq(2)').text().trim() || 'N/A'; // Index adjusted
                var dateOpened = row.find('td:eq(3)').text().trim() || 'N/A'; // Index adjusted

                var modal = $(this);

                modal.find('.modal-title').text('Details for Case: ' + caseNumber);
                modal.find('#modalDetailCaseNumber').text(caseNumber);
                modal.find('#modalDetailCompany').text(company);
                modal.find('#modalDetailContactName').text(contactName);
                modal.find('#modalDetailSeverity').text(severity);
                modal.find('#modalDetailDateOpened').text(dateOpened);
                modal.find('#modalDetailType').text(type);
                modal.find('#modalDetailSubject').text(subject);
                modal.find('#modalDetailProductGroup').text(productGroup);
                modal.find('#modalDetailProduct').text(product);
                modal.find('#modalDetailProductVersion').text(productVersion);
                modal.find('#modalDetailLastModified').text(lastModified);
                // Removed lines for Case Owner
            });

        }); // End document ready
    </script>

</body>
</html>