<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
$pageTitle = "On-going Cases";
require_once "../fetch/ongoing_cases.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">


    <style>
        /* Chat Messages Container */
        #chatMessages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            /* Space between messages */
            padding: 10px;
            overflow-y: auto;
            max-height: 300px;
        }

        /* Common message styles */
        .chat-message {
            max-width: 80%;
            word-wrap: break-word;
            padding: 10px 15px;
            border-radius: 15px;
            position: relative;
        }

        /* Sender's message (align to the right) */
        .message-sender {
            align-self: flex-end;
            background-color: #007bff;
            color: white;
            text-align: left;
        }

        /* Receiver's message (align to the left) */
        .message-receiver {
            align-self: flex-start;
            background-color: #f1f1f1;
            color: black;
            text-align: left;
        }

        /* Timestamp styling */
        .message-time {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            text-align: right;
            margin-top: 5px;
            display: block;
        }

        .message-receiver .message-time {
            color: rgba(0, 0, 0, 0.6);
        }
    </style>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include_once "../components/sidebar.php" ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php include_once "../components/topbar.php" ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                        <!-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generate
                            Report</a> -->
                    </div>

                    <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> Case solved successfully! Go to <a href="solved_cases.php">Solved Cases</a>.
                        </div>
                    <?php elseif (isset($_GET["escalate_severity"]) && $_GET["escalate_severity"] === "1"): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> Case severity escalated successfully!
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= $pageTitle ?> Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="table">
                                    <thead>
                                        <tr>
                                            <th>Case Number</th>
                                            <th>Type</th>
                                            <th>Subject</th>
                                            <th>Product Group</th>
                                            <th>Product</th>
                                            <th>Product Version</th>
                                            <th>Severity</th>
                                            <th>Case Owner</th>
                                            <th>Company</th>
                                            <th>Last Modified</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($ongoingCasesTable as $row) {
                                            $caseNumber = '<a href="#" class="case-number btn" data-case-id="' . $row["id"] . '" data-case-number="' . $row["case_number"] . '" data-case-owner="' . $row["case_owner"] . '">' . $row["case_number"] . '</a>';

                                            $action = '<button 
                                            data-bs-case-number="' . $row["case_number"] . '"
                                            data-bs-reopen="false"
                                            type="button" 
                                            class="mark-as-solved-btn btn badge btn-primary btn-sm" 
                                            data-toggle="modal" 
                                            data-target="#markAsSolved">
                                            <i class="bi bi-check"></i> 
                                            Mark as Solved
                                            </button>';

                                            if ($row["severity"] == "Production System Down") {
                                                $escalateSeverity = "";
                                            } else {
                                                $escalateSeverity = '<button 
                                                data-bs-case-id="' . $row["id"] . '"
                                                data-bs-severity="' . $row["severity"] . '"
                                                data-bs-reopen="false"
                                                type="button" 
                                                class="escalate-severity-btn btn badge btn-warning btn-sm" 
                                                data-toggle="modal" 
                                                data-target="#escalateSeverity">
                                                <i class="bi bi-exclamation"></i> 
                                                Escalate Issue
                                                </button>';
                                            }

                                            echo "<tr>";
                                            echo "<td>" . $caseNumber . "</td>";
                                            echo "<td>" . $row["type"] . "</td>";
                                            echo "<td>" . $row["subject"] . "</td>";
                                            echo "<td>" . $row["product_group"] . "</td>";
                                            echo "<td>" . $row["product"] . "</td>";
                                            echo "<td>" . $row["product_version"] . "</td>";
                                            echo "<td>" . $row["severity"] . $escalateSeverity . "</td>";
                                            echo "<td>" . $row["case_owner"] . "</td>";
                                            echo "<td>" . $row["company"] . "</td>";
                                            echo "<td>" . $row["last_modified"] . "</td>";
                                            echo "<td>" . $action . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php include_once "../components/footer.php" ?>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <?php include_once "../modals/logout.php" ?>
    <?php include_once "../modals/mark_as_solved.php" ?>
    <?php include_once "../modals/escalate_severity.php" ?>

    <!-- Chat Modal -->
    <div class="modal fade" id="chatModal" tabindex="-1" role="dialog" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatModalLabel">Chat</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="chatMessages" style="height: 300px; overflow-y: scroll;">
                        <!-- Chat messages will be loaded here -->
                    </div>
                    <textarea id="chatInput" style="width: 100%; margin-top: 10px;" placeholder="Type your message here..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="sendMessage">Send</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <!-- <script src="../vendor/chart.js/Chart.min.js"></script> -->

    <!-- Page level custom scripts -->
    <!-- <script src="../js/demo/chart-area-demo.js"></script>
    <script src="../js/demo/chart-pie-demo.js"></script> -->

    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>

    <script>
        new DataTable('#table');
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const markAsSolvedModal = document.getElementById("markAsSolved");
            const caseNumberHidden = document.getElementById("caseNumber");
            const isReopenHidden = document.getElementById("isReopen");

            document.querySelectorAll('.mark-as-solved-btn').forEach(item => {
                item.addEventListener('click', function(event) {
                    caseNumberHidden.value = this.getAttribute("data-bs-case-number");
                    isReopenHidden.value = this.getAttribute("data-bs-reopen");
                });
            });

            const caseIdHidden = document.getElementById("caseId");

            document.querySelectorAll('.escalate-severity-btn').forEach(item => {
                item.addEventListener('click', function(event) {

                    caseIdHidden.value = this.getAttribute("data-bs-case-id");

                    // switch (severity) {
                    //     case "Question/Inconvenience":
                    //         addOption(severitySelect, "Production System Down", "1 - Production System Down");
                    //         addOption(severitySelect, "Restricted Operations", "2 - Restricted Operations");
                    //         break;
                    //     case "Restricted Operations":
                    //         addOption(severitySelect, "Production System Down", "1 - Production System Down");
                    //         break;
                    //     default:
                    //         console.warn("Unknown severity:", severity);
                    //         break;
                    // }

                    // for (let i = 0; i < severitySelect.options.length; i++) {
                    //     switch (severity) {
                    //         case "Question/Inconvenience":
                    //             addOption(severitySelect, "Production System Down", "1 - Production System Down");
                    //             addOption(severitySelect, "Restricted Operations", "2 - Restricted Operations");
                    //             break;
                    //         case "Restricted Operations":
                    //             addOption(severitySelect, "Production System Down", "1 - Production System Down");
                    //             break;
                    //         default:
                    //             console.warn("Unknown severity:", severity);
                    //             break;
                    //     }
                    // }
                });
            });
        });

        // Helper function to add options to the select element
        function addOption(selectElement, value, text) {
            const option = document.createElement("option");
            option.value = value;
            option.text = text;
            selectElement.add(option);
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chatModal = new bootstrap.Modal(document.getElementById('chatModal'));
            const chatMessages = document.getElementById('chatMessages');
            const chatInput = document.getElementById('chatInput');
            const sendMessageButton = document.getElementById('sendMessage');
            let currentCaseNumber = null;
            let fetchInterval = null; // To store the interval reference

            // Event listener for case number clicks
            document.querySelectorAll('.case-number').forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault();
                    currentCaseNumber = this.getAttribute('data-case-number');
                    currentCaseOwner = this.getAttribute("data-case-owner");
                    fetchChatMessages(currentCaseNumber);

                    chatModal.show();

                    // Start fetching messages in real-time every 3 seconds
                    if (fetchInterval) clearInterval(fetchInterval);
                    fetchInterval = setInterval(() => {
                        fetchChatMessages(currentCaseNumber);
                    }, 3000);
                });
            });

            // Function to fetch chat messages
            function fetchChatMessages(caseNumber) {
                fetch(`../fetch/chat_messages.php?case_number=${caseNumber}`)
                    .then(response => response.json())
                    .then(data => {
                        chatMessages.innerHTML = '';

                        data.forEach(message => {
                            const messageElement = document.createElement('div');
                            messageElement.classList.add('chat-message');

                            if (message.sender === "<?= $_SESSION['user_full_name'] ?>") {
                                messageElement.classList.add('message-sender');
                            } else {
                                messageElement.classList.add('message-receiver');
                            }

                            messageElement.innerHTML = `
                        <strong>${message.sender}</strong><br>
                        ${message.message}
                        <span class="message-time">${message.created_at}</span>
                    `;

                            chatMessages.appendChild(messageElement);
                        });

                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    })
                    .catch(error => console.error('Error fetching chat messages:', error));
            }

            // Event listener for sending messages
            sendMessageButton.addEventListener('click', function() {
                const message = chatInput.value.trim();
                if (message && currentCaseNumber) {
                    fetch('../process/send_message.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                case_number: currentCaseNumber,
                                case_owner: currentCaseOwner,
                                message: message
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                chatInput.value = '';
                                fetchChatMessages(currentCaseNumber); // Fetch immediately after sending a message
                            }
                        });
                }
            });

            // Stop fetching messages when the chat modal is closed
            document.getElementById('chatModal').addEventListener('hidden.bs.modal', function() {
                if (fetchInterval) {
                    clearInterval(fetchInterval);
                    fetchInterval = null;
                }
            });
        });
    </script>

</body>

</html>