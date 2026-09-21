<!-- Detailed Comment: Add Doctor modal with 5-tab layout containing all columns from doctors table and credentials from doctorsrights -->
<div class="modal fade" data-bs-backdrop="static" id="add_doctor_modal" tabindex="-1"
    aria-labelledby="addDoctorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h4 class="modal-title fw-bold" id="addDoctorModalLabel">
                    <i class="fa-solid fa-user-doctor text-primary me-2"></i> Add Doctor Account
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body p-3" id="add_doctor_form">
                @csrf

                <!-- Tab Navigation -->
                <ul class="nav nav-pills mb-3 border-bottom pb-2" id="addDoctorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2 px-3" id="add-tab-personal" data-bs-toggle="pill"
                            data-bs-target="#add-pane-personal" type="button" role="tab">
                            <i class="fa-solid fa-id-card me-1"></i> 1. Personal &amp; Account
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="add-tab-licenses" data-bs-toggle="pill"
                            data-bs-target="#add-pane-licenses" type="button" role="tab">
                            <i class="fa-solid fa-certificate me-1"></i> 2. Licenses &amp; Accreditations
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="add-tab-practice" data-bs-toggle="pill"
                            data-bs-target="#add-pane-practice" type="button" role="tab">
                            <i class="fa-solid fa-stethoscope me-1"></i> 3. Practice &amp; Clinic
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="add-tab-rates" data-bs-toggle="pill"
                            data-bs-target="#add-pane-rates" type="button" role="tab">
                            <i class="fa-solid fa-receipt me-1"></i> 4. Rates, Tax &amp; Billing
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="add-tab-system" data-bs-toggle="pill"
                            data-bs-target="#add-pane-system" type="button" role="tab">
                            <i class="fa-solid fa-sliders me-1"></i> 5. System &amp; Notes
                        </button>
                    </li>
                </ul>

                <!-- Tab Content Panes -->
                <div class="tab-content" id="addDoctorTabsContent">
                    <!-- Tab 1: Personal & Account Credentials -->
                    <div class="tab-pane fade show active" id="add-pane-personal" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="docfname">First Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="docfname" id="docfname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="docmname">Middle Name</label>
                                <input class="form-control" type="text" name="docmname" id="docmname">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="doclname">Last Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="doclname" id="doclname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="suffix">Suffix</label>
                                <input class="form-control" type="text" name="suffix" id="suffix"
                                    placeholder="Jr., III">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="titlename">Title</label>
                                <input class="form-control" type="text" name="titlename" id="titlename"
                                    placeholder="MD, FPCP">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="docfirst">First Name Alias / Nickname</label>
                                <input class="form-control" type="text" name="docfirst" id="docfirst">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="docusername">Username <span
                                        class="text-secondary fw-normal">(Defaults to Last Name)</span></label>
                                <input class="form-control font-monospace" type="text" name="username" id="docusername"
                                    placeholder="Defaults to lowercase last name">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="pass">Default Password <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="pass" id="pass" value="12345" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="emailadd">Email Address</label>
                                <input class="form-control" type="email" name="emailadd" id="emailadd">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="cellno">Contact #</label>
                                <input class="form-control" type="tel" name="cellno" id="cellno" placeholder="(+63)">
                            </div>
                        </div>

                        <!-- Detailed Comment: PSGC Geographic Address Cascade Integration for Doctor Registration -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="doc_region">Region</label>
                                <select class="form-select" id="doc_region" name="doc_region">
                                    <option value="" selected disabled>-- Select Region --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="doc_prov">Province</label>
                                <select class="form-select" id="doc_prov" name="doc_prov" disabled>
                                    <option value="" selected disabled>-- Select Province --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="doc_mun">City / Municipality</label>
                                <select class="form-select" id="doc_mun" name="doc_mun" disabled>
                                    <option value="" selected disabled>-- Select Municipality --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="doc_brgy">Barangay</label>
                                <select class="form-select" id="doc_brgy" name="doc_brgy" disabled>
                                    <option value="" selected disabled>-- Select Barangay --</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="doc_street">Street / Building / Unit</label>
                                <input class="form-control" type="text" id="doc_street" name="doc_street" placeholder="e.g. Rm 101, Bldg A, Rizal Ave">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold" for="doc_zipcode">Zip Code</label>
                                <input class="form-control" type="text" id="doc_zipcode" name="doc_zipcode" placeholder="Zip Code" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="adrs">Compiled Address</label>
                                <input class="form-control" type="text" name="adrs" id="adrs" placeholder="Full address will be compiled here">
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Licenses & Accreditations -->
                    <div class="tab-pane fade" id="add-pane-licenses" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="licno">PRC License # <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="licno" id="licno" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="licnoexpiry">PRC Valid Until <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="licnoexpiry" id="licnoexpiry" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="tin">TIN</label>
                                <input class="form-control" type="text" name="tin" id="tin">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="phicno">PHIC Accreditation # <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="phicno" id="phicno" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="phicexpiry">PHIC Valid Until <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="phicexpiry" id="phicexpiry" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="phicname">PHIC HCI Name</label>
                                <input class="form-control" type="text" name="phicname" id="phicname">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="s2no">S2 License #</label>
                                <input class="form-control" type="text" name="s2no" id="s2no">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="ptr">PTR #</label>
                                <input class="form-control" type="text" name="ptr" id="ptr">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="phicrate">PHIC Rate (₱)</label>
                                <input class="form-control" type="number" step="0.01" name="phicrate" id="phicrate"
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="phicenable" id="phicenable" value="1">
                            <label class="form-check-label fw-bold" for="phicenable">PhilHealth Enabled</label>
                        </div>
                    </div>

                    <!-- Tab 3: Practice & Clinic -->
                    <div class="tab-pane fade" id="add-pane-practice" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="proftype">Professional Type</label>
                                <input class="form-control" type="text" name="proftype" id="proftype">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="expertise">Expertise / Specialization</label>
                                <input class="form-control" type="text" name="expertise" id="expertise">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="department">Department</label>
                                <input class="form-control" type="text" name="department" id="department">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="profgroup">Professional Group</label>
                                <input class="form-control" type="text" name="profgroup" id="profgroup">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="catg">Category Code</label>
                                <input class="form-control" type="text" name="catg" id="catg" maxlength="3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="station">Station</label>
                                <input class="form-control" type="text" name="station" id="station">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="clinicroom">Clinic Room</label>
                                <input class="form-control" type="text" name="clinicroom" id="clinicroom"
                                    placeholder="e.g. Room 204">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="clinichours">Clinic Hours</label>
                                <input class="form-control" type="text" name="clinichours" id="clinichours"
                                    placeholder="e.g. 9:00 AM - 12:00 PM">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="groupname">Group Name</label>
                                <input class="form-control" type="text" name="groupname" id="groupname" maxlength="3">
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Rates, Tax & Billing -->
                    <div class="tab-pane fade" id="add-pane-rates" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="pfrate">PF Rate (₱)</label>
                                <input class="form-control" type="number" step="0.01" name="pfrate" id="pfrate"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="rodrate">ROD Rate (₱)</label>
                                <input class="form-control" type="number" step="0.01" name="rodrate" id="rodrate"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="tax">Tax %</label>
                                <input class="form-control" type="number" step="0.01" name="tax" id="tax"
                                    placeholder="e.g. 10.00">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="vatrate">VAT Rate %</label>
                                <input class="form-control" type="number" step="0.01" name="vatrate" id="vatrate"
                                    placeholder="e.g. 12.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="coacode">Chart of Accounts Code</label>
                                <input class="form-control" type="text" name="coacode" id="coacode">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="accountno">Bank / Account No.</label>
                                <input class="form-control" type="text" name="accountno" id="accountno">
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="vatable" id="vatable"
                                        value="1">
                                    <label class="form-check-label fw-bold" for="vatable">VATable</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="autoAddVAT" id="autoAddVAT"
                                        value="1">
                                    <label class="form-check-label fw-bold" for="autoAddVAT">Auto Add VAT</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="issuehospOR" id="issuehospOR"
                                        value="1">
                                    <label class="form-check-label fw-bold" for="issuehospOR">Issue Hospital OR</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 5: System Settings & Notes -->
                    <div class="tab-pane fade" id="add-pane-system" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="status">Status</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="statusreason">Status Reason</label>
                                <input class="form-control" type="text" name="statusreason" id="statusreason">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="quevisible" id="quevisible"
                                        value="1" checked>
                                    <label class="form-check-label fw-bold" for="quevisible">Visible in Queue</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allowtextresult"
                                        id="allowtextresult" value="1">
                                    <label class="form-check-label fw-bold" for="allowtextresult">Allow Text
                                        Result</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allowdocsystem"
                                        id="allowdocsystem" value="1">
                                    <label class="form-check-label fw-bold" for="allowdocsystem">Allow Doc
                                        System</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="disabletext" id="disabletext"
                                        value="1">
                                    <label class="form-check-label fw-bold" for="disabletext">Disable Text</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="otherinfo">Other Information</label>
                            <textarea class="form-control" name="otherinfo" id="otherinfo" rows="2"></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold" for="biodata">Doctor Biodata / CV</label>
                            <textarea class="form-control" name="biodata" id="biodata" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </form>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-bold" id="add_doctor_form_btn">
                    <i class="fa-solid fa-plus me-1"></i> Add Doctor
                </button>
            </div>
        </div>
    </div>
</div>