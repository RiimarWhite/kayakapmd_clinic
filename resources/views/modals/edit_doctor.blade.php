<!-- Detailed Comment: Edit Doctor modal with 5-tab layout containing all columns from doctors table and credentials from doctorsrights -->
<div class="modal fade" data-bs-backdrop="static" id="edit_doctor_modal" tabindex="-1"
    aria-labelledby="editDoctorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h4 class="modal-title fw-bold" id="editDoctorModalLabel">
                    <i class="fa-solid fa-user-doctor text-primary me-2"></i> Edit Doctor Account
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body p-3" id="edit_doctor_form">
                @csrf
                <input type="hidden" name="docrefno" id="edocrefno">

                <!-- Tab Navigation -->
                <ul class="nav nav-pills mb-3 border-bottom pb-2" id="editDoctorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2 px-3" id="edit-tab-personal" data-bs-toggle="pill"
                            data-bs-target="#edit-pane-personal" type="button" role="tab">
                            <i class="fa-solid fa-id-card me-1"></i> 1. Personal &amp; Account
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="edit-tab-licenses" data-bs-toggle="pill"
                            data-bs-target="#edit-pane-licenses" type="button" role="tab">
                            <i class="fa-solid fa-certificate me-1"></i> 2. Licenses &amp; Accreditations
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="edit-tab-practice" data-bs-toggle="pill"
                            data-bs-target="#edit-pane-practice" type="button" role="tab">
                            <i class="fa-solid fa-stethoscope me-1"></i> 3. Practice &amp; Clinic
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="edit-tab-rates" data-bs-toggle="pill"
                            data-bs-target="#edit-pane-rates" type="button" role="tab">
                            <i class="fa-solid fa-receipt me-1"></i> 4. Rates, Tax &amp; Billing
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="edit-tab-system" data-bs-toggle="pill"
                            data-bs-target="#edit-pane-system" type="button" role="tab">
                            <i class="fa-solid fa-sliders me-1"></i> 5. System &amp; Notes
                        </button>
                    </li>
                </ul>

                <!-- Tab Content Panes -->
                <div class="tab-content" id="editDoctorTabsContent">
                    <!-- Tab 1: Personal & Account Credentials -->
                    <div class="tab-pane fade show active" id="edit-pane-personal" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="edocfname">First Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="edocfname" id="edocfname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="edocmname">Middle Name</label>
                                <input class="form-control" type="text" name="edocmname" id="edocmname">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="edoclname">Last Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="edoclname" id="edoclname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="esuffix">Suffix</label>
                                <input class="form-control" type="text" name="esuffix" id="esuffix"
                                    placeholder="Jr., III">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="etitlename">Title</label>
                                <input class="form-control" type="text" name="etitlename" id="etitlename"
                                    placeholder="MD, FPCP">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="edocfirst">First Name Alias / Nickname</label>
                                <input class="form-control" type="text" name="edocfirst" id="edocfirst">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="edocusername">Username <span
                                        class="text-danger">*</span></label>
                                <input class="form-control font-monospace" type="text" name="eusername"
                                    id="edocusername" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="epass">New Password</label>
                                <input class="form-control" type="password" name="epass" id="epass"
                                    placeholder="Leave blank to keep current">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="eemailadd">Email Address</label>
                                <input class="form-control" type="email" name="eemailadd" id="eemailadd">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="ecellno">Contact #</label>
                                <input class="form-control" type="tel" name="ecellno" id="ecellno" placeholder="(+63)">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold" for="eadrs">Address</label>
                            <input class="form-control" type="text" name="eadrs" id="eadrs">
                        </div>
                    </div>

                    <!-- Tab 2: Licenses & Accreditations -->
                    <div class="tab-pane fade" id="edit-pane-licenses" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="elicno">PRC License # <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="elicno" id="elicno" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="elicnoexpiry">PRC Valid Until <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="elicnoexpiry" id="elicnoexpiry" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="etin">TIN</label>
                                <input class="form-control" type="text" name="etin" id="etin">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="ephicno">PHIC Accreditation # <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="ephicno" id="ephicno" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="ephicexpiry">PHIC Valid Until <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="ephicexpiry" id="ephicexpiry" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="ephicname">PHIC HCI Name</label>
                                <input class="form-control" type="text" name="ephicname" id="ephicname">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="es2no">S2 License #</label>
                                <input class="form-control" type="text" name="es2no" id="es2no">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="eptr">PTR #</label>
                                <input class="form-control" type="text" name="eptr" id="eptr">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="ephicrate">PHIC Rate (₱)</label>
                                <input class="form-control" type="number" step="0.01" name="ephicrate" id="ephicrate"
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="ephicenable" id="ephicenable"
                                value="1">
                            <label class="form-check-label fw-bold" for="ephicenable">PhilHealth Enabled</label>
                        </div>
                    </div>

                    <!-- Tab 3: Practice & Clinic -->
                    <div class="tab-pane fade" id="edit-pane-practice" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="eproftype">Professional Type</label>
                                <input class="form-control" type="text" name="eproftype" id="eproftype">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="eexpertise">Expertise / Specialization</label>
                                <input class="form-control" type="text" name="eexpertise" id="eexpertise">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="edepartment">Department</label>
                                <input class="form-control" type="text" name="edepartment" id="edepartment">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="eprofgroup">Professional Group</label>
                                <input class="form-control" type="text" name="eprofgroup" id="eprofgroup">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="ecatg">Category Code</label>
                                <input class="form-control" type="text" name="ecatg" id="ecatg" maxlength="3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="estation">Station</label>
                                <input class="form-control" type="text" name="estation" id="estation">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="eclinicroom">Clinic Room</label>
                                <input class="form-control" type="text" name="eclinicroom" id="eclinicroom">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="eclinichours">Clinic Hours</label>
                                <input class="form-control" type="text" name="eclinichours" id="eclinichours">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="egroupname">Group Name</label>
                                <input class="form-control" type="text" name="egroupname" id="egroupname" maxlength="3">
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Rates, Tax & Billing -->
                    <div class="tab-pane fade" id="edit-pane-rates" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="epfrate">PF Rate (₱)</label>
                                <input class="form-control" type="number" step="0.01" name="epfrate" id="epfrate"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="erodrate">ROD Rate (₱)</label>
                                <input class="form-control" type="number" step="0.01" name="erodrate" id="erodrate"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="etax">Tax %</label>
                                <input class="form-control" type="number" step="0.01" name="etax" id="etax"
                                    placeholder="e.g. 10.00">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="evatrate">VAT Rate %</label>
                                <input class="form-control" type="number" step="0.01" name="evatrate" id="evatrate"
                                    placeholder="e.g. 12.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="ecoacode">Chart of Accounts Code</label>
                                <input class="form-control" type="text" name="ecoacode" id="ecoacode">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="eaccountno">Bank / Account No.</label>
                                <input class="form-control" type="text" name="eaccountno" id="eaccountno">
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="evatable" id="evatable"
                                        value="1">
                                    <label class="form-check-label fw-bold" for="evatable">VATable</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="eautoAddVAT" id="eautoAddVAT"
                                        value="1">
                                    <label class="form-check-label fw-bold" for="eautoAddVAT">Auto Add VAT</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="eissuehospOR"
                                        id="eissuehospOR" value="1">
                                    <label class="form-check-label fw-bold" for="eissuehospOR">Issue Hospital OR</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 5: System Settings & Notes -->
                    <div class="tab-pane fade" id="edit-pane-system" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="estatus">Status</label>
                                <select class="form-select" name="estatus" id="estatus">
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="estatusreason">Status Reason</label>
                                <input class="form-control" type="text" name="estatusreason" id="estatusreason">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="equevisible" id="equevisible"
                                        value="1">
                                    <label class="form-check-label fw-bold" for="equevisible">Visible in Queue</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="eallowtextresult"
                                        id="eallowtextresult" value="1">
                                    <label class="form-check-label fw-bold" for="eallowtextresult">Allow Text
                                        Result</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="eallowdocsystem"
                                        id="eallowdocsystem" value="1">
                                    <label class="form-check-label fw-bold" for="eallowdocsystem">Allow Doc
                                        System</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="edisabletext"
                                        id="edisabletext" value="1">
                                    <label class="form-check-label fw-bold" for="edisabletext">Disable Text</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="eotherinfo">Other Information</label>
                            <textarea class="form-control" name="eotherinfo" id="eotherinfo" rows="2"></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold" for="ebiodata">Doctor Biodata / CV</label>
                            <textarea class="form-control" name="ebiodata" id="ebiodata" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </form>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-bold" id="edit_doctor_form_btn">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Doctor
                </button>
            </div>
        </div>
    </div>
</div>