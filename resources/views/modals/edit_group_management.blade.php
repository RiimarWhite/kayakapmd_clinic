<!-- Edit Services Group Modal -->
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Services Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="edit_group_form">
                    @csrf

                    <!-- Hidden token -->
                    <input type="hidden" id="edit_group_token">


                    <!-- Group Name -->
                    <div class="mb-3">
                        <label class="form-label">Group Name</label>
                        <input type="text" class="form-control"
                            id="edit_servicegroup_name" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control"
                            id="edit_servicegroup_dscr"
                            rows="3" required></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-warning" id="update_group_btn">
                    <i class="fa-solid fa-save"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>
