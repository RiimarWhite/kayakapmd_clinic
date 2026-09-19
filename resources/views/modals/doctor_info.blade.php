<!-- Detailed Comment: Self-service Doctor Profile Modal for Doctor Dashboard.
     Allows authenticated doctor to view and edit their own profile across all columns
     from the 'doctors' table and credentials from 'doctorsrights' (username & password). -->
<div class="modal fade" data-bs-backdrop="static" id="doctor_profile_modal" tabindex="-1"
    aria-labelledby="doctorProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h4 class="modal-title fw-bold" id="doctorProfileModalLabel">
                    <i class="fa-solid fa-user-doctor text-primary me-2"></i> My Doctor Profile
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body p-3" id="doctor_profile_form">
                @csrf

                <!-- Tab Navigation -->
                <ul class="nav nav-pills mb-3 border-bottom pb-2" id="doctorProfileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2 px-3" id="doc-tab-personal" data-bs-toggle="pill"
                            data-bs-target="#doc-pane-personal" type="button" role="tab">
                            <i class="fa-solid fa-id-card me-1"></i> 1. Personal &amp; Account
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="doc-tab-licenses" data-bs-toggle="pill"
                            data-bs-target="#doc-pane-licenses" type="button" role="tab">
                            <i class="fa-solid fa-certificate me-1"></i> 2. Licenses &amp; Accreditations
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="doc-tab-practice" data-bs-toggle="pill"
                            data-bs-target="#doc-pane-practice" type="button" role="tab">
                            <i class="fa-solid fa-stethoscope me-1"></i> 3. Practice &amp; Clinic
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="doc-tab-rates" data-bs-toggle="pill"
                            data-bs-target="#doc-pane-rates" type="button" role="tab">
                            <i class="fa-solid fa-receipt me-1"></i> 4. Rates, Tax &amp; Billing
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2 px-3" id="doc-tab-system" data-bs-toggle="pill"
                            data-bs-target="#doc-pane-system" type="button" role="tab">
                            <i class="fa-solid fa-sliders me-1"></i> 5. System &amp; Notes
                        </button>
                    </li>
                </ul>

                <!-- Tab Content Panes -->
                <div class="tab-content" id="doctorProfileTabsContent">
                    <!-- Tab 1: Personal & Account Credentials -->
                    <div class="tab-pane fade show active" id="doc-pane-personal" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="prof_docfname">First Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="docfname" id="prof_docfname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="prof_docmname">Middle Name</label>
                                <input class="form-control" type="text" name="docmname" id="prof_docmname">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="prof_doclname">Last Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="doclname" id="prof_doclname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="prof_suffix">Suffix</label>
                                <input class="form-control" type="text" name="suffix" id="prof_suffix"
                                    placeholder="Jr., III">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="prof_titlename">Title</label>
                                <input class="form-control" type="text" name="titlename" id="prof_titlename"
                                    placeholder="MD, FPCP">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="prof_docfirst">First Name Alias /
                                    Nickname</label>
                                <input class="form-control" type="text" name="docfirst" id="prof_docfirst">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="prof_username">Login Username <span
                                        class="text-danger">*</span></label>
                                <input class="form-control font-monospace" type="text" name="username"
                                    id="prof_username" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="prof_new_password">New Password</label>
                                <input class="form-control" type="password" name="new_password" id="prof_new_password"
                                    placeholder="Leave blank to keep unchanged">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="prof_emailadd">Email Address</label>
                                <input class="form-control" type="email" name="emailadd" id="prof_emailadd">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="prof_cellno">Mobile / Contact #</label>
                                <input class="form-control" type="tel" name="cellno" id="prof_cellno"
                                    placeholder="(+63)">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold" for="prof_adrs">Home / Clinic Address</label>
                            <input class="form-control" type="text" name="adrs" id="prof_adrs">
                        </div>
                    </div>

                    <!-- Tab 2: Licenses & Accreditations -->
                    <div class="tab-pane fade" id="doc-pane-licenses" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_licno">PRC License No. <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="licno" id="prof_licno" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_licnoexpiry">PRC Valid Until</label>
                                <input class="form-control" type="date" name="licnoexpiry" id="prof_licnoexpiry">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_tin">TIN</label>
                                <input class="form-control" type="text" name="tin" id="prof_tin">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_phicno">PHIC Accreditation No.</label>
                                <input class="form-control" type="text" name="phicno" id="prof_phicno">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_phicexpiry">PHIC Valid Until</label>
                                <input class="form-control" type="date" name="phicexpiry" id="prof_phicexpiry">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_phicname">PHIC HCI Name</label>
                                <input class="form-control" type="text" name="phicname" id="prof_phicname">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_s2no">S2 License No.</label>
                                <input class="form-control" type="text" name="s2no" id="prof_s2no">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_ptr">PTR No.</label>
                                <input class="form-control" type="text" name="ptr" id="prof_ptr">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_phicrate">PHIC Rate (₱)</label>
                                <input class="form-control" type="number" step="0.01" name="phicrate" id="prof_phicrate"
                                    placeholder="0.00">
                            </div>
                        </div>

                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="phicenable" id="prof_phicenable"
                                value="1">
                            <label class="form-check-label fw-bold" for="prof_phicenable">PhilHealth Enabled</label>
                        </div>
                    </div>

                    <!-- Tab 3: Practice & Clinic Setup -->
                    <div class="tab-pane fade" id="doc-pane-practice" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_proftype">Professional Type</label>
                                <input class="form-control" type="text" name="proftype" id="prof_proftype"
                                    placeholder="e.g. Attending, Consultant">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_expertise">Specialization /
                                    Expertise</label>
                                <input class="form-control" type="text" name="expertise" id="prof_expertise"
                                    placeholder="e.g. Internal Medicine">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_department">Department</label>
                                <input class="form-control" type="text" name="department" id="prof_department"
                                    placeholder="e.g. Medical Services">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_profgroup">Professional Group</label>
                                <input class="form-control" type="text" name="profgroup" id="prof_profgroup">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_catg">Category Code</label>
                                <input class="form-control" type="text" name="catg" id="prof_catg" maxlength="3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_station">Station</label>
                                <input class="form-control" type="text" name="station" id="prof_station">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_clinicroom">Clinic Room</label>
                                <input class="form-control" type="text" name="clinicroom" id="prof_clinicroom"
                                    placeholder="e.g. Room 204">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_clinichours">Clinic Hours</label>
                                <input class="form-control" type="text" name="clinichours" id="prof_clinichours"
                                    placeholder="e.g. 9:00 AM - 12:00 PM">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_groupname">Group Name</label>
                                <input class="form-control" type="text" name="groupname" id="prof_groupname"
                                    maxlength="3">
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Rates, Tax & Billing -->
                    <div class="tab-pane fade" id="doc-pane-rates" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_pfrate">PF Rate / Consultation Fee
                                    (₱)</label>
                                <input class="form-control" type="number" step="0.01" name="pfrate" id="prof_pfrate"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_rodrate">ROD Rate (₱)</label>
                                <input class="form-control" type="number" step="0.01" name="rodrate" id="prof_rodrate"
                                    placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_tax">Withholding Tax %</label>
                                <input class="form-control" type="number" step="0.01" name="tax" id="prof_tax"
                                    placeholder="e.g. 10.00">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_vatrate">VAT Rate %</label>
                                <input class="form-control" type="number" step="0.01" name="vatrate" id="prof_vatrate"
                                    placeholder="e.g. 12.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_coacode">Chart of Accounts Code</label>
                                <input class="form-control" type="text" name="coacode" id="prof_coacode">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="prof_accountno">Bank / Account No.</label>
                                <input class="form-control" type="text" name="accountno" id="prof_accountno">
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="vatable" id="prof_vatable"
                                        value="1">
                                    <label class="form-check-label fw-bold" for="prof_vatable">VATable Entity</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="autoAddVAT"
                                        id="prof_autoAddVAT" value="1">
                                    <label class="form-check-label fw-bold" for="prof_autoAddVAT">Auto Add VAT</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="issuehospOR"
                                        id="prof_issuehospOR" value="1">
                                    <label class="form-check-label fw-bold" for="prof_issuehospOR">Issue Hospital
                                        OR</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 5: System Settings & Notes -->
                    <div class="tab-pane fade" id="doc-pane-system" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="quevisible"
                                        id="prof_quevisible" value="1" checked>
                                    <label class="form-check-label fw-bold" for="prof_quevisible">Visible in
                                        Queue</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allowtextresult"
                                        id="prof_allowtextresult" value="1">
                                    <label class="form-check-label fw-bold" for="prof_allowtextresult">Allow Text
                                        Result</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allowdocsystem"
                                        id="prof_allowdocsystem" value="1">
                                    <label class="form-check-label fw-bold" for="prof_allowdocsystem">Allow Doc
                                        System</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="disabletext"
                                        id="prof_disabletext" value="1">
                                    <label class="form-check-label fw-bold" for="prof_disabletext">Disable Text</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="prof_otherinfo">Other Information / Practice
                                Notes</label>
                            <textarea class="form-control" name="otherinfo" id="prof_otherinfo" rows="2"></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold" for="prof_biodata">Doctor Biodata / Curriculum
                                Vitae</label>
                            <textarea class="form-control" name="biodata" id="prof_biodata" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </form>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary fw-bold" id="save_doctor_profile_btn">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Profile
                </button>
            </div>
        </div>
    </div>
</div>