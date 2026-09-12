<!-- Global reference number reference -->
<input type="hidden" name="consultationrefno" id="consultationrefno">

<div class="modal fade" data-bs-backdrop="static" id="consultation_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="min-width: 90rem;">
        <div class="modal-content" style="height: 50rem;">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fa-solid fa-stethoscope"></i> Consultation</h2>
                <button type="button" class="btn-close" id="close-consultation-modal"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-3" id="consul_sidebar">
                <div class="accordion" id="consultation_accordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#rx_patient_info" aria-expanded="false" aria-controls="rx_patient_info">
                                <i class="fa-solid fa-user me-2"></i> Patient Information
                            </button>
                        </h2>

                        <div class="accordion-collapse collapse" id="rx_patient_info">
                            <div class="accordion-body">
                                <div class="d-flex flex-column">
                                    <p class="m-0">Name: <strong id="genname"></strong></p>
                                    <p class="m-0">Sex: <strong id="gensex"></strong></p>
                                    <div class="d-flex">
                                        <p class="m-0 w-50">Birthdate: <strong id="genbday"></strong></p>
                                        <p class="m-0 w-50">Age: <strong id="genage"></strong></p>
                                    </div>

                                    <div class="d-flex">
                                        <p class="m-0 w-50">Contact #: <strong id="gencellno"></strong></p>
                                        <p class="m-0 w-50">Email: <strong id="genemail"></strong></p>
                                    </div>

                                    <p class="m-0">Address: <strong id="genaddress"></strong></p>

                                    <hr>

                                    <div class="d-flex justify-content-between">
                                        <p class="m-0 w-50">Weight: <strong id="genweight"></strong></p>
                                        <p class="m-0 w-50">Height: <strong id="genheight"></strong></p>
                                        <p class="m-0 w-50">Temperature: <strong id="gentemp"></strong></p>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <p class="m-0 w-50">Respiratory Rate: <strong id="genresprate"></strong></p>
                                        <p class="m-0 w-50">Pulse Rate: <strong id="genpulserate"></strong></p>
                                        <p class="m-0 w-50">Blood Pressure: <strong id="genbp"></strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card d-flex flex-row p-2 h-100 gap-3">
                    <nav class="nav nav-pills d-flex flex-column w-25 gap-1">
                        <!-- <li class="nav-item">
                            <button class="nav-link active w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#medQuestTab" role="tab" aria-controls="medQuestTab"
                                aria-selected="false" id="medical_questions_tab_btn">
                                <i class="fa-solid fa-clipboard-question"></i> Medical Questions
                            </button>
                        </li> -->

                        <li class="nav-item">
                            <button class="nav-link active w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#medHistoryTab" role="tab" aria-controls="medHistoryTab"
                                aria-selected="false" id="medhistory_btn">
                                <i class="fa-solid fa-timeline"></i> Medical History
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#impDiagTab" role="tab" aria-controls="impDiagTab"
                                aria-selected="false" id="impDiagBtn">
                                <i class="fa-solid fa-quote-left"></i> Impressions & Diagnosis
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#rxTab" role="tab" aria-controls="rxTab"
                                aria-selected="false" id="rx_sidebar_btn">
                                <i class="fa-solid fa-prescription"></i> Rx & Instructions
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#dReqsTab" role="tab" aria-controls="dReqsTab"
                                aria-selected="false" id="dReqsTabBtn">
                                <i class="fa-solid fa-envelope-open-text"></i> Diagnostic Requests
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#radLabTab" role="tab" aria-controls="radLabTab"
                                aria-selected="false" id="radLabTabBtn">
                                <i class="fa-solid fa-vial"></i> Radiology & Laboratory
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link w-100 text-start text-dark fw-bold" data-bs-toggle="tab"
                                data-bs-target="#patientChargeTab" role="tab" aria-controls="patientChargeTab"
                                aria-selected="false" id="patient_charge_tab_btn">
                                <i class="fa-solid fa-coins"></i> Patient Charges
                            </button>
                        </li>
                    </nav>

                    <div class="tab-content d-flex overflow-y-auto w-75 px-2">
                        <!-- <div class="tab-pane active w-100" id="medQuestTab" role="tabpanel" aria-labelledby="medQuestTab-tab"
                            tabindex="0">
                            <h4>Medical Questions</h4>
                            <hr class="m-0 mb-4">

                            <div class="table-responsive">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="m-0 fw-bold">Saved Questions</h6>
                                    <button class="btn btn-sm btn-primary text-white" id="add_question_btn"><i class="fa-solid fa-file-pen"></i> Add New Question</button>
                                </div>

                                <table class="table table-bordered caption-top" id="patient_questions_answer">
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col">Actions</th>
                                            <th scope="col">Question</th>
                                            <th scope="col">Answer</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div> -->

                        <!-- Medical History -->
                        <div class="tab-pane active w-100" id="medHistoryTab" role="tabpanel" aria-labelledby="medHistoryTab-tab"
                            tabindex="0">
                            <h4>Medical History</h4>
                            <hr class="m-0 mb-4">

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="medhistory_table">
                                    <thead class="table-warning">
                                        <tr>
                                            <th scope="col">Photo</th>
                                            <th scope="col">Consultation Date</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Impressions and Diagnosis -->
                        <div class="tab-pane w-100" id="impDiagTab" role="tabpanel" aria-labelledby="impDiagTab-tab"
                            tabindex="0">
                            <h4>Impressions & Diagnosis</h4>
                            <hr class="m-0 mb-4">

                            <form class="d-flex flex-column gap-2">
                                <div>
                                    <label class="form-label fw-bold" for="reasonforconsultation">Chief Complaints</label>
                                    <textarea rows="4" class="form-control" name="reasonforconsultation" id="reasonforconsultation"></textarea>
                                </div>

                                <div>
                                    <label class="form-label fw-bold" for="impressions">Impressions</label>
                                    <textarea rows="4" class="form-control" name="impressions" id="impressions"></textarea>
                                </div>

                                <div>
                                    <label class="form-label fw-bold" for="diagnosis">Diagnosis</label>
                                    <textarea rows="4" class="form-control" name="diagnosis" id="diagnosis"></textarea>
                                </div>

                                <button type="button" class="btn btn-primary fw-bold w-25" id="save_impressions_diagnosis">Save</button>
                            </form>
                        </div>

                        <!-- RX and Instructions -->
                        <div class="tab-pane w-100" id="rxTab" role="tabpanel" aria-labelledby="rxTab-tab"
                            tabindex="0">
                            <h4>Rx & Instructions</h4>
                            <hr class="m-0 mb-4">

                            <div>
                                <div class="table-responsive mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="m-0 fw-bold">List of Rx</h6>
                                        <a target="_blank" class="btn btn-sm btn-info text-white" id="print_rx_btn"><i class="fa-solid fa-print"></i> Print</a>
                                    </div>

                                    <table class="table table-sm table-bordered" id="dashboard_rx_table">
                                        <thead class="table-info">
                                            <tr>
                                                <th scope="col">Medicine Name</th>
                                                <th scope="col">Quantity</th>
                                                <th scope="col">Dispense Status</th>
                                            </tr>
                                        </thead>

                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="m-0 fw-bold">Instructions</h6>
                                    <a target="_blank" class="btn btn-sm btn-warning text-white" id="print_inst_btn"><i class="fa-solid fa-print"></i> Print</a>
                                </div>
                                <textarea class="form-control" name="instructions" rows="3" id="patient_instructions" disabled></textarea>
                            </div>
                        </div>

                        <!-- Diagnostics Request -->
                        <div class="tab-pane w-100" id="dReqsTab" role="tabpanel" aria-labelledby="dReqsTab-tab" tabindex="0">
                            <h4>Diagnostics Requests</h4>
                            <hr class="m-0 mb-4">

                            <div class="table-responsive">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="m-0 fw-bold">List of Requests</h6>
                                    <div>
                                        <button class="btn btn-primary btn-sm text-white" data-bs-target="#diagnostic_modal" id="diagnostic_btn_2" data-bs-toggle="modal"><i class="fa-solid fa-plus"></i> Add Requests</button>
                                        <a target="_blank" class="btn btn-sm btn-info text-white" id="print_diagnostics"><i class="fa-solid fa-print"></i> Print</a>
                                    </div>
                                </div>

                                <table class="table table-sm table-bordered" id="diagnostics_table">
                                    <thead class="table-success">
                                        <tr>
                                            <th scope="col">Actions</th>
                                            <th scope="col">Name</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Radiology and Laboratory -->
                        <div class="tab-pane w-100" id="radLabTab" role="tabpanel" aria-labelledby="radLabTab-tab" tabindex="0">
                            <h4>Radiology & Laboratory</h4>
                            <hr class="m-0 mb-4">

                            <form class="d-flex gap-4" id="rad_lab_form">
                                @csrf

                                <div class=" w-50">
                                    <label class="form-label fw-bold" for="preview_radiology">Radiology <span class="bg-success text-white rounded px-2 py-0 d-none" style="font-size: 14px;" id="radiology_hasfile"><i class="fa-solid fa-check"></i> Document Available</span></label>
                                    <div class="input-group input-group-sm">
                                        <input class="form-control form-control-sm" type="file" name="radiology" id="radiology_result">
                                        <button type="button" class="btn btn-sm btn-primary" name="preview_radiology" id="preview_radiology">Preview</button>
                                    </div>
                                </div>

                                <div class=" w-50">
                                    <label class="form-label fw-bold" for="preview_laboratory">Laboratory <span class="bg-success text-white rounded px-2 py-0 d-none" style="font-size: 14px;" id="laboratory_hasfile"><i class="fa-solid fa-check"></i> Document Available</span></label>
                                    <div class="input-group">
                                        <input class="form-control form-control-sm" type="file" name="laboratory" id="laboratory_result">
                                        <button type="button" class="btn btn-sm btn-primary" name="preview_laboratory" id="preview_laboratory">Preview</button>
                                    </div>
                                </div>
                            </form>
                            <div class="form-text mb-2">Uploading files will overwrite the old one. Images can be previewed while document files are downloaded.</div>

                            <div>
                                <button type="button" class="btn btn-sm btn-primary" id="update_files_btn">Save updates</button>
                                <div class="form-text">Save updated files before switching tabs.</div>
                            </div>
                        </div>

                        <!-- Patient Charges -->
                        <div class="tab-pane w-100" id="patientChargeTab" role="tabpanel" aria-labelledby="patientChargeTab-tab"
                            tabindex="0">
                            <h4>Patient Charges</h4>
                            <hr class="m-0 mb-4">

                            <div class="table-responsive">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="m-0 fw-bold">List of Charges</h6>
                                    <button class="btn btn-sm btn-warning text-white" id="append_charge_btn" data-bs-target="#append_charge_modal" data-bs-toggle="modal">
                                        <i class="fa-solid fa-plus"></i> Append Charges
                                    </button>
                                </div>

                                <table class="table table-sm table-bordered align-middle" id="charges_table">
                                    <thead class="table-warning">
                                        <tr>
                                            <th scope="col">Actions</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Discount</th>
                                            <th scope="col">Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-info fw-bold" data-bs-target="#append_charge_modal" id="append_charge_btn_2" data-bs-toggle="modal">Append Patient Charges</button>
                <button type="button" class="btn btn-sm btn-info fw-bold" data-bs-target="#diagnostic_modal" id="diagnostic_btn" data-bs-toggle="modal">Diagnostic Requests</button>
                <button type="button" class="btn btn-sm btn-info fw-bold" data-bs-target="#rx_modal" id="gen_rx_btn" data-bs-toggle="modal">Generate Rx</button>
                <button type="button" class="btn btn-sm btn-primary fw-bold" id="save_consul">Save Consultation</button>
            </div>
        </div>
    </div>
</div>

<!-- Diagnostic Requests -->
<div class="modal fade" data-bs-backdrop="static" id="diagnostic_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Diagnostic Requests</h2>
                <button type="button" class="btn-close return_btn" data-bs-target="#consultation_modal" data-bs-toggle="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- <ul class="nav nav-tabs nav-fill" role="tablist" id="diagnostic_tablist"></ul> -->

                <div class="border rounded d-flex p-4 flex-column flex-grow-1 mb-2" id="diagnostic_options"></div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered caption-top" id="selected_table">
                        <caption>Selected Requests</caption>
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">Actions</th>
                                <th scope="col">Name</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary w-25 return_btn" data-bs-target="#consultation_modal" data-bs-toggle="modal" id="diag_to_consul">Cancel</button>
                <button type="button" class="btn btn-info text-white w-25" id="save_requests">Save Requests</button>
            </div>
        </div>
    </div>
</div>

<!-- Generate Rx Modal -->
<div class="modal fade overflow-hidden" data-bs-backdrop="static" id="rx_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Generate Rx</h2>
                <button type="button" class="btn-close return_btn" data-bs-target="#consultation_modal" data-bs-toggle="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="accordion mb-3">
                    <!-- Patient Information -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#rx_patient_info" aria-expanded="false" aria-controls="rx_patient_info">
                                <i class="fa-solid fa-user me-2"></i> Patient Information
                            </button>
                        </h2>

                        <div class="accordion-collapse collapse" id="rx_patient_info">
                            <div class="accordion-body">
                                <div class="d-flex flex-column">
                                    <p class="m-0">Name: <strong id="genname"></strong></p>
                                    <p class="m-0">Sex: <strong id="gensex"></strong></p>
                                    <div class="d-flex">
                                        <p class="m-0 w-50">Birthdate: <strong id="genbday"></strong></p>
                                        <p class="m-0 w-50">Age: <strong id="genage"></strong></p>
                                    </div>

                                    <div class="d-flex">
                                        <p class="m-0 w-50">Contact #: <strong id="gencellno"></strong></p>
                                        <p class="m-0 w-50">Email: <strong id="genemail"></strong></p>
                                    </div>

                                    <p class="m-0">Address: <strong id="genaddress"></strong></p>

                                    <hr>

                                    <div class="d-flex justify-content-between">
                                        <p class="m-0 w-50">Weight: <strong id="genweight"></strong></p>
                                        <p class="m-0 w-50">Height: <strong id="genheight"></strong></p>
                                        <p class="m-0 w-50">Temperature: <strong id="gentemp"></strong></p>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <p class="m-0 w-50">Respiratory Rate: <strong id="genresprate"></strong></p>
                                        <p class="m-0 w-50">Pulse Rate: <strong id="genpulserate"></strong></p>
                                        <p class="m-0 w-50">Blood Pressure: <strong id="genbp"></strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#rx" role="tab"
                            aria-controls="rx" aria-selected="true" id="usemyrx">Use My Rx</button>
                    </li>

                    <!-- <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#new_rx" role="tab"
                            aria-controls="new_rx" aria-selected="true" id="createrx" disabled>Create New Rx</button>
                    </li> -->
                </ul>

                <div class="tab-content border border-top-0 rounded-bottom d-flex p-4 flex-column flex-grow-1">
                    <!-- My Rx -->
                    <div class="tab-pane active h-100" id="rx" role="tabpanel" aria-labelledby="rx-tab"
                        tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle caption-top" id="rx_table">
                                <caption>Prescriptions</caption>
                                <thead class="table-info">
                                    <tr>
                                        <th scope="col">Actions</th>
                                        <th scope="col">Medicine Name</th>
                                        <th scope="col">Quantity</th>
                                    </tr>
                                </thead>

                                <tbody></tbody>
                            </table>
                        </div>

                        <form class="d-flex gap-2 m-0" id="myrx_form">
                            @csrf

                            <div class="d-flex flex-column flex-fill">
                                <label class="form-label" for="mymed">Medicine</label>
                                <select class="form-select" name="mymed" id="mymed"></select>
                            </div>

                            <div>
                                <!-- <span class="input-group-text">Dosage</span>
                                <input class="form-control" type="number" name="mydosage" id="mydosage" value="0" required>
                                <span class="input-group-text">Duration</span>
                                <input class="form-control" type="text" name="myduration" id="myduration" required> -->
                                <label class="form-label" for="myquantity">Quantity</label>
                                <input class="form-control" type="number" name="myquantity" id="myquantity" value="0" required>
                            </div>

                            <button type="button" class="btn btn-primary w-25 align-self-end mt-2" id="add_rx">Add Medicine</button>
                        </form>
                    </div>

                    <!-- Create New Rx -->
                    <div class="tab-pane h-100" id="new_rx" role="tabpanel" aria-labelledby="new_rx-tab"
                        tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-bordered caption-top" id="create_new_rx_table">
                                <caption>Prescription</caption>
                                <thead class="table-info">
                                    <tr>
                                        <th scope="col">Actions</th>
                                        <th scope="col">Medicine Name</th>
                                        <th scope="col">Quantity</th>
                                    </tr>
                                </thead>

                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mt-4 mx-4">
                    <div class="">
                        <label class="form-label" for="pxinstructions">Instructions</label>
                        <textarea class="form-control" name="pxinstructions" rows="3" id="pxinstructions" required></textarea>
                        <div class="form-text">Enter patient instructions here.</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary w-25 return_btn" data-bs-target="#consultation_modal" data-bs-toggle="modal">Cancel</button>
                <button type="button" class="btn btn-info text-white w-25" id="save_rx_btn">Save Rx</button>
            </div>
        </div>
    </div>
</div>

<!-- Append Charges Modal -->
<div class="modal fade" data-bs-backdrop="static" id="append_charge_modal" tabindex="-1" aria-labelledby="append_charge_modalModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content" id="appended_charges_form">
			<div class="modal-header">
				<h3 class="modal-title"><i class="fa-solid fa-coins"></i> Append Charges</h3>
				<button type="button" class="btn-close" data-bs-toggle="modal" data-bs-target="#consultation_modal" aria-label="Close"></button>
			</div>
			<div class="modal-body d-flex flex-column gap-3">
                <div class="">
                    <label class="form-label fw-bold" for="search_charge">Search Charges</label>
                    <div class="input-group">
                        <!-- <input class="form-control" type="search" name="search_charge" id="search_charge" required>
                        <input type="hidden" name="charge_code" id="charge_code"> -->
                        <select class="flex-fill form-control" name="search_charge" id="search_charge"></select>
                        <span class="input-group-text"><i class="fa-solid fa-filter"></i></span>
                        <select class="form-select" name="search_filter" id="search_filter">
                            <option value="ALL">ALL</option>
                            <option value="SUPPLIES">SUPPLIES</option>
                            <option value="DRUGS AND MEDS">DRUGS AND MEDS</option>
                            <option value="PROCEDURES">PROCEDURES</option>
                            <option value="DIAGNOSTIC">DIAGNOSTIC</option>
                            <option value="IMAGING">IMAGING</option>
                            <option value="PROFESSIONAL FEE">PROFESSIONAL FEE</option>
                        </select>
                    </div>
                    <div class="form-text">Appended charges won't be available from the search.</div>
                </div>
                <div class="d-flex align-items-end gap-2">
                    <div class="flex-fill">
                        <label class="form-label fw-bold" for="charge_qty">Quantity</label>
                        <input class="form-control" type="number" name="charge_qty" id="charge_qty" min="0" value="0" required>
                    </div>
                    <div class="flex-fill d-flex flex-column">
                        <label class="form-label fw-bold" for="charge_amount">Charge Amount</label>
                        <input class="form-control" type="number" name="charge_amount" id="charge_amount" min="0" value="0" required>
                        <!-- <select class="form-select" name="charge_amount" id="charge_amount">
                            <option selected disabled>-- Select Price --</option>
                        </select> -->
                    </div>
                    <button type="button" class="btn btn-primary text-white" id="append_to_charges_btn"><i class="fa-solid fa-plus"></i> Append Charge</button>
                </div>
                <div class="">
                    <label class="form-label fw-bold">Appended Charges</label>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="appended_charges_table">
                            <thead class="table-warning">
                                <tr>
                                    <th scope="col" style="width: 1%;">Actions</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Total</th>
                                </tr>
                            </thead>

                            <tbody></tbody>
                        </table>
                    </div>
                </div>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-primary" id="save_charges_btn">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-target="#consultation_modal" data-bs-toggle="modal">Cancel</button>
			</div>
        </form>
	</div>
</div>

<!-- Preview File -->
<div class="modal fade overflow-hidden" data-bs-backdrop="static" id="preview_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header" id="preview_title">
                <h3 class="modal-title">Preview</h3>
                <button type="button" class="btn-close" data-bs-target="#consultation_modal" data-bs-toggle="modal"></button>
            </div>

            <div class="modal-body">
                <iframe id="docPreview" width="100%" height="500px" style="display: none;"></iframe>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-target="#consultation_modal" data-bs-toggle="modal" id="preview_close_btn">Close</button>
            </div>
        </div>
    </div>
</div>

@include('template.toast')
@include('modals.add_question')
