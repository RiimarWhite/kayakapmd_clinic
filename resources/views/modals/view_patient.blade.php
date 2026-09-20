{{--
  Detailed Comment: Comprehensive Patient Masterlist Details Modal.
  Displays all attributes from pxmasterlist organized logically across Personal & Identity,
  Contact & Address, and Clinic & Audit details.
--}}
<div class="modal fade" id="viewPatientModal" tabindex="-1" aria-labelledby="viewPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div class="d-flex align-items-center gap-3">
                    <img id="view_patient_photo" src="/images/blank_photo.png" alt="Patient Photo" class="rounded rounded-circle border shadow-sm" style="width: 55px; height: 55px; object-fit: cover;">
                    <div>
                        <h4 class="modal-title fw-bold m-0" id="view_patient_header_name">Patient Details</h4>
                        <div class="text-muted small">PIN: <span id="view_patient_header_pin" class="fw-semibold">N/A</span> | Ref: <span id="view_patient_header_ref" class="fw-semibold">N/A</span></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs mb-3" id="viewPatientTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold" id="view-personal-tab" data-bs-toggle="tab" data-bs-target="#view-personal" type="button" role="tab"><i class="fa-solid fa-user me-1"></i> Personal & Identity</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="view-contact-tab" data-bs-toggle="tab" data-bs-target="#view-contact" type="button" role="tab"><i class="fa-solid fa-location-dot me-1"></i> Contact & Address</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="view-clinic-tab" data-bs-toggle="tab" data-bs-target="#view-clinic" type="button" role="tab"><i class="fa-solid fa-notes-medical me-1"></i> Clinic & Audit Trail</button>
                    </li>
                </ul>

                <div class="tab-content" id="viewPatientTabContent">
                    <!-- Tab 1: Personal & Identity -->
                    <div class="tab-pane fade show active" id="view-personal" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">PIN Code</label>
                                <input class="form-control bg-light" type="text" id="view_pincode" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Patient Ref No.</label>
                                <input class="form-control bg-light" type="text" id="view_pxrefno" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">PhilHealth PIN</label>
                                <input class="form-control bg-light" type="text" id="view_phic_pin" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">IPD PIN</label>
                                <input class="form-control bg-light" type="text" id="view_ipd_pincode" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">First Name</label>
                                <input class="form-control bg-light" type="text" id="view_pxfirstname" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Middle Name</label>
                                <input class="form-control bg-light" type="text" id="view_pxmidname" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Last Name</label>
                                <input class="form-control bg-light" type="text" id="view_pxlastname" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Suffix</label>
                                <input class="form-control bg-light" type="text" id="view_pxsuffix" readonly>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted">Sex</label>
                                <input class="form-control bg-light" type="text" id="view_gender" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Date of Birth</label>
                                <input class="form-control bg-light" type="text" id="view_birthday" readonly>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-bold text-muted">Age</label>
                                <input class="form-control bg-light text-center" type="text" id="view_age" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Religion</label>
                                <input class="form-control bg-light" type="text" id="view_religion" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Nationality</label>
                                <input class="form-control bg-light" type="text" id="view_nationality" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">PWD Status</label>
                                <input class="form-control bg-light" type="text" id="view_ispwd" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Senior Citizen ID No.</label>
                                <input class="form-control bg-light" type="text" id="view_senior_idno" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Classification</label>
                                <input class="form-control bg-light" type="text" id="view_classification" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Contact & Address -->
                    <div class="tab-pane fade" id="view-contact" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Mobile Number</label>
                                <input class="form-control bg-light" type="text" id="view_mobilenumber" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Email Address</label>
                                <input class="form-control bg-light" type="text" id="view_emailaddress" readonly>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Full Address</label>
                                <input class="form-control bg-light" type="text" id="view_address" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Street Address</label>
                                <input class="form-control bg-light" type="text" id="view_streetadrs" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Barangay</label>
                                <input class="form-control bg-light" type="text" id="view_brgy" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Municipality / City</label>
                                <input class="form-control bg-light" type="text" id="view_muncity" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Province</label>
                                <input class="form-control bg-light" type="text" id="view_province" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Zip Code</label>
                                <input class="form-control bg-light" type="text" id="view_zipcode" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Region</label>
                                <input class="form-control bg-light" type="text" id="view_region" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Country</label>
                                <input class="form-control bg-light" type="text" id="view_country" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Clinic & Audit Trail -->
                    <div class="tab-pane fade" id="view-clinic" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Last Consultation Date</label>
                                <input class="form-control bg-light" type="text" id="view_last_consultation" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Last Attending Doctor</label>
                                <input class="form-control bg-light" type="text" id="view_last_docname" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Follow-up Date</label>
                                <input class="form-control bg-light" type="text" id="view_followupdate" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Follow-up Checkup</label>
                                <input class="form-control bg-light" type="text" id="view_followupcheckup" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Recorded By</label>
                                <input class="form-control bg-light" type="text" id="view_recordedby" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Recorded Date</label>
                                <input class="form-control bg-light" type="text" id="view_recordeddate" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Updated By</label>
                                <input class="form-control bg-light" type="text" id="view_updatedby" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Last Updated Date</label>
                                <input class="form-control bg-light" type="text" id="view_updated" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
