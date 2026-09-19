<div class="modal fade" data-bs-backdrop="static" id="settlementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><span class="fa-solid fa-credit-card"></span> Settlements</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#gen_sett" role="tab" aria-controls="gen_sett" aria-selected="true" type="button">
                            Generate Setlements
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#view_sett" role="tab" aria-controls="view_sett" aria-selected="true" type="button" id="view_sett_btn">
                            View Settlements
                        </button>
                    </li>
                </div>

                <div class="tab-content border border-top-0 rounded-bottom p-4 d-flex flex-column flex-grow-1">
                    <div class="tab-pane active" id="gen_sett">
                        <form class="d-flex flex-column gap-3" id="settlement_form">
                            @csrf
                            <div class="text-center w-100">
                                <h2 class="mb-1">
                                    <span class="fw-bold">Total:</span> <span id="total_amount"></span>
                                </h2>

                                <p class="text-secondary fw-bold">Remaining: <span class="fw-normal" id="remaining"></span></p>
                            </div>

                            <input type="hidden" name="sett_consultationrefno" id="sett_consultationrefno">
                            <input type="hidden" name="total" id="total">

                            <div class="d-flex flex-column gap-2">
                                <div class="input-group">
                                    <div class="input-group-text justify-content-center d-flex fw-bold" style="width: 5rem">CASH</div>
                                    <input class="form-control settlement-input" type="number" step="0.01" min="0.00" placeholder="0.00" name="cash" id="cash">
                                    <button class="btn btn-secondary import-total" type="button" title="Import total"><i class="fa-solid fa-circle-arrow-down"></i></button>
                                </div>

                                <div class="input-group">
                                    <div class="input-group-text justify-content-center fw-bold" style="width: 5rem">CTA</div>
                                    <input class="form-control settlement-input" step="0.01" min="0.00" placeholder="0.00"  type="number" name="cta" id="cta">
                                    <select class="form-select" name="card_type" id="card_type">
                                        <option value="" selected disabled>-- Select Card Type --</option>
                                        <option value="cc">Credit Card</option>
                                        <option value="dc">Debit Card</option>
                                    </select>
                                    <button class="btn btn-secondary import-total" type="button" title="Import total"><i class="fa-solid fa-circle-arrow-down"></i></button>
                                </div>

                                <div class="input-group">
                                    <div class="input-group-text justify-content-center fw-bold" style="width: 5rem">--:--</div>
                                    <input class="form-control settlement-input" type="number" step="0.01" min="0.00" placeholder="0.00" name="aaa" id="aaa">
                                    <button class="btn btn-secondary import-total" type="button" title="Import total"><i class="fa-solid fa-circle-arrow-down"></i></button>
                                </div>

                                <div class="input-group">
                                    <div class="input-group-text justify-content-center fw-bold" style="width: 5rem">HMO</div>
                                    <input class="form-control settlement-input" step="0.01" min="0.00" placeholder="0.00"  type="number" name="hmo" id="hmo">
                                    <select class="form-select" name="hmo_type" id="hmo_type">
                                        <option value="" selected disabled>-- Select HMO --</option>
                                    </select>
                                    <button class="btn btn-secondary import-total" type="button" title="Import total"><i class="fa-solid fa-circle-arrow-down"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Detailed Comment: View Settlements tab with text inputs to safely render formatted currency and string names (CTA card types, HMO labels) --}}
                    <div class="tab-pane" id="view_sett">
                        <div class="d-flex flex-column gap-2">
                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="width: 10rem;">TOTAL</span>
                                <input class="form-control" type="text" id="info_total" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="width: 10rem;">CASH</span>
                                <input class="form-control" type="text" id="info_cash" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="width: 10rem;">CTA</span>
                                <input class="form-control" type="text" id="info_cta" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="width: 10rem;">CTA Type</span>
                                <input class="form-control" type="text" id="info_cta_type" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="width: 10rem;">HMO</span>
                                <input class="form-control" type="text" id="info_hmo" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="width: 10rem;">HMO Type</span>
                                <input class="form-control" type="text" id="info_hmo_type" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="save_settlements">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeTakePhoto">Close</button>
            </div>
        </div>
    </div>
</div>