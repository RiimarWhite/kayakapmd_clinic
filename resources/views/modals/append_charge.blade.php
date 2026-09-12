<div class="modal fade" data-bs-backdrop="static" id="append_charge_modal" tabindex="-1" aria-labelledby="append_charge_modalModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title"><i class="fa-solid fa-coins"></i> Append Charges</h3>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>

			<div class="modal-body d-flex gap-2">
                <form id="">
					@csrf

					<input class="form-control" type="search" name="search_charge" id="search_charge">
				</form>
			</div>

			<div class="modal-footer">
                <button type="button" class="btn btn-primary" id="save_charges_btn">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			</div>
        </form>
	</div>
</div>
