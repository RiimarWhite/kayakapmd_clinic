<div class="modal fade" data-bs-backdrop="static" id="assigned_doctors_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><span class="fa fa-solid fa-user-doctor"></span> Assign Doctors</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-4 p-4">
                <h4 class="fw-bold m-0">Secretary: <span class="fw-normal" id="secretary_name"></span></h4>

                <div class="">
                    <label class="form-label fw-bold" for="availdoctors">Available Doctors</label>
                    <div class="input-group">
                        <select class="form-select" name="availdoctors" id="availdoctors">
                            <option value="" disabled selected>-- List of Available Doctors --</option>
                        </select>
                        <button type="button" class="btn btn-primary" id="assign_doctor_btn">Assign</button>
                    </div>
                </div>
                
                <form id="append_doctor_form">
                    @csrf
                    
                    <div class="d-flex flex-column">
                        <label class="form-label fw-bold">Assigned Doctors</label>
                        <div class="d-inline-flex p-1 gap-1" id="assigned_doctors_badge"></div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="save_append">Save Changes</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>