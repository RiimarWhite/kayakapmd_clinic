<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-3">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="edit_service_form">
                    @csrf

                    <!-- Hidden token -->
                    <input type="hidden" id="edit_service_token">

                    <!-- Service Reference Code -->
                    <div class="mb-3">
                        <label class="form-label">Service Reference Code</label>
                        <input type="text"
                               class="form-control"
                               id="edit_servicerefno"
                               readonly>
                    </div>

                    <!-- Service Name -->
                    <div class="mb-3">
                        <label class="form-label">Service Name</label>
                        <input type="text"
                               class="form-control"
                               id="edit_servicename"
                               required>
                    </div>

                    <!-- Service Charge -->
                    <div class="mb-3">
                        <label class="form-label">Service Charge</label>
                        <input type="number"
                               class="form-control"
                               id="edit_servicecharge"
                               step="0.01"
                               required>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select"
                                id="edit_service_category"
                                required>
                            <option value="" disabled selected>Select category</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control"
                                  id="edit_servicedscr"
                                  rows="3"
                                  required></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-warning" id="update_service_btn">
                    <i class="fa-solid fa-save"></i> Update Service
                </button>
            </div>
        </div>
    </div>
</div>
