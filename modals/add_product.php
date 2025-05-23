<div class="modal fade" id="addProduct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Product</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="../process/add_product.php" id="myForm" method="post" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_group" class="form-label">Product Group</label>
                                <select name="product_group" id="product_group" class="form-control" required>
                                    <option value="" disabled selected>-- Select Product Group --</option>
                                    <option value="Software">Software</option>
                                    <option value="Hardware">Hardware</option>
                                    <option value="Service">Service</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="product_type" class="form-label">Product Type</label>
                                <input name="product_type" id="product_type" class="form-control" placeholder="Product Type" required>
                            </div>
                            <div class="mb-3">
                                <label for="product_version" class="form-label">Product Version</label>
                                <input type="text" name="product_version" step="0.01" required placeholder="Product Version" id="product_version" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="license_type" class="form-label">License Type</label>
                                <select name="license_type" id="license_type" class="form-control" required>
                                    <option value="" disabled selected>-- Select License Type --</option>
                                    <option value="Subscription">Subscription</option>
                                    <option value="Perpetual">Perpetual</option>
                                    <option value="Trial">Trial</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" required placeholder="Serial Number" id="serial_number" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="" disabled selected>-- Select Status --</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Expired">Expired</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                        <input type="hidden" name="action" value="add">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById("myForm").addEventListener("submit", function(event) {
        const checkboxes = document.querySelectorAll('input[name="supported_platform[]"]');
        let checked = false;
        const messageDiv = document.getElementById("supportedPlatformsHelp");

        checkboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                checked = true;
            }
        });

        if (!checked) {
            messageDiv.textContent = "Please select at least one platform.";
            messageDiv.style.color = "red"; // Make the text red
            event.preventDefault(); // Prevent form submission
        } else {
            messageDiv.textContent = ""; // Clear the message if at least one is checked
        }

        // Also trigger default Bootstrap validation
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        this.classList.add('was-validated');
    });
</script>

<script>
    document.getElementById("product_group").addEventListener("change", function() {
        // Use the value of the product_group select
        let productGroupValue = this.value;
        // Target the product_type select
        let productTypeSelect = document.getElementById("product_type");

        // Clear the existing options
        productTypeSelect.innerHTML = '<option value="" disabled selected>-- Select Product Type --</option>';

        if (productGroupValue) {
            // Make AJAX request
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "../fetch/product_types.php", true); // Keep the original endpoint
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        let response = JSON.parse(xhr.responseText);
                        // Check if the response is an array and not empty
                        if (Array.isArray(response) && response.length > 0) {
                            // Populate the product type dropdown
                            response.forEach(function(item) {
                                let option = document.createElement("option");
                                // Assuming the response items have 'id' and 'product_type' properties
                                option.value = item.id; // Use ID as value
                                option.textContent = item.product_type; // Use product_type as text
                                productTypeSelect.appendChild(option);
                            });
                        } else {
                            // Add a default option if no types are returned
                            let option = document.createElement("option");
                            option.value = "";
                            option.textContent = "-- No Product Types Found --";
                            productTypeSelect.appendChild(option);
                        }
                    } catch (e) {
                        console.error("Error parsing JSON response:", e);
                        // Add an error option
                        let option = document.createElement("option");
                        option.value = "";
                        option.textContent = "-- Error loading types --";
                        productTypeSelect.appendChild(option);
                    }
                } else if (xhr.readyState === 4) {
                    console.error("AJAX error:", xhr.status, xhr.statusText);
                    // Add an error option on AJAX failure
                    let option = document.createElement("option");
                    option.value = "";
                    option.textContent = "-- Error loading types --";
                    productTypeSelect.appendChild(option);
                }
            };

            // Send the product_group value
            xhr.send("product_group=" + encodeURIComponent(productGroupValue));
        }
    });
</script>