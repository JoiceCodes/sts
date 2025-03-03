<div class="modal fade" id="acceptCase" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Accept Case</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to accept this cases?
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">
                    No
                </button>
                <form action="../process/accept_case.php" method="post">
                    <input type="hidden" name="case_id" id="caseId">
                    <button type="submit" class="btn btn-primary">Yes</button>
                </form>
                <!-- <a class="btn btn-primary" href="login.html">Yes</a> -->
            </div>
        </div>
    </div>
</div>