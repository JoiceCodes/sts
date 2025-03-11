<div class="modal fade" id="newCase" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">New Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="../process/new_case.php" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="mb-3">
                        <div class="form-text mb-3">Case Information</div>
                        <div class="mb-3">
                            <input type="text" name="type" id="type" placeholder="Type" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="subject" id="subject" placeholder="Subject" class="form-control" required>
                        </div>
                        <div>
                            <select name="severity" id="severity" class="form-control" required>
                                <option value="" disabled selected>-- Select Severity --</option>
                                <option value="Production System Down">1 - Production System Down</option>
                                <option value="Restricted Operations">2 - Restricted Operations</option>
                                <option value="Question/Inconvenience">3 - Question/Inconvenience</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-text mb-3">Product Information</div>
                        <div class="mb-3">
                            <input type="text" name="serial_number" id="serialNumber" required placeholder="Serial Number" class="form-control">
                        </div>
                        <div class="mb-3">
                            <input type="text" name="company" placeholder="Company" required id="company" class="form-control" readonly>
                        </div class="mb-3">
                        <div class="mb-3">
                            <input type="text" name="product_group" placeholder="Product Group" required id="productGroup" class="form-control" readonly>
                        </div class="mb-3">
                        <div class="mb-3">
                            <input type="text" name="product_name" placeholder="Product Name" required id="productName" class="form-control" readonly>
                        </div>
                        <div>
                            <input type="text" name="product_version" placeholder="Product Version" required id="productVersion" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-text mb-3">Additional Information (Optional)</div>
                        <div>
                            <input type="file" name="attachment" id="attachment" placeholder="Attachment" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const serialInput = document.getElementById("serialNumber");
        const companyInput = document.getElementById("company");
        const productGroupInput = document.getElementById("productGroup");
        const productNameInput = document.getElementById("productName");
        const productVersionInput = document.getElementById("productVersion");

        serialInput.addEventListener("input", function() {
            const serialNumber = serialInput.value.trim();

            if (serialNumber.length > 2) { // Only fetch if at least 3 characters entered
                fetch("../fetch/product.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: "serial_number=" + encodeURIComponent(serialNumber),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            companyInput.value = data.company;
                            productGroupInput.value = data.product_group;
                            productNameInput.value = data.product_name;
                            productVersionInput.value = data.product_version;
                        } else {
                            companyInput.value = "";
                            productGroupInput.value = "";
                            productNameInput.value = "";
                            productVersionInput.value = "";
                        }
                    })
                    .catch(error => console.error("Error fetching product details:", error));
            } else {
                companyInput.value = "";
                productGroupInput.value = "";
                productNameInput.value = "";
                productVersionInput.value = "";
            }
        });
    });
</script>