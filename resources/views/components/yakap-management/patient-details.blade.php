<div class="container-fluid px-0">
    <form id="patientDetailsForm">
        @csrf
        <input type="hidden" name="dCaseNo" id="dCaseNo" />

        <h5>
            Clinic Patient Number:
            <span id="px_pin"></span>
            <a href="#" id="link_patient_btn" class="d-none" data-bs-toggle="modal"
                data-bs-target="#linkPatientModal">
                <i class="fa-solid fa-link"></i> Link this patient
            </a>
        </h5>
        {{-- PhilHealth Information --}}
        <div class="mb-4">
            <h6><u>PhilHealth Information</u></h6>
            <div class="row g-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Registration Date:</label>
                    <input type="date" name="dEnlistDate" id="dEnlistDate" class="datepicker form-control"
                        placeholder="mm/dd/yyyy" style="width:160px; text-transform:uppercase" required readonly />
                </div>
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Package Type:</label>
                    <select name="dPackageType" id="dPackageType" class="form-select" style="width:160px;" required>
                        <option value="K" selected>KONSULTA</option>
                    </select>
                </div>
                <div class="col-auto d-flex align-items-center" style="padding-bottom: 6px;">
                    <input type="checkbox" name="dWithConsent" id="dWithConsent" class="form-check-input me-2"
                        value="Y" checked />
                    <label for="dWithConsent" class="form-check-label fw-bold">
                        <span class="text-danger">*</span> With Consent to share patient record?
                    </label>
                </div>
            </div>
        </div>

        {{-- Client's Information --}}
        <div class="mb-4">
            <h6><u>Client's Information</u></h6>

            <div class="row g-3 mb-3">
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Client Type:</label>
                    <select name="dPatientType" id="dPatientType" class="form-select" style="width:200px;">
                        <option value="MM" selected>Member</option>
                        <option value="SP">Spouse</option>
                        <option value="CH">Child</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> PIN:</label>
                    <input type="text" name="dPatientPin" id="dPatientPin" class="form-control"
                        style="width:160px; text-transform:uppercase" autocomplete="off" minlength="12" maxlength="12"
                        readonly required />
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label class="form-label"><span class="text-danger">*</span> Last Name:</label>
                    <input type="text" name="dPatientLname" id="dPatientLname" class="form-control"
                        style="text-transform:uppercase" maxlength="60" autocomplete="off" required />
                </div>
                <div class="col">
                    <label class="form-label"><span class="text-danger">*</span> First Name:</label>
                    <input type="text" name="dPatientFname" id="dPatientFname" class="form-control"
                        style="text-transform:uppercase" maxlength="60" autocomplete="off" required />
                </div>
                <div class="col">
                    <label class="form-label">Middle Name:</label>
                    <input type="text" name="dPatientMname" id="dPatientMname" class="form-control"
                        style="text-transform:uppercase" maxlength="60" autocomplete="off" />
                </div>
                <div class="col">
                    <label class="form-label">Extension (SR/JR):</label>
                    <input type="text" name="dPatientExtname" id="dPatientExtname" class="form-control"
                        style="text-transform:uppercase" maxlength="4" autocomplete="off" />
                </div>
            </div>

            <div class="row g-3">
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Date of Birth:</label>
                    <input type="date" name="dPatientDob" id="dPatientDob" class="datepicker form-control"
                        placeholder="mm/dd/yyyy" style="width:160px; text-transform:uppercase" autocomplete="off"
                        required />
                </div>
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Sex:</label>
                    <select name="dPatientSex" id="dPatientSex" class="form-select" style="width:160px;" required>
                        <option value="" selected disabled>Select Sex</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Mobile Number:</label>
                    <input type="text" name="dPatientMobileNo" id="dPatientMobileNo" class="form-control"
                        style="width:160px; text-transform:uppercase" maxlength="11" autocomplete="off" required />
                </div>
                <div class="col-auto">
                    <label class="form-label">Landline Number:</label>
                    <input type="text" name="dPatientLandlineNo" id="dPatientLandlineNo" class="form-control"
                        style="width:160px; text-transform:uppercase" maxlength="11" autocomplete="off" />
                </div>
            </div>
        </div>

        {{-- Member Information --}}
        <div class="mb-4">
            <h6><u>Member Information</u></h6>

            <div class="row g-3 mb-3">
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Member PIN:</label>
                    <input type="text" name="dMemPin" id="dMemPin" class="form-control"
                        style="width:160px; text-transform:uppercase" autocomplete="off" minlength="12"
                        maxlength="12" readonly required />
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Member's Last Name:</label>
                    <input type="text" name="dMemLname" id="dMemLname" class="form-control"
                        style="width:160px; text-transform:uppercase" maxlength="60" autocomplete="off" readonly
                        required />
                </div>
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Member's First Name:</label>
                    <input type="text" name="dMemFname" id="dMemFname" class="form-control"
                        style="width:160px; text-transform:uppercase" maxlength="60" autocomplete="off" readonly
                        required />
                </div>
                <div class="col-auto">
                    <label class="form-label">Member's Middle Name:</label>
                    <input type="text" name="dMemMname" id="dMemMname" class="form-control"
                        style="width:160px; text-transform:uppercase" maxlength="60" autocomplete="off" readonly />
                </div>
                <div class="col-auto">
                    <label class="form-label">Extension (SR/JR):</label>
                    <input type="text" name="dMemExtname" id="dMemExtname" class="form-control"
                        style="width:100px; text-transform:uppercase" maxlength="4" autocomplete="off" readonly />
                </div>
            </div>

            <div class="row g-3">
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Member's Date of Birth:</label>
                    <input type="date" name="dMemDob" id="dMemDob" class="datepicker form-control"
                        placeholder="mm/dd/yyyy" style="width:160px; text-transform:uppercase" readonly required />
                </div>
                <div class="col-auto">
                    <label class="form-label"><span class="text-danger">*</span> Member Sex:</label>
                    <select name="dMemberSex" id="dMemberSex" class="form-select" style="width:160px;">
                        <option value="" selected disabled>Select Sex</option>
                    </select>
                </div>
            </div>
        </div>

        <p class="text-danger" style="font-size:10px; font-family: Verdana, Geneva, sans-serif;">
            <i>NOTE: All fields marked with asterisk (*) are required.</i>
        </p>

        <div class="d-flex justify-content-center mb-3">
            <button type="submit" class="btn btn-primary">Save Details</button>
        </div>

        <hr>
    </form>
</div>
