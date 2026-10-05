{{-- Detailed Comment: Updated modal ID to settlement_modal and removed tabindex="-1" to prevent Bootstrap focus-trapping Select2 search inputs --}}
<div class="modal fade" data-bs-backdrop="static" id="settlement_modal">
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

                <div class="tab-content border border-top-0 rounded-bottom p-3 d-flex flex-column flex-grow-1">
                    <div class="tab-pane active" id="gen_sett">
                        <form class="d-flex flex-column gap-2" id="settlement_form">
                            @csrf
                            <div class="text-center w-100 py-1">
                                <h3 class="mb-0">
                                    <span class="fw-bold">Total Gross:</span> PHP <span id="total_amount">0.00</span>
                                </h3>
                            </div>

                            <input type="hidden" name="sett_consultationrefno" id="sett_consultationrefno">
                            <input type="hidden" name="total" id="total">
                            <input type="hidden" name="net_payable" id="net_payable_input">

                            {{-- Detailed Comment: Senior Citizen / PWD Discount with Checkbox and sub-fields (Ref Number & Amount) --}}
                            <div class="card p-2 border bg-light mb-1">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" id="is_srpwd" name="is_srpwd" value="1">
                                    <label class="form-check-label fw-bold text-dark small" for="is_srpwd">
                                        <i class="fa-solid fa-person-cane me-1 text-primary"></i> Senior Citizen / PWD Discount
                                    </label>
                                </div>
                                <div class="row g-2 d-none" id="srpwd_fields_wrap">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text fw-bold">Ref / ID #</span>
                                            <input class="form-control" type="text" name="srpwd_refno" id="srpwd_refno" placeholder="Senior / PWD ID">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text fw-bold text-danger">Amount (₱)</span>
                                            <input class="form-control deduction-input text-end" type="number" step="0.01" min="0.00" placeholder="0.00" name="less_srpwd" id="less_srpwd">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Detailed Comment: PhilHealth (PHIC) Coverage with ICD/RVS code and Amount --}}
                            <div class="card p-2 border bg-light mb-1">
                                <div class="fw-bold text-success mb-1 small"><i class="fa-solid fa-heart-pulse me-1"></i> PhilHealth (PHIC) Coverage</div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text fw-bold text-success">ICD / RVS</span>
                                            <input class="form-control" type="text" name="phic_icd_rvs" id="phic_icd_rvs" placeholder="ICD-10 / RVS Code">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text fw-bold text-success">Amount (₱)</span>
                                            <input class="form-control deduction-input text-end" type="number" step="0.01" min="0.00" placeholder="0.00" name="phic" id="phic">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Detailed Comment: HMO Coverage with Provider dropdown sourced from hmo_masterlist table via HMOModel --}}
                            @php
                                $hmoMasterlist = \App\Models\HMOModel::whereNotNull('hmoname')
                                    ->where('hmoname', '!=', '')
                                    ->orderBy('hmoname', 'ASC')
                                    ->get();
                            @endphp
                            <div class="card p-2 border bg-light mb-1">
                                <div class="fw-bold text-info mb-1 small"><i class="fa-solid fa-shield-halved me-1"></i> HMO Coverage</div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm flex-nowrap">
                                            <span class="input-group-text fw-bold">HMO</span>
                                            {{-- Detailed Comment: Add min-width: 0 to ensure Select2 container inside input-group flexbox expands fully without collapsing --}}
                                            <div class="flex-grow-1" style="min-width: 0;">
                                                <select class="form-select form-select-sm w-100" name="hmo_type" id="hmo_type">
                                                    <option value="" selected disabled>-- Select HMO --</option>
                                                    @if(isset($hmoMasterlist) && $hmoMasterlist->isNotEmpty())
                                                        @foreach($hmoMasterlist as $hmoItem)
                                                            <option value="{{ $hmoItem->hmocode }}" data-name="{{ $hmoItem->hmoname }}">{{ $hmoItem->hmoname }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text fw-bold text-info">Amount (₱)</span>
                                            <input class="form-control deduction-input text-end" type="number" step="0.01" min="0.00" placeholder="0.00" name="hmo" id="hmo">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Detailed Comment: Other Discount with Description and Amount --}}
                            <div class="card p-2 border bg-light mb-1">
                                <div class="fw-bold text-secondary mb-1 small"><i class="fa-solid fa-tag me-1"></i> Other Discount</div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text fw-bold">Description</span>
                                            <input class="form-control" type="text" name="discount_description" id="discount_description" placeholder="Discount description">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text fw-bold text-secondary">Amount (₱)</span>
                                            <input class="form-control deduction-input text-end" type="number" step="0.01" min="0.00" placeholder="0.00" name="less_discount" id="less_discount">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Detailed Comment: Net Billing Field dynamically recomputed from Gross - Deductions --}}
                            <div class="p-2 bg-white border border-2 border-primary rounded text-center my-1 shadow-sm">
                                <h4 class="m-0 text-primary fw-bold">
                                    Net Billing: PHP <span id="net_billing_display">0.00</span>
                                </h4>
                                <p class="text-secondary fw-bold small m-0 mt-1">Remaining to Settle: PHP <span class="fw-normal" id="remaining">0.00</span></p>
                            </div>

                            {{-- Detailed Comment: Payment settlement channels (CASH and CTA) moved to the bottom part per user requirements --}}
                            <div class="d-flex flex-column gap-2 mt-1">
                                <div class="input-group">
                                    <div class="input-group-text justify-content-center d-flex fw-bold bg-success text-white" style="width: 5.5rem">CASH</div>
                                    <input class="form-control settlement-payment-input" type="number" step="0.01" min="0.00" placeholder="0.00" name="cash" id="cash">
                                    <button class="btn btn-secondary import-net-billing" type="button" title="Import Net Billing"><i class="fa-solid fa-circle-arrow-down"></i></button>
                                </div>

                                <div class="input-group">
                                    <div class="input-group-text justify-content-center fw-bold bg-primary text-white" style="width: 5.5rem">CTA</div>
                                    <input class="form-control settlement-payment-input" step="0.01" min="0.00" placeholder="0.00" type="number" name="cta" id="cta">
                                    <select class="form-select" name="card_type" id="card_type" style="max-width: 12rem;">
                                        <option value="" selected disabled>-- Select Card Type --</option>
                                        <option value="cc">Credit Card</option>
                                        <option value="dc">Debit Card</option>
                                    </select>
                                    <button class="btn btn-secondary import-net-billing" type="button" title="Import Net Billing"><i class="fa-solid fa-circle-arrow-down"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Detailed Comment: View Settlements tab with comprehensive itemized breakdown --}}
                    <div class="tab-pane" id="view_sett">
                        <div class="d-flex flex-column gap-2">
                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="width: 12rem;">TOTAL GROSS</span>
                                <input class="form-control" type="text" id="info_total" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold text-danger" style="width: 12rem;">Senior/PWD Discount</span>
                                <input class="form-control" type="text" id="info_srpwd" readonly>
                                <span class="input-group-text">Ref #</span>
                                <input class="form-control" type="text" id="info_srpwd_ref" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold text-success" style="width: 12rem;">PHIC Coverage</span>
                                <input class="form-control" type="text" id="info_phic" readonly>
                                <span class="input-group-text">ICD/RVS</span>
                                <input class="form-control" type="text" id="info_phic_icd" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold text-info" style="width: 12rem;">HMO Coverage</span>
                                <input class="form-control" type="text" id="info_hmo" readonly>
                                <span class="input-group-text">Provider</span>
                                <input class="form-control" type="text" id="info_hmo_type" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold text-secondary" style="width: 12rem;">Other Discount</span>
                                <input class="form-control" type="text" id="info_discount" readonly>
                                <span class="input-group-text">Note</span>
                                <input class="form-control" type="text" id="info_discount_desc" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold bg-primary text-white" style="width: 12rem;">NET BILLING</span>
                                <input class="form-control fw-bold fs-5 text-primary" type="text" id="info_net_payable" readonly>
                            </div>

                            <hr class="my-1">

                            <div class="input-group">
                                <span class="input-group-text fw-bold text-success" style="width: 12rem;">CASH PAID</span>
                                <input class="form-control" type="text" id="info_cash" readonly>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text fw-bold text-primary" style="width: 12rem;">CTA / CARD PAID</span>
                                <input class="form-control" type="text" id="info_cta" readonly>
                                <span class="input-group-text">Type</span>
                                <input class="form-control" type="text" id="info_cta_type" readonly>
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