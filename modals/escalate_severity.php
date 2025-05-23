<div class="modal fade" id="escalateSeverity" tabindex="-1" role="dialog" aria-labelledby="escalateSeverityLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="../process/escalate_severity.php" method="POST" id="escalateForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="escalateSeverityLabel">Escalate Case Priority</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="case_id" id="escalateCaseId" value="">
                    <input type="hidden" id="escalateCurrentPriorityNum" value=""> <p>Escalating Case: <strong id="escalateCaseNumberDisplay">[Case Number]</strong></p>
                    <p>Current Severity: <strong id="escalateCurrentSeverityDisplay">[Current Severity]</strong></p>

                    <div class="form-group">
                        <label for="new_priority">Select New Priority Level:</label>
                        <select class="form-control" id="new_priority" name="new_priority" required>
                            <option value="" disabled selected>-- Choose New Priority --</option>
                            <option value="1">P1 - Production System Down</option>
                            <option value="2">P2 - Restricted Operations</option>
                            <option value="3">P3 - System Impaired</option>
                            <option value="4">P4 - General Guidance</option>
                        </select>
                        <small id="priorityWarning" class="text-danger mt-1" style="display: none;">New priority cannot be the same as current.</small>
                    </div>
                    <div class="form-group">
                        <label for="escalation_reason">Reason for Escalation (Optional):</label>
                        <textarea class="form-control" id="escalation_reason" name="escalation_reason" rows="3" placeholder="Enter justification for the priority change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="confirmEscalateBtn" disabled>Confirm Escalation</button>
                </div>
            </form>
        </div>
    </div>
</div>