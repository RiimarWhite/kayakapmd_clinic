{{--
  Detailed Comment: Add New Patient Modal.
  Supports capturing full demographic, address, PhilHealth, and PWD/Senior attributes matching pxmasterlist.
--}}
<div class="modal fade" data-bs-backdrop="static" id="add_patient_modal" tabindex="-1" aria-labelledby="addPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h3 class="modal-title fw-bold m-0"><i class="fa-solid fa-user-plus text-success me-2"></i> Add New Patient</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form class="d-flex flex-column gap-3" id="add_patient_form">
                    @csrf

                    <div>
                        <h6 class="fw-bold border-bottom pb-1 text-primary"><i class="fa-solid fa-id-card me-1"></i> Patient Identity</h6>
                        <div class="row g-2 mt-1">
                            <div class="col-md-3">
                                <label class="form-label fw-bold small" for="pPatientFname">First Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="pPatientFname" id="pPatientFname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small" for="pPatientMname">Middle Name</label>
                                <input class="form-control" type="text" name="pPatientMname" id="pPatientMname">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small" for="pPatientLname">Last Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="pPatientLname" id="pPatientLname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small" for="pPatientExtname">Suffix</label>
                                <input class="form-control" type="text" name="pPatientExtname" id="pPatientExtname" placeholder="Jr., Sr., III" maxlength="10">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold small" for="pPatientSex">Sex <span class="text-danger">*</span></label>
                                <select class="form-select" name="pPatientSex" id="pPatientSex" required>
                                    <option value="" selected disabled>Select</option>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small" for="pPatientDob">Date of Birth <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="pPatientDob" id="pPatientDob" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small" for="phic_pin">PhilHealth PIN</label>
                                <input class="form-control" type="text" name="phic_pin" id="phic_pin" placeholder="00-000000000-0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small" for="religion">Religion</label>
                                <input class="form-control" type="text" name="religion" id="religion" placeholder="e.g. Roman Catholic">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small" for="nationality">Nationality</label>
                                <input class="form-control" type="text" name="nationality" id="nationality" value="FILIPINO">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small" for="ispwd">PWD Status</label>
                                <select class="form-select" name="ispwd" id="ispwd">
                                    <option value="0" selected>No (Non-PWD)</option>
                                    <option value="1">Yes (Person with Disability)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small" for="senior_idno">Senior Citizen ID No.</label>
                                <input class="form-control" type="text" name="senior_idno" id="senior_idno" placeholder="OSCA / Senior Citizen ID">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="fw-bold border-bottom pb-1 text-primary"><i class="fa-solid fa-phone me-1"></i> Contact Information</h6>
                        <div class="row g-2 mt-1">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small" for="pPatientMobileNo">Mobile Number <span class="text-danger">*</span></label>
                                <input class="form-control" type="tel" name="pPatientMobileNo" id="pPatientMobileNo" placeholder="09xxxxxxxxx" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small" for="email">Email Address</label>
                                <input class="form-control" type="email" name="email" id="email" placeholder="patient@example.com">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="fw-bold border-bottom pb-1 text-primary"><i class="fa-solid fa-location-dot me-1"></i> Address Details</h6>
                        <div class="row g-2 mt-1">
                            <div class="col-12">
                                <label class="form-label fw-bold small" for="address">Full Address</label>
                                <input class="form-control" type="text" name="address" id="address" placeholder="Full address line">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small" for="streetadrs">Street Address</label>
                                <input class="form-control" type="text" name="streetadrs" id="streetadrs" placeholder="House No. / Street">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small" for="brgy">Barangay</label>
                                <input class="form-control" type="text" name="brgy" id="brgy" placeholder="Barangay">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small" for="muncity">Municipality / City</label>
                                <input class="form-control" type="text" name="muncity" id="muncity" placeholder="City or Municipality">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold small" for="province">Province</label>
                                <input class="form-control" type="text" name="province" id="province" placeholder="Province">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small" for="zipcode">Zip Code</label>
                                <input class="form-control" type="text" name="zipcode" id="zipcode" placeholder="Zip code">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small" for="region">Region</label>
                                <input class="form-control" type="text" name="region" id="region" placeholder="Region">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small" for="country">Country</label>
                                <input class="form-control" type="text" name="country" id="country" value="PHILIPPINES">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success fw-bold" id="add_patient_btn">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Patient
                </button>
            </div>
        </div>
    </div>
</div>
