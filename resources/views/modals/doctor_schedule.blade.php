<!-- Detailed Comment: Doctor Clinic Schedule Management Modals.
     Provides dedicated modals for adding and editing doctor clinic schedules from the Doctor Dashboard. -->

<!-- Modal: Add Doctor Schedule -->
<div class="modal fade" data-bs-backdrop="static" id="doctor_add_schedule_modal" tabindex="-1" aria-labelledby="doctorAddScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="doctorAddScheduleModalLabel">
                    <i class="fa-solid fa-calendar-plus me-2"></i> Add Clinic Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="doctor_add_schedule_form" class="modal-body p-3">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold" for="add_sched_day">Day of Week <span class="text-danger">*</span></label>
                    <select class="form-select" id="add_sched_day" name="day" required>
                        <option value="" disabled selected>-- Select Day --</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold" for="add_sched_start">Start Time <span class="text-danger">*</span></label>
                        <input class="form-control" type="time" id="add_sched_start" name="start" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold" for="add_sched_end">End Time <span class="text-danger">*</span></label>
                        <input class="form-control" type="time" id="add_sched_end" name="end" required>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 small mb-0">
                    <i class="fa-solid fa-circle-info me-1"></i> Schedules configure available consultation slots for patient appointment queueing.
                </div>
            </form>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="save_doctor_schedule_btn">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Schedule
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Doctor Schedule -->
<div class="modal fade" data-bs-backdrop="static" id="doctor_edit_schedule_modal" tabindex="-1" aria-labelledby="doctorEditScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="doctorEditScheduleModalLabel">
                    <i class="fa-solid fa-pen-to-square me-2"></i> Edit Clinic Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="doctor_edit_schedule_form" class="modal-body p-3">
                @csrf
                <input type="hidden" id="edit_sched_refno" name="schedrefno">

                <div class="mb-3">
                    <label class="form-label fw-bold" for="edit_sched_day">Day of Week <span class="text-danger">*</span></label>
                    <select class="form-select" id="edit_sched_day" name="day" required>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold" for="edit_sched_start">Start Time <span class="text-danger">*</span></label>
                        <input class="form-control" type="time" id="edit_sched_start" name="start" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold" for="edit_sched_end">End Time <span class="text-danger">*</span></label>
                        <input class="form-control" type="time" id="edit_sched_end" name="end" required>
                    </div>
                </div>
            </form>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="update_doctor_schedule_btn">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Update Schedule
                </button>
            </div>
        </div>
    </div>
</div>
