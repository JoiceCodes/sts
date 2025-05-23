<div class="modal fade" id="newCase" tabindex="-1" role="dialog" aria-labelledby="newCaseLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newCaseLabel">New Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newCaseForm" action="../process/new_case.php" method="post" class="needs-validation" novalidate enctype="multipart/form-data">

                    <div class="mb-3">
                        <div class="form-text mb-2 font-weight-bold">Case Information</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="productName" class="form-label">Product Name:</label>
                                <select name="product_name" id="productName" class="form-control" required>
                                    <option value="" disabled selected>-- Select Product --</option>
                                    <option value="MOVEit Cloud" data-company="Progress Software">MOVEit Cloud</option>
                                    <option value="WS_FTP" data-company="Progress Software">WS_FTP</option>
                                    <option value="MOVEit" data-company="Progress Software">MOVEit</option>
                                    <option value="WhatsUp Gold" data-company="Progress Software">WhatsUp Gold</option>
                                    <option value="Sophos Antivirus" data-company="Sophos">Sophos Antivirus</option>
                                    <option value="Sophos Firewall" data-company="Sophos">Sophos Firewall</option>
                                </select>
                                <div class="invalid-feedback">Please select a product name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="severity" class="form-label">Severity:</label>
                                <select name="severity" id="severity" class="form-control" required>
                                    <option value="" disabled selected>-- Select Severity --</option>
                                    <option value="Production System Down">Production System Down</option>
                                    <option value="Restricted Operations">Restricted Operations</option>
                                    <option value="Question/Inconvenience">Question/Inconvenience</option>
                                </select>
                                <div class="invalid-feedback">Please select a severity level.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="subjectSelect" class="form-label">Subject:</label>
                            <select name="subject_base" id="subjectSelect" class="form-control" required>
                                <option value="" disabled selected>-- Select Subject --</option>
                                <option value="Installation Issue">Installation Issue</option>
                                <option value="Configuration Problem">Configuration Problem</option>
                                <option value="Usage Question">Usage Question</option>
                                <option value="Bug Report">Bug Report</option>
                                <option value="Feature Request">Feature Request</option>
                                <option value="Other">Other (Please specify)</option>
                            </select>
                            <div class="invalid-feedback">Please select a subject or 'Other'.</div>
                        </div>
                        <div class="mb-3" id="otherSubjectContainer" style="display: none;">
                            <label for="subjectOther" class="form-label">Specify Subject:</label>
                            <input type="text" name="subject_other" id="subjectOther" placeholder="Enter subject details" class="form-control">
                            <div class="invalid-feedback">Please specify the subject.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-text mb-2 font-weight-bold">Product Information</div>
                        <div class="mb-3">
                            <label for="serialNumber" class="form-label">Serial Number:</label>
                            <div class="input-group">
                                <input type="text" name="serial_number" id="serialNumber" placeholder="Enter Serial Number" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="validateSerialBtn">Validate</button>
                            </div>
                            <div id="serialValidationStatus" class="form-text small mt-1"></div>
                            <div class="invalid-feedback">Serial number is required.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company" class="form-label">Company:</label>
                                <input type="text" name="company" id="company" placeholder="Autofilled from Product Name" required class="form-control" readonly>
                                <div id="companyStatus" class="form-text small mt-1"></div>
                                <div class="invalid-feedback">Company name is required.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="productGroup" class="form-label">Product Group:</label>
                                <input type="text" name="product_group" placeholder="Autofilled from Serial" id="productGroup" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="productVersion" class="form-label">Product Version:</label>
                            <input type="text" name="product_version" placeholder="Autofilled from Serial" id="productVersion" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-text mb-2 font-weight-bold">Additional Information</div>
                        <div class="mb-3">
                            <label for="attachment" class="form-label">Attachment (Optional):</label>
                            <input type="file" name="attachment" id="attachment" class="form-control" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx">
                            <div class="form-text small text-muted">Allowed formats: JPG, PNG, PDF, DOC, DOCX. Max size: 10 MB. One file only.</div>
                            <div id="attachmentError" class="text-danger small mt-1"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="createCaseBtn" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {

    // --- Element References ---
    const newCaseForm = document.getElementById("newCaseForm");
    const productNameSelect = document.getElementById("productName");
    const companyInput = document.getElementById("company");
    const companyStatus = document.getElementById("companyStatus");
    const subjectSelect = document.getElementById("subjectSelect");
    const otherSubjectContainer = document.getElementById("otherSubjectContainer");
    const subjectOtherInput = document.getElementById("subjectOther");
    const serialInput = document.getElementById("serialNumber");
    const validateSerialBtn = document.getElementById("validateSerialBtn");
    const productGroupInput = document.getElementById("productGroup");
    const productVersionInput = document.getElementById("productVersion");
    const serialValidationStatus = document.getElementById("serialValidationStatus");
    const attachmentInput = document.getElementById("attachment"); // Uses ID 'attachment'
    const attachmentError = document.getElementById("attachmentError");
    const createCaseBtn = document.getElementById("createCaseBtn");

    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB in bytes
    // Ensure these match the 'accept' attribute and PHP $allowed_types (adjust PHP if needed)
    const ALLOWED_EXTENSIONS = ['jpeg', 'jpg', 'png', 'pdf', 'doc', 'docx'];

    // --- Initial Setup ---
    // No initial AJAX needed as product list is hardcoded in HTML

    // --- Event Listeners ---

    // 1. Product Name Change -> Autofill Company
    productNameSelect.addEventListener("change", function() {
        const selectedOption = this.options[this.selectedIndex];
        // Get company from the hardcoded data-company attribute
        const company = selectedOption.dataset && selectedOption.dataset.company ? selectedOption.dataset.company : '';

        if (selectedOption.value && company) { // Check if a valid product is selected AND company data exists
            companyInput.value = company;
            companyStatus.textContent = 'Company autofilled.';
            companyStatus.className = 'form-text small mt-1 text-success';
            companyInput.readOnly = true; // Keep readonly when autofilled
        } else {
             // If default option selected or no company data found for the option
            companyInput.value = "";
            companyStatus.textContent = ''; // Clear status message
            companyInput.readOnly = true; // Keep readonly if no product selected or no company known
             // Optional: Allow manual entry if company missing for a valid product
             // if (selectedOption.value) {
             //     companyStatus.textContent = 'Company not found. Please enter manually.';
             //     companyStatus.className = 'form-text small mt-1 text-warning';
             //     companyInput.readOnly = false;
             // } else {
             //     companyInput.readOnly = true; // Readonly for default option
             // }
        }
        // Trigger validation update if using Bootstrap's JS
        if (typeof $ === 'function') { $(companyInput).trigger('input'); }
        companyInput.dispatchEvent(new Event('input', { bubbles: true }));
    });

    // 2. Subject Change -> Show/Hide "Other" Input
    subjectSelect.addEventListener("change", function() {
        if (this.value === "Other") {
            otherSubjectContainer.style.display = "block";
            subjectOtherInput.required = true; // Make required when shown
        } else {
            otherSubjectContainer.style.display = "none";
            subjectOtherInput.required = false; // Make not required when hidden
            subjectOtherInput.value = ""; // Clear value when hiding
            // If Bootstrap validation was already applied, remove invalid state
             if (subjectOtherInput.classList.contains('is-invalid')) {
                  subjectOtherInput.classList.remove('is-invalid');
             }
        }
         // Trigger validation update (important for Bootstrap)
         if (typeof $ === 'function') { $(subjectOtherInput).trigger('input').trigger('blur'); } // Trigger input and blur
         subjectOtherInput.dispatchEvent(new Event('input', { bubbles: true }));
         subjectOtherInput.dispatchEvent(new Event('blur', { bubbles: true }));
         // Re-validate the form if possible (might require manual triggering depending on setup)
         if (newCaseForm.classList.contains('was-validated')) {
             newCaseForm.checkValidity();
         }
    });

    // 3. Validate Serial Number Button Click
    validateSerialBtn.addEventListener("click", function() {
        const serialNumber = serialInput.value.trim();
        serialValidationStatus.textContent = 'Validating...';
        serialValidationStatus.className = 'form-text small mt-1 text-info';
        productGroupInput.value = ""; // Clear previous values
        productVersionInput.value = "";

        if (serialNumber.length > 2) {
            // Ensure this endpoint exists and returns expected JSON
            fetch("../fetch/product.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "serial_number=" + encodeURIComponent(serialNumber),
            })
            .then(response => {
                 if (!response.ok) { throw new Error(`Network response was not ok (${response.status})`); }
                 return response.json();
            })
            .then(data => {
                if (data.success && data.product_group && data.product_version) {
                    productGroupInput.value = data.product_group;
                    productVersionInput.value = data.product_version;
                    serialValidationStatus.textContent = 'Serial number validated successfully.';
                    serialValidationStatus.className = 'form-text small mt-1 text-success';
                     // Trigger validation update
                     if (typeof $ === 'function') { $(productGroupInput).trigger('input'); $(productVersionInput).trigger('input'); }
                     productGroupInput.dispatchEvent(new Event('input', { bubbles: true }));
                     productVersionInput.dispatchEvent(new Event('input', { bubbles: true }));
                } else {
                    serialValidationStatus.textContent = data.message || 'Invalid or inactive serial number.';
                    serialValidationStatus.className = 'form-text small mt-1 text-danger';
                }
            })
            .catch(error => {
                console.error("Error fetching product details:", error);
                serialValidationStatus.textContent = 'Error during validation. Please try again.';
                serialValidationStatus.className = 'form-text small mt-1 text-danger';
            });
        } else {
            serialValidationStatus.textContent = 'Please enter a valid serial number (min 3 chars).';
            serialValidationStatus.className = 'form-text small mt-1 text-warning';
        }
    });

     // 3b. Reset autofilled fields if serial number is changed *after* validation
     serialInput.addEventListener('input', function() {
         // Only reset if validation was previously successful
         if (serialValidationStatus.classList.contains('text-success')) {
             productGroupInput.value = "";
             productVersionInput.value = "";
             serialValidationStatus.textContent = 'Serial number changed, please re-validate.';
             serialValidationStatus.className = 'form-text small mt-1 text-warning';
         } else if (serialInput.value.trim() === '' && serialValidationStatus.textContent !== '') {
            // Clear status if input is cleared
            serialValidationStatus.textContent = '';
         }
     });

    // 4. Attachment Validation (Size and Type)
    attachmentInput.addEventListener("change", function(event) {
        attachmentError.textContent = ""; // Clear previous error
        const file = event.target.files[0];

        if (file) {
            // Check Size
            if (file.size > MAX_FILE_SIZE) {
                attachmentError.textContent = `File too large (Max: ${Math.round(MAX_FILE_SIZE / 1024 / 1024)} MB).`;
                event.target.value = null; // Clear the selected file
                return; // Stop further processing
            }

            // Check Type (Extension)
            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (!ALLOWED_EXTENSIONS.includes(fileExtension)) {
                attachmentError.textContent = `Invalid file type. Allowed: ${ALLOWED_EXTENSIONS.join(', ')}.`;
                event.target.value = null; // Clear the selected file
                return; // Stop further processing
            }
        }
         // Trigger validation update if needed (though file inputs often don't need this for Bootstrap validation itself)
         if (typeof $ === 'function') { $(attachmentInput).trigger('input'); }
         attachmentInput.dispatchEvent(new Event('input', { bubbles: true }));
    });

    // 5. Bootstrap Form Validation Initialization & Custom Submit Logic
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                 // Explicitly check 'Other' subject requirement before submitting
                 if (subjectSelect.value === "Other" && !subjectOtherInput.value.trim()) {
                     subjectOtherInput.required = true; // Ensure it's marked required
                     // Manually add is-invalid class because checkValidity might not catch it if it was hidden before
                     subjectOtherInput.classList.add('is-invalid');
                     // Find the feedback div associated with it (assuming it's the next sibling)
                     const feedback = subjectOtherInput.nextElementSibling;
                     if (feedback && feedback.classList.contains('invalid-feedback')) {
                         feedback.style.display = 'block';
                     }
                 } else if (subjectSelect.value !== "Other") {
                    // Ensure it's not required if 'Other' isn't selected
                    subjectOtherInput.required = false;
                    subjectOtherInput.classList.remove('is-invalid'); // Remove potential invalid state
                    const feedback = subjectOtherInput.nextElementSibling;
                     if (feedback && feedback.classList.contains('invalid-feedback')) {
                         feedback.style.display = 'none';
                     }
                 }

                // Check Bootstrap validity *after* custom checks
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated') // Add class to show validation styles

                // Optional: Disable submit button after first click to prevent double submissions
                // if (form.checkValidity()) { // Only disable if the form is actually valid and ready to submit
                //    createCaseBtn.disabled = true;
                //    createCaseBtn.textContent = 'Creating...';
                // }

            }, false) // end submit listener
        }) // end forEach form
    })() // end self-invoking function

}); // End DOMContentLoaded
</script>