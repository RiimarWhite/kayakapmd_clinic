<div class="modal fade" data-bs-backdrop="static" id="add_patient_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fa-solid fa-user"></i> Add New Patient</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-4 p-4">
                <form class="d-flex flex-column gap-4" id="add_patient_form">
                    @csrf

                    <div class="d-flex flex-column gap-2">
                        <p class="m-0 fw-bold" style="font-size: 16px;">Patient Information</p>
                        <div class="d-flex gap-2 ms-2">
                            <div class="">
                                <label class="form-label fw-bold" for="pPatientFname">First Name</label>
                                <input class="form-control" type="text" name="pPatientFname" id="pPatientFname"
                                    required>
                            </div>

                            <div class="">
                                <label class="form-label fw-bold" for="pPatientMname">Middle Name</label>
                                <input class="form-control" type="text" name="pPatientMname" id="pPatientMname">
                            </div>

                            <div class="">
                                <label class="form-label fw-bold" for="pPatientLname">Last Name</label>
                                <input class="form-control" type="text" name="pPatientLname" id="pPatientLname"
                                    required>
                            </div>

                            <div class="">
                                <label class="form-label fw-bold" for="pPatientExtname">Suffix</label>
                                <input class="form-control" type="text" name="pPatientExtname" id="pPatientExtname" maxlength="10">
                            </div>

                        </div>
                    </div>

                    <div class="d-flex gap-2 ms-2">
                        <div class="">
                            <label class="form-label fw-bold" for="pPatientSex">Sex</label>
                            <select class="form-select" name="pPatientSex" id="pPatientSex" style="width: 100px;"
                                required>
                                <option selected disabled>Select</option>
                                <option value="MALE">Male</option>
                                <option value="FEMALE">Female</option>
                            </select>
                        </div>

                        <div class=" d-flex flex-column flex-grow-1">
                            <label class="form-label fw-bold" for="pPatientDob">Date of Birth</label>
                            <input class="form-control" type="date" name="pPatientDob" id="pPatientDob" required>
                        </div>

                        <div class="">
                            <label class="form-label fw-bold" for="pPatientMobileNo">Mobile Number</label>
                            <input class="form-control" type="tel" name="pPatientMobileNo" id="pPatientMobileNo"
                                required>
                        </div>

                        <div class="">
                            <label class="form-label fw-bold" for="pPatientLandlineNo">Landline Number</label>
                            <input class="form-control" type="tel" name="pPatientLandlineNo"
                                id="pPatientLandlineNo">
                        </div>

                        <div class="">
                            <label class="form-label fw-bold" for="email">Email Address</label>
                            <input class="form-control" type="email" name="email" id="email">
                        </div>
                    </div>

                    <div class="d-flex gap-2 w-100">
                        <div class=" ms-2 w-100">
                            <label class="form-label fw-bold" for="address">Address</label>
                            <input class="form-control" type="text" name="address" id="address">
                        </div>

                        <!-- <div class="d-flex gap-2 w-50">
                            <div class=" w-100">
                                <label class="form-label fw-bold" for="pxsched">Consultation Date</label>
                                <div class="input-group">
                                    <button class="btn btn-primary" type="button" id="add_patient_prev">
                                        <span class="fa-solid fa-angle-left"></span>
                                    </button>
                                    <input class="form-control" type="date" name="pxsched" id="pxsched" value="">
                                    <button class="btn btn-primary" type="button" id="add_patient_next">
                                        <span class="fa-solid fa-angle-right"></span>
                                    </button>
                                </div>
                                <div class="form-text">Consultation date can be set later.</div>
                            </div>

                            <div class=" w-100">
                                <label class="form-label fw-bold" for="pxtime">Consultation Time</label>
                                <select class="form-select" name="pxtime" id="pxtime">
                                    <option value="" selected disabled>No date selected.</option>
                                </select>
                            </div>
                        </div> -->
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" id="add_patient_btn">Add Patient</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
