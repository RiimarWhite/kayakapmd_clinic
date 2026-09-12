<div class="modal fade" id="editPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editPatientForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Patient Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="pincode" id="edit_pincode" hidden>
                    <input type="text" name="doccode" id="edit_doccode" hidden>

                    <div class=" mb-4">
                        <label class="form-label fw-bold">PIN</label>
                        <input class="form-control" type="text" name="memPin" id="edit_memPin">
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom mb-2">
                        <p class="m-0 fw-bold" style="font-size: 16px;">Member Information</p>
                        <button type="button" class="btn btn-sm btn-outline-primary mb-1" id="syncMemberInfo">
                            <i class="fa-solid fa-copy"></i> Same as Patient
                        </button>
                    </div>
                    
                    <div class="row g-2 ms-1 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small">First Name</label>
                            <input class="form-control" type="text" name="memFname" id="edit_pMemFname">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Middle Name</label>
                            <input class="form-control" type="text" name="memMname" id="edit_pMemMname">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Last Name</label>
                            <input class="form-control" type="text" name="memLname" id="edit_pMemLname">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Suffix</label>
                            <input class="form-control" type="text" name="memExtname" id="edit_pMemExtname">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label class="form-label small">Date of Birth</label>
                            <input class="form-control" type="date" name="memDob" id="edit_pMemDob">
                        </div>
                    </div>

                    <p class="m-0 fw-bold border-bottom mb-2" style="font-size: 16px;">Patient Information</p>
                    <div class="row g-2 ms-1">
                        <div class="col-md-3">
                            <label class="form-label small">First Name</label>
                            <input class="form-control" type="text" name="patientname" id="edit_pPatientFname">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Middle Name</label>
                            <input class="form-control" type="text" name="pxmidname" id="edit_pPatientMname">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Last Name</label>
                            <input class="form-control" type="text" name="pxlastname" id="edit_pPatientLname">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Suffix</label>
                            <input class="form-control" type="text" name="pxsuffix" id="edit_pPatientExtname">
                        </div>
                    </div>

                    <div class="row g-2 ms-1 mt-2">
                        <div class="col-md-2">
                            <label class="form-label small">Sex</label>
                            <select class="form-control" name="gender" id="edit_pPatientSex">
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Date of Birth</label>
                            <input class="form-control" type="date" name="birthday" id="edit_pPatientDob">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Mobile No.</label>
                            <input class="form-control" type="text" name="mobilenumber" id="edit_pPatientMobileNo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Email</label>
                            <input class="form-control" type="email" name="emailaddress" id="edit_email">
                        </div>
                    </div>

                    <div class=" ms-2 mt-2">
                        <label class="form-label small">Address</label>
                        <input class="form-control" type="text" name="address" id="edit_address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updatePatientBtn">Update & Consult Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>