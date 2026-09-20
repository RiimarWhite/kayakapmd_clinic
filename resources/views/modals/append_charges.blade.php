<!--
  Detailed Comment: Append Charges Modal for Secretary Queue and Admin Secretary Panel.
  Allows searching clinic charges/supplies/medicines/diagnostics via Select2, specifying quantity
  and unit price, queueing multiple charge items, and saving them into stocks_ledger via saveAppendedCharges.
-->
<div class="modal fade" data-bs-backdrop="static" id="append_charge_modal" tabindex="-1" aria-labelledby="appendChargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="sec_appended_charges_form">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fa-solid fa-coins"></i> Append Charges</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label fw-bold" for="sec_search_charge">Search Charges</label>
                    <div class="input-group">
                        <select class="form-select flex-fill" name="sec_search_charge" id="sec_search_charge" required></select>
                        <span class="input-group-text"><i class="fa-solid fa-filter"></i></span>
                        <select class="form-select" name="sec_search_filter" id="sec_search_filter" style="max-width: 15rem;">
                            <option value="ALL">ALL CATEGORIES</option>
                            <option value="SUPPLIES">SUPPLIES</option>
                            <option value="DRUGS AND MEDS">DRUGS AND MEDS</option>
                            <option value="PROCEDURES">PROCEDURES</option>
                            <option value="DIAGNOSTIC">DIAGNOSTIC</option>
                            <option value="IMAGING">IMAGING</option>
                            <option value="PROFESSIONAL FEE">PROFESSIONAL FEE</option>
                        </select>
                    </div>
                    <div class="form-text">Choose a service, procedure, medicine, or diagnostic fee to append to this consultation.</div>
                </div>

                <div class="d-flex align-items-end gap-2">
                    <div class="flex-fill" style="max-width: 10rem;">
                        <label class="form-label fw-bold" for="sec_charge_qty">Quantity</label>
                        <input class="form-control" type="number" name="sec_charge_qty" id="sec_charge_qty" min="1" value="1" required>
                    </div>

                    <div class="flex-fill">
                        <label class="form-label fw-bold" for="sec_charge_amount">Unit Price (PHP)</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="sec_charge_amount" id="sec_charge_amount" placeholder="0.00" required>
                    </div>

                    <div>
                        <button type="button" class="btn btn-primary text-white fw-bold" id="sec_append_to_charges_btn">
                            <i class="fa-solid fa-plus"></i> Append Charge
                        </button>
                    </div>
                </div>

                <div>
                    <label class="form-label fw-bold">Appended Charges to Save</label>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle" id="sec_appended_charges_table">
                            <thead class="table-warning">
                                <tr>
                                    <th scope="col" style="width: 1%;" class="text-center">Action</th>
                                    <th scope="col">Description</th>
                                    <th scope="col" style="width: 10%;" class="text-center">Quantity</th>
                                    <th scope="col" style="width: 15%;" class="text-end">Unit Price</th>
                                    <th scope="col" style="width: 15%;" class="text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="no-charges-placeholder">
                                    <td class="align-middle text-center text-muted" colspan="5">No pending charges appended yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success fw-bold" id="sec_save_charges_btn">
                    <i class="fa-solid fa-floppy-disk"></i> Save Charges
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
