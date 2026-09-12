<div class="modal fade" data-bs-backdrop="static" id="edit_doctor_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><span class="fa fa-solid fa-user-doctor"></span> Edit Doctor</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body d-flex flex-column gap-4" id="edit_doctor_form">
                @csrf

                <input type="hidden" name="docrefno" id="edocrefno">

                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="edocfname">First Name</label>
                        <input class="form-control" type="text" name="edocfname" id="edocfname" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="edocmname">Middle Name</label>
                        <input class="form-control" type="text" name="edocmname" id="edocmname">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="edoclname">Last Name</label>
                        <input class="form-control" type="text" name="edoclname" id="edoclname" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="esuffix">Suffix</label>
                        <input class="form-control" type="text" name="esuffix" id="esuffix">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="etitlename">Title</label>
                        <input class="form-control" type="text" name="etitlename" id="etitlename">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="eemailadd">Email Address</label>
                        <input class="form-control" type="email" name="eemailadd" id="eemailadd">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="ecellno">Contact #</label>
                        <input class="form-control" type="tel" name="ecellno" id="ecellno">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="eadrs">Address</label>
                        <input class="form-control" type="text" name="eadrs" id="eadrs">
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="eproftype">Professional Type</label>
                        <input class="form-control" type="text" name="eproftype" id="eproftype">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="eexpertise">Expertise</label>
                        <input class="form-control" type="text" name="eexpertise" id="eexpertise">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="etin">TIN</label>
                        <input class="form-control" type="text" name="etin" id="etin">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="elicno">PRC License #</label>
                        <input class="form-control" type="text" name="elicno" id="elicno" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="elicnoexpiry">Valid Until</label>
                        <input class="form-control" type="date" name="elicnoexpiry" id="elicnoexpiry" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="ephicno">PHIC License #</label>
                        <input class="form-control" type="text" name="ephicno" id="ephicno" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="ephicexpiry">Valid Until</label>
                        <input class="form-control" type="date" name="ephicexpiry" id="ephicexpiry" required>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="estatus">Status</label>
                        <select class="form-select" name="estatus" id="estatus">
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="estatusreason">Status Reason</label>
                        <input class="form-control" type="text" name="estatusreason" id="estatusreason">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label" for="epass">New Password</label>
                        <input class="form-control" type="password" name="epass" id="epass" required>
                    </div>
                </div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="edit_doctor_form_btn">Save Doctor</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>