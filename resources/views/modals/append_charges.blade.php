<div class="modal fade" data-bs-backdrop="static" id="append_charge_modal" tabindex="-1" aria-labelledby="append_charge_modalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="appended_charges_form">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fa-solid fa-coins"></i> Append Charges</h3>
                <button type="button" class="btn-close" data-bs-target="#consultation_modal" data-bs-toggle="modal"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-3">
                <div class="">
                    <label class="form-label fw-bold" for="search_charge">Search Charges</label>
                    <div class="input-group">
                        <select name="form-select" id="search_pxcharge" required></select>
                        <!-- <input class="form-control" type="search" name="search_pxcharge" id="search_pxcharge" required>
                        <input type="hidden" name="charge_code" id="charge_code"> -->

                        <span class="input-group-text"><i class="fa-solid fa-filter"></i></span>
                        <select class="form-select" name="search_filter" id="search_filter"></select>
                    </div>
                    <div class="form-text">Appended charges won't be available from the search.</div>
                </div>

                <div class="d-flex flex-fill align-items-end gap-2">
                    <div class="flex-fill">
                        <label class="form-label fw-bold" for="charge_amount">Charge Amount</label>
                        <input class="form-control" type="number" name="charge_amount" id="charge_amount" required>
                    </div>

                    <div class="flex-fill">
                        <label class="form-label fw-bold" for="charge_discount">Discount</label>
                        <input class="form-control" type="number" name="charge_discount" id="charge_discount">
                    </div>

                    <div class="">
                        <button type="button" class="btn btn-primary text-white" id="append_to_pxcharges_btn"><i class="fa-solid fa-plus"></i> Append Charge</button>
                    </div>
                </div>

                <div class="">
                    <label class="form-label fw-bold">Appended Charges</label>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="appended_charges_table">
                            <thead class="table-warning">
                                <tr>
                                    <th scope="col" style="width: 1%;">Actions</th>
                                    <th scope="col">Charge</th>
                                    <th scope="col">Discount</th>
                                    <th scope="col">Total</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
			</div>

			<div class="modal-footer">
                <button type="button" class="btn btn-primary" id="pxsave_charges_btn">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-target="#consultation_modal" data-bs-toggle="modal">Cancel</button>
			</div>
        </form>
	</div>
</div>
