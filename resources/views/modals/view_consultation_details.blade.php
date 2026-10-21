{{--
Detailed Comment: Dedicated Consultation Details Viewer Modal for Secretary Console.
Reproduces all vertical tabs and data fields from the Doctor's Consultation Modal,
including Demographics, Vital Signs, Impressions & Diagnosis, Rx & Instructions,
Diagnostic Requests, Radiology & Laboratory attachments, and Patient Charges.
Updated with fluid responsive viewport styles to ensure optimal layout across desktops,
laptops, and tablets without horizontal or vertical clipping.
--}}
<div class="modal fade" data-bs-backdrop="static" id="view_consultation_details_modal" tabindex="-1"
    aria-labelledby="viewConsultationDetailsModalLabel" aria-hidden="true" style="z-index: 1070;">
    {{-- Detailed Comment: Replaced rigid min-width: 85rem with fluid responsive style to prevent viewport overflow on
    laptops (<1400px) and mobile screens --}} <div
        class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl"
        style="min-width: min(85rem, 95vw); max-width: 95vw;">
        {{-- Detailed Comment: Replaced rigid height: 52rem with min-height: min(45rem, 85vh); max-height: 90vh; for
        responsive viewport adaptation --}}
        <div class="modal-content" style="min-height: min(45rem, 85vh); max-height: 90vh;">
            <div class="modal-header d-flex justify-content-between align-items-center bg-light flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h3 class="modal-title m-0 fw-bold text-dark">
                        <i class="fa-solid fa-stethoscope text-primary me-2"></i> Consultation Details
                    </h3>
                    <span class="badge bg-primary fs-6" id="vcd_consultdate_badge">Date: --</span>
                    <span class="badge bg-secondary fs-6" id="vcd_consultref_badge">Ref: --</span>
                    <span class="badge bg-info text-white fs-6" id="vcd_status_badge">Status: --</span>
                    <span class="badge bg-warning text-dark fs-6" id="vcd_patient_type_badge">Type: REGULAR</span>
                    <span class="badge bg-secondary text-white fs-6 d-none" id="vcd_classification_badge">Class:
                        MEDICAL</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-3 p-3">
                {{-- Patient Demographics & Vitals Card --}}
                <div class="card border shadow-sm">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">
                            <i class="fa-solid fa-user me-2 text-primary"></i> Patient Information &amp; Vital Signs
                        </span>
                        <span class="text-muted small">Consulting Doctor: <strong id="vcd_docname"
                                class="text-primary">--</strong></span>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex gap-3 align-items-center mb-2">
                            <img id="vcd_photo" src="/images/blank_photo.png"
                                class="rounded rounded-circle border shadow-sm"
                                style="width: 55px; height: 55px; object-fit: cover;" alt="Patient Photo"
                                onerror="this.src='/images/blank_photo.png'">
                            <div>
                                <h5 class="m-0 fw-bold" id="vcd_name">--</h5>
                                <div class="text-muted small">PIN: <span id="vcd_pincode" class="fw-semibold">N/A</span>
                                    | Ref: <span id="vcd_pxrefno" class="fw-semibold">N/A</span></div>
                            </div>
                        </div>

                        <div class="row g-2 small mb-2">
                            <div class="col-md-3">Sex: <strong id="vcd_sex">--</strong></div>
                            <div class="col-md-3">Birthdate: <strong id="vcd_bday">--</strong> (Age: <strong
                                    id="vcd_age">--</strong>)</div>
                            <div class="col-md-3">Mobile: <strong id="vcd_cellno">--</strong></div>
                            <div class="col-md-3">Address: <strong id="vcd_address">--</strong></div>
                        </div>

                        <hr class="my-2">

                        <div class="row g-2 small text-center">
                            <div class="col-md-2 p-1 bg-light rounded border">
                                <span class="text-muted d-block">Weight</span>
                                <strong id="vcd_weight" class="text-dark">--</strong>
                            </div>
                            <div class="col-md-2 p-1 bg-light rounded border">
                                <span class="text-muted d-block">Height</span>
                                <strong id="vcd_height" class="text-dark">--</strong>
                            </div>
                            <div class="col-md-2 p-1 bg-light rounded border">
                                <span class="text-muted d-block">Temperature</span>
                                <strong id="vcd_temp" class="text-dark">--</strong>
                            </div>
                            <div class="col-md-2 p-1 bg-light rounded border">
                                <span class="text-muted d-block">Resp. Rate</span>
                                <strong id="vcd_resprate" class="text-dark">--</strong>
                            </div>
                            <div class="col-md-2 p-1 bg-light rounded border">
                                <span class="text-muted d-block">Pulse Rate</span>
                                <strong id="vcd_pulserate" class="text-dark">--</strong>
                            </div>
                            <div class="col-md-2 p-1 bg-light rounded border">
                                <span class="text-muted d-block">Blood Pressure</span>
                                <strong id="vcd_bp" class="text-dark">--</strong>
                            </div>
                        </div>

                        {{-- Detailed Comment: Dynamic Patient Classification & Case History display container --}}
                        <div class="mt-2 p-2 bg-light rounded border small d-none"
                            id="vcd_classification_fields_row"></div>
                    </div>
                </div>

                {{-- Detailed Comment: Vertical Tabs reproducing Doctor Consultation Modal structure, adapted with
                responsive flex layout for mobile and tablet screens --}}
                <div class="card d-flex flex-column flex-lg-row p-2 h-100 gap-3 border shadow-sm">
                    <nav class="nav nav-pills d-flex flex-row flex-lg-column gap-1 flex-shrink-0 overflow-x-auto text-nowrap"
                        style="min-width: min(15rem, 100%);">
                        <li class="nav-item">
                            <button class="nav-link active w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#vcd_pane_impdiag" role="tab" type="button">
                                <i class="fa-solid fa-quote-left me-2"></i> Impressions &amp; Diagnosis
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#vcd_pane_rx" role="tab" type="button">
                                <i class="fa-solid fa-prescription me-2"></i> Rx &amp; Instructions
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#vcd_pane_dreqs" role="tab" type="button">
                                <i class="fa-solid fa-envelope-open-text me-2"></i> Diagnostic Requests
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#vcd_pane_radlab" role="tab" type="button">
                                <i class="fa-solid fa-vial me-2"></i> Radiology &amp; Laboratory
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#vcd_pane_charges" role="tab" type="button">
                                <i class="fa-solid fa-coins me-2"></i> Patient Charges
                            </button>
                        </li>
                        {{-- Detailed Comment: Dedicated Medical History vertical tab per Milestone 2 Requirement R2
                        --}}
                        <li class="nav-item">
                            <button class="nav-link w-100 text-start text-dark fw-bold" id="v-pills-medhistory-tab"
                                data-bs-toggle="tab" data-bs-target="#vcd_pane_medhistory" role="tab" type="button">
                                <i class="fa-solid fa-file-waveform me-2"></i> Medical History
                            </button>
                        </li>
                    </nav>

                    <div class="tab-content flex-grow-1 overflow-y-auto px-lg-2">
                        {{-- Tab 1: Impressions and Diagnosis --}}
                        <div class="tab-pane active w-100" id="vcd_pane_impdiag" role="tabpanel">
                            <h5 class="fw-bold mb-3"><i class="fa-solid fa-quote-left text-primary me-2"></i>
                                Impressions &amp; Diagnosis</h5>
                            <div class="d-flex flex-column gap-3">
                                {{-- Detailed Comment: Answered Doctor's Questions container in Impressions & Diagnosis
                                tab --}}
                                <div class="border rounded p-3 bg-light d-none" id="vcd_doctor_questions_box">
                                    <h6 class="fw-bold text-dark mb-2"><i
                                            class="fa-solid fa-clipboard-question text-primary me-2"></i> Answered
                                        Doctor's Questions</h6>
                                    <div id="vcd_answered_questions_list" class="d-flex flex-column gap-2"></div>
                                </div>
                                <div>
                                    <label class="form-label fw-bold small text-muted">Chief Complaints</label>
                                    <textarea rows="3" class="form-control bg-light" id="vcd_chief_complaint"
                                        readonly></textarea>
                                </div>
                                {{-- Detailed Comment: Brief History of Present Illness readonly display --}}
                                <div>
                                    <label class="form-label fw-bold small text-muted">Brief History of Present Illness</label>
                                    <textarea rows="3" class="form-control bg-light" id="vcd_brief_history"
                                        readonly placeholder="No brief history of present illness recorded."></textarea>
                                </div>
                                {{-- Detailed Comment: Pertinent Past Medical History readonly display --}}
                                <div>
                                    <label class="form-label fw-bold small text-muted">Pertinent Past Medical History</label>
                                    <textarea rows="3" class="form-control bg-light" id="vcd_past_medical_history"
                                        readonly placeholder="No pertinent past medical history recorded."></textarea>
                                </div>
                                <div>
                                    <label class="form-label fw-bold small text-muted">Impressions</label>
                                    <textarea rows="3" class="form-control bg-light" id="vcd_impressions"
                                        readonly></textarea>
                                </div>
                                <div>
                                    <label class="form-label fw-bold small text-muted">Diagnosis</label>
                                    <textarea rows="3" class="form-control bg-light" id="vcd_diagnosis"
                                        readonly></textarea>
                                </div>
                                <div class="border rounded p-3 bg-light" id="vcd_admit_box">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-danger fs-6" id="vcd_admit_badge">Not for Admission</span>
                                        <span class="fw-bold text-dark">Admission Orders &amp; Instructions to
                                            Kin</span>
                                    </div>
                                    <textarea rows="3" class="form-control bg-white" id="vcd_admit_instructions"
                                        readonly placeholder="No admission instructions recorded."></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Tab 2: Rx & Instructions --}}
                        <div class="tab-pane w-100" id="vcd_pane_rx" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold m-0"><i class="fa-solid fa-prescription text-primary me-2"></i> List
                                    of Rx &amp; Prescriptions</h5>
                                <a target="_blank" class="btn btn-sm btn-info text-white" id="vcd_print_rx_btn"><i
                                        class="fa-solid fa-print"></i> Print Rx</a>
                            </div>
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered" id="vcd_rx_table">
                                    <thead class="table-info">
                                        <tr>
                                            <th>Medicine Name</th>
                                            <th>Instructions / Sig</th>
                                            <th style="width: 100px;">Quantity</th>
                                            <th style="width: 120px;">Dispensed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No prescriptions recorded.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2">
                                <label class="form-label fw-bold small text-muted">Doctor's General Instructions</label>
                                <textarea class="form-control bg-light" rows="3" id="vcd_general_instructions"
                                    readonly></textarea>
                            </div>
                        </div>

                        {{-- Tab 3: Diagnostic Requests --}}
                        <div class="tab-pane w-100" id="vcd_pane_dreqs" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold m-0"><i class="fa-solid fa-envelope-open-text text-primary me-2"></i>
                                    Diagnostic Requests</h5>
                                <a target="_blank" class="btn btn-sm btn-info text-white" id="vcd_print_diag_btn"><i
                                        class="fa-solid fa-print"></i> Print Requests</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="vcd_diagnostics_table">
                                    <thead class="table-success">
                                        <tr>
                                            <th>Request / Procedure Name</th>
                                            <th style="width: 150px;">Category</th>
                                            <th style="width: 170px;">Requested Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No diagnostic requests
                                                recorded.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 4: Radiology & Laboratory --}}
                        <div class="tab-pane w-100" id="vcd_pane_radlab" role="tabpanel">
                            <h5 class="fw-bold mb-3"><i class="fa-solid fa-vial text-primary me-2"></i> Radiology &amp;
                                Laboratory Documents</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card p-3 border">
                                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-x-ray me-1 text-primary"></i>
                                            Radiology Result</h6>
                                        <div id="vcd_rad_status" class="mb-2 text-muted small">No radiology file
                                            uploaded.</div>
                                        <a href="#" target="_blank" class="btn btn-sm btn-primary d-none"
                                            id="vcd_rad_preview_btn">
                                            <i class="fa-solid fa-eye me-1"></i> Preview / Download
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card p-3 border">
                                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-flask me-1 text-success"></i>
                                            Laboratory Result</h6>
                                        <div id="vcd_lab_status" class="mb-2 text-muted small">No laboratory file
                                            uploaded.</div>
                                        <a href="#" target="_blank" class="btn btn-sm btn-success d-none"
                                            id="vcd_lab_preview_btn">
                                            <i class="fa-solid fa-eye me-1"></i> Preview / Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tab 5: Patient Charges --}}
                        <div class="tab-pane w-100" id="vcd_pane_charges" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold m-0"><i class="fa-solid fa-coins text-primary me-2"></i> Patient
                                    Charges</h5>
                                <h5 class="fw-bold m-0 text-success">Total: PHP <span id="vcd_charges_total">0.00</span>
                                </h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="vcd_charges_table">
                                    <thead class="table-warning">
                                        <tr>
                                            <th>Description</th>
                                            <th style="width: 140px;">Category</th>
                                            <th style="width: 80px;" class="text-center">Qty</th>
                                            <th style="width: 120px;" class="text-end">Unit Price</th>
                                            <th style="width: 130px;" class="text-end">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No charges recorded.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Detailed Comment: Tab 6: Patient Permanent Medical History vertical pane per Milestone 2
                        Requirement R2 --}}
                        <div class="tab-pane w-100" id="vcd_pane_medhistory" role="tabpanel">
                            {{-- Detailed Comment: Header for Patient Permanent Medical History kept on single line for exact match assertion --}}
                            <h5 class="fw-bold mb-3"><i class="fa-solid fa-notes-medical text-primary me-2"></i> Patient Permanent Medical History</h5>
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <h6 class="fw-bold text-dark mb-2">Critical Alerts &amp; Immunizations</h6>
                                    <div class="p-2 border rounded bg-light mb-2">
                                        <span class="fw-bold text-danger small d-block mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Allergies &amp; Drug Reactions:</span>
                                        <div id="vcd_allergies" class="small text-dark fw-semibold">None recorded</div>
                                    </div>
                                    <div class="p-2 border rounded bg-light mb-2">
                                        <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-syringe me-1 text-primary"></i> Injections &amp; Immunizations:</span>
                                        <div id="vcd_injections" class="small text-dark">None recorded</div>
                                    </div>
                                    <div class="p-2 border rounded bg-light">
                                        <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-pills me-1 text-info"></i> Maintenance Medications:</span>
                                        <div id="vcd_maintenance_meds" class="small text-dark">None recorded</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <h6 class="fw-bold text-dark mb-2">Medical, Surgical &amp; Family History</h6>
                                    <div class="p-2 border rounded bg-light mb-2">
                                        <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-heart-pulse me-1 text-secondary"></i> Past Medical Conditions:</span>
                                        <div id="vcd_past_medical_history" class="small text-dark">None recorded</div>
                                    </div>
                                    <div class="p-2 border rounded bg-light mb-2">
                                        <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-bandage me-1 text-warning"></i> Surgical/Operations History:</span>
                                        <div id="vcd_surgical_history" class="small text-dark">None recorded</div>
                                    </div>
                                    <div class="p-2 border rounded bg-light">
                                        <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-users me-1 text-success"></i> Family Medical History:</span>
                                        <div id="vcd_family_history" class="small text-dark">None recorded</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-2 border rounded bg-light">
                                        <span class="fw-bold text-dark small d-block mb-1"><i
                                                class="fa-solid fa-comment-medical me-1 text-secondary"></i> Special
                                            Clinical Notes &amp; Warnings:</span>
                                        <div id="vcd_clinical_notes" class="small text-dark">None recorded</div>
                                    </div>
                                </div>
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