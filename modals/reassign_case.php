<div class="modal fade" id="reassignCase" tabindex="-1" role="dialog" aria-labelledby="reassignCaseLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reassignCaseLabel">Reassign Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="../process/reassign_case.php" method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="case_id" id="caseId">
                    <input type="hidden" id="engineerId">
                    <div class="mb-3">
                        <label for="engineer">Engineer</label>
                        <select name="engineer" required id="engineer" class="form-control">
                            <option value="" selected disabled></option>
                            <?php
                            foreach ($engineers as $row) {
                                $engineerName = $row["full_name"];

                                echo '<option value="' . $row["id"] . '">';
                                echo $engineerName;
                                echo "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Reassign Case</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>