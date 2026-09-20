{{--
  Detailed Comment: Admin Edit Patient Details Modal.
  Allows administrators to edit all pxmasterlist fields including demographic, address,
  PhilHealth, senior/PWD attributes, and clinical follow-up markers.
--}}
<div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="editPatientForm">
            @csrf
            <div class="modal-header bg-light">
                <h4 class="modal-title fw-bold m-0" id="editPatientModalLabel">
                    <i class="fa-solid fa-user-pen text-primary me-2"></i> Edit Patient Record
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <input type="hidden" name="pxrefno" id="edit_pxrefno">

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-3" id="editPatientTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold" id="edit-personal-tab" data-bs-toggle="tab" data-bs-target="#edit-personal" type="button" role="tab"><i class="fa-solid fa-user me-1"></i> Personal & Identity</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="edit-contact-tab" data-bs-toggle="tab" data-bs-target="#edit-contact" type="button" role="tab"><i class="fa-solid fa-location-dot me-1"></i> Contact & Address</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="edit-clinic-tab" data-bs-toggle="tab" data-bs-target="#edit-clinic" type="button" role="tab"><i class="fa-solid fa-calendar-check me-1"></i> Follow-up & Classification</button>
                    </li>
                </ul>

                <div class="tab-content" id="editPatientTabContent">
                    <!-- Tab 1: Personal & Identity -->
                    <div class="tab-pane fade show active" id="edit-personal" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">PIN Code</label>
                                <input class="form-control bg-light" type="text" name="pincode" id="edit_pincode" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Patient Ref No.</label>
                                <input class="form-control bg-light" type="text" id="edit_display_pxrefno" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_phic_pin">PhilHealth PIN</label>
                                <input class="form-control" type="text" name="phic_pin" id="edit_phic_pin" placeholder="00-000000000-0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_ipd_pincode">IPD PIN</label>
                                <input class="form-control" type="text" name="ipd_pincode" id="edit_ipd_pincode">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_pxfirstname">First Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="pxfirstname" id="edit_pxfirstname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_pxmidname">Middle Name</label>
                                <input class="form-control" type="text" name="pxmidname" id="edit_pxmidname">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_pxlastname">Last Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="pxlastname" id="edit_pxlastname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_pxsuffix">Suffix</label>
                                <input class="form-control" type="text" name="pxsuffix" id="edit_pxsuffix" placeholder="Jr., Sr., III">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_gender">Sex <span class="text-danger">*</span></label>
                                <select class="form-select" name="gender" id="edit_gender" required>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_birthday">Date of Birth</label>
                                <input class="form-control" type="date" name="birthday" id="edit_birthday">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_religion">Religion</label>
                                <input class="form-control" type="text" name="religion" id="edit_religion" placeholder="e.g. Roman Catholic">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_nationality">Nationality</label>
                                <input class="form-control" type="text" name="nationality" id="edit_nationality" value="FILIPINO">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="edit_ispwd">PWD Status</label>
                                <select class="form-select" name="ispwd" id="edit_ispwd">
                                    <option value="0">No (Non-PWD)</option>
                                    <option value="1">Yes (Person with Disability)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="edit_senior_idno">Senior Citizen ID No.</label>
                                <input class="form-control" type="text" name="senior_idno" id="edit_senior_idno" placeholder="OSCA / Senior Citizen ID">
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Contact & Address -->
                    <div class="tab-pane fade" id="edit-contact" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="edit_mobilenumber">Mobile Number</label>
                                <input class="form-control" type="tel" name="mobilenumber" id="edit_mobilenumber" placeholder="09xxxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="edit_emailaddress">Email Address</label>
                                <input class="form-control" type="email" name="emailaddress" id="edit_emailaddress" placeholder="patient@example.com">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold" for="edit_address">Complete Address</label>
                                <input class="form-control" type="text" name="address" id="edit_address" placeholder="Full address line">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="edit_streetadrs">Street Address</label>
                                <input class="form-control" type="text" name="streetadrs" id="edit_streetadrs" placeholder="House No. / Street">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="edit_brgy">Barangay</label>
                                <input class="form-control" type="text" name="brgy" id="edit_brgy" placeholder="Barangay">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="edit_muncity">Municipality / City</label>
                                <input class="form-control" type="text" name="muncity" id="edit_muncity" placeholder="City or Municipality">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_province">Province</label>
                                <input class="form-control" type="text" name="province" id="edit_province" placeholder="Province">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_zipcode">Zip Code</label>
                                <input class="form-control" type="text" name="zipcode" id="edit_zipcode" placeholder="Zip code">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_region">Region</label>
                                <input class="form-control" type="text" name="region" id="edit_region" placeholder="Region">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_country">Country</label>
                                <input class="form-control" type="text" name="country" id="edit_country" value="PHILIPPINES">
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Follow-up & Classification -->
                    <div class="tab-pane fade" id="edit-clinic" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="edit_classification">Patient Classification</label>
                                <input class="form-control" type="text" name="classification" id="edit_classification" placeholder="e.g. Regular, Indigent, Senior">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="edit_followupdate">Follow-up Date</label>
                                <input class="form-control" type="date" name="followupdate" id="edit_followupdate">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="edit_followupcheckup">Follow-up Checkup</label>
                                <input class="form-control" type="text" name="followupcheckup" id="edit_followupcheckup" placeholder="Follow-up instructions/purpose">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold" id="save_edit_patient_btn">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>