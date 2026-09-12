<div class="modal fade" data-bs-backdrop="static" id="edit_charge_modal" tabindex="-1" aria-labelledby="edit_charge_modalModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title"><i class="fa-solid fa-coins"></i> Edit Charge Details</h3>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body d-flex gap-2">
                <div class=" w-100">
                    <label class="form-label fw-bold" for="echarge_name">Charge name</label>
                    <input class="form-control" type="text" name="echarge_name" id="echarge_name">
                </div>

                <div class=" w-100">
                    <label class="form-label fw-bold" for="echarge_catg">Category</label>
                    <select class="form-select" name="echarge_catg" id="echarge_catg"></select>
                </div>

                <div class=" w-100">
                    <label class="form-label fw-bold" for="echarge_amt">Charge amount</label>
                    <input class="form-control" type="number" name="echarge_amt" id="echarge_amt">
                </div>
			</div>

			<div class="modal-footer">
                <button type="button" class="btn btn-primary" id="update_charge_btn">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			</div>
        </form>
	</div>
</div>