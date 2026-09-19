<!-- Detailed Comment: Modal for editing existing admin user details and credentials -->
<div class="modal fade" data-bs-backdrop="static" id="edit_admin_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-shield"></i> Edit Administrator
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body d-flex flex-column gap-3" id="edit_admin_form">
                @csrf
                <input type="hidden" name="id" id="edit_admin_id">
                <input type="hidden" name="adminrefno" id="edit_admin_refno">

                <div class="d-flex gap-2">
                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_adminfname">First Name</label>
                        <input class="form-control" type="text" name="adminfname" id="edit_adminfname" required>
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_adminmname">Middle Name</label>
                        <input class="form-control" type="text" name="adminmname" id="edit_adminmname">
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_adminlname">Last Name</label>
                        <input class="form-control" type="text" name="adminlname" id="edit_adminlname" required>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_adminusername">Username</label>
                        <input class="form-control" type="text" name="username" id="edit_adminusername" required>
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_admincontactno">Contact #</label>
                        <input class="form-control" type="tel" name="admincontactno" id="edit_admincontactno"
                            placeholder="(+63)">
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_adminemail">Email Address</label>
                        <input class="form-control" type="email" name="adminemail" id="edit_adminemail">
                    </div>
                </div>

                <div class="d-flex flex-column">
                    <label class="form-label fw-bold" for="edit_adminpassword">New Password <span
                            class="text-secondary fw-normal">(Leave blank to keep current)</span></label>
                    <input class="form-control" type="password" name="password" id="edit_adminpassword"
                        placeholder="Leave blank to keep unchanged">
                </div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save_edit_admin_btn">Save Changes</button>
            </div>
        </div>
    </div>
</div>