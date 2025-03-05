<div class="modal fade" id="assignCase" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Assign Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="../process/assign_case.php" method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="case_id" id="caseId">
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
                        <button type="submit" class="btn btn-primary">Assign</button>
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