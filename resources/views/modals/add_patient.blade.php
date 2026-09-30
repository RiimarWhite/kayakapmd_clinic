{{--
  Detailed Comment: Add New Patient Modal with Tabbed UI.
  Structured into Personal & Identity, Contact & Address, and Clinical/Classification tabs.
  Resolves prior div nesting discrepancies and matches pxmasterlist schema.
--}}
<div class="modal fade" data-bs-backdrop="static" id="add_patient_modal" tabindex="-1" aria-labelledby="addPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h4 class="modal-title fw-bold m-0" id="addPatientModalLabel">
                    <i class="fa-solid fa-user-plus text-success me-2"></i> Add New Patient
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form id="add_patient_form">
                    @csrf

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-3" id="addPatientTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="add-personal-tab" data-bs-toggle="tab" data-bs-target="#add-personal" type="button" role="tab">
                                <i class="fa-solid fa-user me-1"></i> Personal & Identity
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="add-contact-tab" data-bs-toggle="tab" data-bs-target="#add-contact" type="button" role="tab">
                                <i class="fa-solid fa-location-dot me-1"></i> Contact & Address
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="add-clinic-tab" data-bs-toggle="tab" data-bs-target="#add-clinic" type="button" role="tab">
                                <i class="fa-solid fa-notes-medical me-1"></i> Clinical & Classification
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="addPatientTabContent">
                        <!-- Tab 1: Personal & Identity -->
                        <div class="tab-pane fade show active" id="add-personal" role="tabpanel">
                            <div class="row g-3">
                                {{-- Detailed Comment: Patient Photo upload and webcam capture controls --}}
                                <div class="col-md-3 d-flex flex-column align-items-center justify-content-start text-center border-end">
                                    <label class="form-label fw-bold small mb-1">Patient Photo</label>
                                    <img class="border border-secondary rounded shadow-sm mb-2"
                                        style="width: 120px; height: 120px; object-fit: cover;"
                                        src="{{ asset('images/blank_photo.png') }}" alt="patient-image"
                                        id="add_patient_picture_preview">
                                    <div class="btn-group btn-group-sm">
                                        <input type="hidden" name="photo_path" id="add_photo_path">
                                        <input type="hidden" name="photo_base64" id="add_photo_base64">
                                        <button type="button" class="btn btn-primary text-nowrap fw-bold" id="add_upload_patient_image">
                                            <i class="fa-solid fa-upload"></i> Upload
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="add_take_photo" title="Take Webcam Snapshot">
                                            <i class="fa-solid fa-camera"></i>
                                        </button>
                                    </div>
                                    <input class="d-none" type="file" accept="image/*" name="patient_image" id="add_patient_image">
                                    <div class="form-text small mt-1">PNG, JPG, or WEBP</div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row g-2">
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

                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small" for="pPatientSex">Sex <span class="text-danger">*</span></label>
                                            <select class="form-select" name="pPatientSex" id="pPatientSex" required>
                                                <option value="" selected disabled>Select</option>
                                                <option value="MALE">Male</option>
                                                <option value="FEMALE">Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small" for="pPatientDob">Date of Birth <span class="text-danger">*</span></label>
                                            <input class="form-control" type="date" name="pPatientDob" id="pPatientDob" required>
                                        </div>
                                        <div class="col-md-5">
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
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small" for="senior_idno">Senior Citizen ID No.</label>
                                            <input class="form-control" type="text" name="senior_idno" id="senior_idno" placeholder="OSCA / Senior Citizen ID">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Contact & Address -->
                        <div class="tab-pane fade" id="add-contact" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small" for="pPatientMobileNo">Mobile Number <span class="text-danger">*</span></label>
                                    <input class="form-control" type="tel" name="pPatientMobileNo" id="pPatientMobileNo" placeholder="09xxxxxxxxx" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small" for="email">Email Address</label>
                                    <input class="form-control" type="email" name="email" id="email" placeholder="patient@example.com">
                                </div>

                                <!-- Detailed Comment: PSGC Cascading Reference Selects -->
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small" for="region">Region <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="region" id="region">
                                        <option value="" selected disabled>-- Select Region --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small" for="province">Province <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="province" id="province" disabled>
                                        <option value="" selected disabled>-- Select Province --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small" for="muncity">Municipality / City <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="muncity" id="muncity" disabled>
                                        <option value="" selected disabled>-- Select Municipality --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small" for="brgy">Barangay <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="brgy" id="brgy" disabled>
                                        <option value="" selected disabled>-- Select Barangay --</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold small" for="streetadrs">Street Address</label>
                                    <input class="form-control form-control-sm" type="text" name="streetadrs" id="streetadrs" placeholder="House No. / Street">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small" for="zipcode">Zip Code</label>
                                    <input class="form-control form-control-sm" type="text" name="zipcode" id="zipcode" placeholder="Zip code">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small" for="country">Country</label>
                                    <input class="form-control form-control-sm" type="text" name="country" id="country" value="PHILIPPINES">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small" for="address">Full Address (Auto-compiled)</label>
                                    <input class="form-control form-control-sm bg-light" type="text" name="address" id="address" placeholder="Auto-compiled address" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Clinical & Classification -->
                        <div class="tab-pane fade" id="add-clinic" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small" for="classification">Patient Classification</label>
                                    <input class="form-control" type="text" name="classification" id="classification" placeholder="e.g. Regular, Indigent, Senior">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small" for="followupdate">Follow-up Date</label>
                                    <input class="form-control" type="date" name="followupdate" id="followupdate">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small" for="followupcheckup">Follow-up Checkup Purpose</label>
                                    <input class="form-control" type="text" name="followupcheckup" id="followupcheckup" placeholder="Follow-up instructions/purpose">
                                </div>
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
