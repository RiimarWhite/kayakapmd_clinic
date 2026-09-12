<div class="modal fade" data-bs-backdrop="static" id="add_doctor_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><span class="fa fa-solid fa-user-doctor"></span> Add Doctor</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body d-flex flex-column gap-2" id="add_doctor_form">
                @csrf

                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="docfname">First Name</label>
                        <input class="form-control" type="text" name="docfname" id="docfname" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="docmname">Middle Name</label>
                        <input class="form-control" type="text" name="docmname" id="docmname">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="doclname">Last Name</label>
                        <input class="form-control" type="text" name="doclname" id="doclname" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="suffix">Suffix</label>
                        <input class="form-control" type="text" name="suffix" id="suffix" maxlength="10">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="titlename">Title</label>
                        <input class="form-control" type="text" name="titlename" id="titlename">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="emailadd">Email Address</label>
                        <input class="form-control" type="email" name="emailadd" id="emailadd">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="cellno">Contact #</label>
                        <input class="form-control" type="tel" name="cellno" id="cellno" placeholder="(+63)">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="adrs">Address</label>
                        <input class="form-control" type="text" name="adrs" id="adrs">
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="proftype">Professional Type</label>
                        <input class="form-control" type="text" name="proftype" id="proftype">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="expertise">Expertise</label>
                        <input class="form-control" type="text" name="expertise" id="expertise">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="tin">TIN</label>
                        <input class="form-control" type="text" name="tin" id="tin">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="licno">PRC License #</label>
                        <input class="form-control" type="text" name="licno" id="licno" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="licnoexpiry">Valid Until</label>
                        <input class="form-control" type="date" name="licnoexpiry" id="licnoexpiry" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="phicno">PHIC License #</label>
                        <input class="form-control" type="text" name="phicno" id="phicno" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="phicexpiry">Valid Until</label>
                        <input class="form-control" type="date" name="phicexpiry" id="phicexpiry" required>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="status">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="statusreason">Status Reason</label>
                        <input class="form-control" type="text" name="statusreason" id="statusreason">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="pass">Default Password</label>
                        <input class="form-control" type="text" name="pass" id="pass" required>
                    </div>
                </div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="add_doctor_form_btn">Add Doctor</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>