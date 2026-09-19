<!-- Detailed Comment: Modal for editing existing secretary details and credentials by admin -->
<div class="modal fade" data-bs-backdrop="static" id="edit_secretary_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-pen"></i> Edit Secretary
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body d-flex flex-column gap-3" id="edit_secretary_form">
                @csrf
                <input type="hidden" name="secrefno" id="edit_sec_refno">

                <div class="d-flex gap-2">
                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_secfname">First Name</label>
                        <input class="form-control" type="text" name="secfname" id="edit_secfname" required>
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_secmname">Middle Name</label>
                        <input class="form-control" type="text" name="secmname" id="edit_secmname">
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_seclname">Last Name</label>
                        <input class="form-control" type="text" name="seclname" id="edit_seclname" required>
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_secsuffix">Suffix</label>
                        <input class="form-control" type="text" name="secsuffix" id="edit_secsuffix" maxlength="10">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_secusername">Username</label>
                        <input class="form-control" type="text" name="username" id="edit_secusername" required>
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_secgender">Sex</label>
                        <select class="form-select" name="secgender" id="edit_secgender">
                            <option value="male">MALE</option>
                            <option value="female">FEMALE</option>
                        </select>
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_secbday">Birthdate</label>
                        <input class="form-control" type="date" name="secbday" id="edit_secbday">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_seccontactno">Contact #</label>
                        <input class="form-control" type="tel" name="seccontactno" id="edit_seccontactno"
                            placeholder="(+63)">
                    </div>

                    <div class="d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="edit_secemail">Email Address</label>
                        <input class="form-control" type="email" name="secemail" id="edit_secemail">
                    </div>
                </div>

                <div class="d-flex flex-column">
                    <label class="form-label fw-bold" for="edit_secadrs">Address</label>
                    <input class="form-control" type="text" name="secadrs" id="edit_secadrs">
                </div>

                <div class="d-flex flex-column">
                    <label class="form-label fw-bold" for="edit_secpassword">New Password <span
                            class="text-secondary fw-normal">(Leave blank to keep current)</span></label>
                    <input class="form-control" type="password" name="secpassword" id="edit_secpassword"
                        placeholder="Leave blank to keep unchanged">
                </div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save_edit_secretary_btn">Save Changes</button>
            </div>
        </div>
    </div>
</div>