<?php
session_start();
$pageTitle = "On-going Cases";

require_once "../fetch/technical_ongoing_cases.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once "../components/head.php" ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.css">

    <style>
        /* Styling for chat messages */
        #chatMessages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            /* Space between messages */
            padding: 10px;
            /* Add some padding inside the chat container */
        }

        /* Sender's message (align to the right) */
        .message-sender {
            align-self: flex-end;
            /* Align to the right */
            text-align: right;
            /* Ensure the text aligns right */
            margin-bottom: 10px;
            /* Add spacing below messages */
        }

        .message-sender-text {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 60%;
            /* Limit the width */
            word-wrap: break-word;
            /* Ensure long words wrap */
            margin-bottom: 5px;
            /* Add spacing between text blocks */
        }

        /* Receiver's message (align to the left) */
        .message-receiver {
            align-self: flex-start;
            /* Align to the left */
            text-align: left;
            /* Ensure the text aligns left */
            margin-bottom: 10px;
            /* Add spacing below messages */
        }

        .message-receiver-text {
            background-color: #f1f1f1;
            color: black;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 60%;
            /* Limit the width */
            word-wrap: break-word;
            /* Ensure long words wrap */
            margin-bottom: 5px;
            /* Add spacing between text blocks */
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
                                            <th>Date & Time Opened</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($ongoingCasesTable as $row) {
                                            $caseNumber = '<a href="#" class="case-number btn" data-case-id="' . $row["id"] . '" data-case-number="' . $row["case_number"] . '" data-case-owner="' . $row["case_owner"] . '">' . $row["case_number"] . '</a>';


                                            echo "<tr>";
                                            echo "<td>" . $caseNumber . "</td>";
                                            echo "<td>" . $row["type"] . "</td>";
                                            echo "<td>" . $row["subject"] . "</td>";
                                            echo "<td>" . $row["product_group"] . "</td>";
                                            echo "<td>" . $row["product"] . "</td>";
                                            echo "<td>" . $row["product_version"] . "</td>";
                                            echo "<td>" . $row["severity"] . "</td>";
                                            echo "<td>" . $row["case_owner"] . "</td>";
                                            echo "<td>" . $row["company"] . "</td>";
                                            echo "<td>" . $row["last_modified"] . "</td>";
                                            echo "<td>" . $row["datetime_opened"] . "</td>";
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
    <script src="../vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../js/demo/chart-area-demo.js"></script>
    <script src="../js/demo/chart-pie-demo.js"></script>

    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.js"></script>

    <script>
        new DataTable('#table');
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
                        chatMessages.innerHTML = ''; // Clear previous messages

                        data.forEach(message => {
                            const messageElement = document.createElement('div');
                            const messageContent = document.createElement('span');

                            // Set message text
                            messageContent.textContent = `${message.sender}: ${message.message}`;

                            // Check if the message sender is the logged-in user
                            if (message.sender === "<?= $_SESSION["user_full_name"] ?>") {
                                messageElement.classList.add('message-sender');
                                messageContent.classList.add('message-sender-text');
                            } else {
                                messageElement.classList.add('message-receiver');
                                messageContent.classList.add('message-receiver-text');
                            }

                            // Append message content
                            messageElement.appendChild(messageContent);
                            chatMessages.appendChild(messageElement);
                        });

                        // Auto-scroll to the latest message
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    });
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