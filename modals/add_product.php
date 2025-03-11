<div class="modal fade" id="addProduct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Product</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="../process/add_product.php" id="myForm" method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <input type="text" name="name" required placeholder="Product Name" id="name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <!-- <input type="text" name="category" required placeholder="Product Category" id="category" class="form-control"> -->
                        <select name="category" required id="category" class="form-control">
                            <option value="" disabled selected>-- Select Product Category --</option>
                            <?php
                            foreach ($productCategories as $row) {
                                echo "<option value ='" . $row["id"] . "'>" . $row["product_category"] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <select name="type" required id="type" class="form-control">
                            <option value="" disabled selected>-- Select Product Type --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="number" name="version" step="0.01" required placeholder="Product Version" id="version" class="form-control">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="supported_platform[]" value="Windows" id="windowsCheck">
                            <label class="form-check-label" for="windowsCheck">Windows</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="supported_platform[]" value="MacOS" id="macOsCheck">
                            <label class="form-check-label" for="macOsCheck">MacOS</label>
                        </div>
                        <div class="form-text" id="supportedPlatformsHelp"></div>
                    </div>
                    <div class="mb-3">
                        <!-- <input type="text" name="license_type" required placeholder="License Type" id="licenseType" class="form-control"> -->
                        <select name="license_type" id="licenseType" class="form-control" required>
                            <option value="" disabled selected>-- Select License Type --</option>
                            <option value="Subscription">Subscription</option>
                            <option value="Perpetual">Perpetual</option>
                            <option value="Service Agreement">Service Agreement</option>
                            <option value="Enterprise">Enterprise</option>
                            <option value="Freemium">Freemium</option>
                            <option value="Trial">Trial</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="serial_number" required placeholder="Serial Number" id="serialNumber" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="licenseDuration">License Duration</label>
                        <input type="date" name="license_duration" required placeholder="License Duration" id="licenseDuration" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
            <!-- <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">
                    Cance
                </button>
                <form action="../process/accept_case.php" method="post">
                    <input type="hidden" name="case_id" id="caseId" value="">
                    <button type="submit" class="btn btn-primary">Assign</button>
                </form>
            </div> -->
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
    });
</script>

<script>
    document.getElementById("category").addEventListener("change", function() {
        let categoryId = this.value;
        let typeSelect = document.getElementById("type");

        // Clear the existing options
        typeSelect.innerHTML = '<option value="" disabled selected>-- Select Product Type --</option>';

        if (categoryId) {
            // Make AJAX request
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "../fetch/product_types.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    let response = JSON.parse(xhr.responseText);

                    // Populate the type dropdown
                    response.forEach(function(item) {
                        let option = document.createElement("option");
                        option.value = item.id;
                        option.textContent = item.product_type;
                        typeSelect.appendChild(option);
                    });
                }
            };

            xhr.send("category_id=" + categoryId);
        }
    });
</script>