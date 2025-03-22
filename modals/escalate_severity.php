<div class="modal fade" id="escalateSeverity" tabindex="-1" role="dialog" aria-labelledby="escalateSeverityLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="escalateSeverityLabel">Escalate Severity</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="../process/escalate_severity.php" method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="case_id" id="caseId">
                    <div class="mb-3">
                        <label for="severity">Severity</label>
                        <select name="severity" required id="severity" class="form-control"></select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Escalate Severity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>