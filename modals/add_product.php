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
                <form action="../process/add_product.php" method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <input type="text" name="name" required placeholder="Product Name" id="name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="category" required placeholder="Product Category" id="category" class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="type" required placeholder="Product Type" id="type" class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="version" required placeholder="Product Version" id="version" class="form-control">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="supported_platform[]"  value="Windows" id="windowsCheck">
                            <label class="form-check-label" for="windowsCheck">
                                Windows
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="supported_platform[]" value="MacOS" id="macOsCheck" checked>
                            <label class="form-check-label" for="macOsCheck">
                                MacOS
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="license_type" required placeholder="License Type" id="licenseType" class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="serial_number" required placeholder="Serial Number" id="serialNumber" class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="license_duration" required placeholder="License Duration" id="licenseDuration" class="form-control">
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