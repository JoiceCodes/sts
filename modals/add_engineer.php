<div class="modal fade" id="newEngineer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">New Engineer</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="../process/new_engineer.php" method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <p class="form-text">Personal Information</p>
                        <div class="mb-3">
                            <input type="text" name="first_name" required placeholder="First Name" id="firstName" class="form-control">
                        </div>
                        <div class="mb-3">
                            <input type="text" name="middle_name" placeholder="Middle Name" id="middleName" class="form-control">
                        </div>
                        <div class="mb-3">
                            <input type="text" name="last_name" required placeholder="Last Name" id="lastName" class="form-control">
                        </div>
                        <div>
                            <label for="suffix">Suffix</label>
                            <select name="suffix" id="suffix" class="form-control">
                                <option value=""></option>
                                <option value="Sr.">Sr.</option>
                                <option value="Jr.">Jr.</option>
                                <option value="I">I</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <p class="form-text">Contact Information</p>
                        <div class="mb-3">
                            <input type="email" name="email" required placeholder="Email Address" id="email" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="form-text">Login Credentials</p>
                        <div class="mb-3">
                            <input type="text" name="username" required placeholder="Username" id="username" class="form-control">
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password" required placeholder="Password" id="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <input type="password" name="repeat_password" required placeholder="Repeat Password" id="repeatPassword" class="form-control">
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