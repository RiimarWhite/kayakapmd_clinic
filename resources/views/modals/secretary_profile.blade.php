<!-- Detailed Comment: Self-service Secretary Profile Modal for Secretary Dashboard.
     Allows authenticated secretary to view and edit their own profile and credentials
     directly from the 'secretaryrights' table, strictly isolated to their own account. -->
<div class="modal fade" data-bs-backdrop="static" id="secretary_profile_modal" tabindex="-1"
    aria-labelledby="secProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h4 class="modal-title fw-bold" id="secProfileModalLabel">
                    <i class="fa-solid fa-user-nurse text-info me-2"></i> My Secretary Profile
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body d-flex flex-column gap-3 p-4" id="secretary_profile_form">
                @csrf

                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label fw-bold" for="my_secfname">First Name <span
                                class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="secfname" id="my_secfname" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" for="my_secmname">Middle Name</label>
                        <input class="form-control" type="text" name="secmname" id="my_secmname">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" for="my_seclname">Last Name <span
                                class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="seclname" id="my_seclname" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" for="my_secsuffix">Suffix</label>
                        <input class="form-control" type="text" name="secsuffix" id="my_secsuffix" maxlength="10">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="my_secusername">Username <span
                                class="text-danger">*</span></label>
                        <input class="form-control font-monospace" type="text" name="username" id="my_secusername"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="my_secpassword">New Password</label>
                        <input class="form-control" type="password" name="secpassword" id="my_secpassword"
                            placeholder="Leave blank to keep unchanged">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="my_secgender">Sex</label>
                        <select class="form-select" name="secgender" id="my_secgender">
                            <option value="MALE">MALE</option>
                            <option value="FEMALE">FEMALE</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="my_secbday">Birthdate</label>
                        <input class="form-control" type="date" name="secbday" id="my_secbday">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="my_seccontactno">Contact #</label>
                        <input class="form-control" type="tel" name="seccontactno" id="my_seccontactno"
                            placeholder="(+63)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="my_secemail">Email Address</label>
                        <input class="form-control" type="email" name="secemail" id="my_secemail">
                    </div>
                </div>

                <div>
                    <label class="form-label fw-bold" for="my_secadrs">Home Address</label>
                    <input class="form-control" type="text" name="secadrs" id="my_secadrs">
                </div>
            </form>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info text-white fw-bold" id="save_secretary_profile_btn">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Profile
                </button>
            </div>
        </div>
    </div>
</div>