@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/consultations/settlements.js')
@endpush

@section('content')
    <!-- Detailed Comment: Admin Consultations Settlements Management View with Column Filters, Add/Edit Modals, and Confirmation Dialogs -->
    <div class="card h-100 p-4" id="admin_page">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h1 class="m-0"><i class="fa-solid fa-cash-register text-primary me-2"></i> Payment & Settlements</h1>
                <p class="text-muted small m-0">Audit, record, and reconcile consultation cashier settlements, cash payments, card receipts, and HMO claims.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary fw-bold" id="btn_open_add_settlement">
                    <i class="fa-solid fa-plus me-1"></i> Add Settlement Record
                </button>
            </div>
        </div>
        <hr>

        <!-- Detailed Comment: min-height ensures column filter dropdown menus have ample vertical space without clipping even when table has few or zero records -->
        <div class="table-responsive" style="min-height: 380px;">
            <table class="table table-sm table-bordered table-hover align-middle caption-top w-100" id="settlements_table">
                <caption>Masterlist of Patient Consultation Settlements and Cashier Receipts</caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 140px;" class="text-center">Actions</th>
                        <th scope="col" id="th_stl_date">Date</th>
                        <th scope="col" id="th_stl_refno">Consultation Ref</th>
                        <th scope="col" id="th_stl_doc">Attending Doctor</th>
                        <th scope="col" id="th_stl_gross" class="text-end">Gross Total (₱)</th>
                        <th scope="col" id="th_stl_cash" class="text-end">Cash (₱)</th>
                        <th scope="col" id="th_stl_card" class="text-end">Card / CTA (₱)</th>
                        <th scope="col" id="th_stl_hmo" class="text-end">HMO (₱)</th>
                        <th scope="col" id="th_stl_phic" class="text-end">PhilHealth (₱)</th>
                        <th scope="col" id="th_stl_payable" class="text-end">Net Payable (₱)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Comment: Modal for creating new Consultation Settlement Record -->
    <div class="modal fade" id="add_settlement_modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="addStlModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addStlModalLabel">
                        <i class="fa-solid fa-plus-circle text-primary me-2"></i> Add Consultation Settlement
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_settlement_form">
                    @csrf
                    <input type="hidden" name="docrefno" id="add_stl_docrefno">
                    <input type="hidden" name="pincode" id="add_stl_pincode">

                    <div class="modal-body p-4">
                        <!-- Select Active Consultation -->
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="add_stl_consultation_select">Select Consultation / Patient <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_stl_consultation_select" required>
                                <option value="" selected disabled>-- Choose Active Consultation --</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="add_stl_consultationrefno">Consultation Ref # <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace" name="consultationrefno" id="add_stl_consultationrefno" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="add_stl_docname">Attending Doctor</label>
                                <input type="text" class="form-control" name="docname" id="add_stl_docname">
                            </div>
                        </div>

                        <!-- Gross Fees Breakdown -->
                        <h6 class="fw-bold text-primary border-bottom pb-1 mb-2"><i class="fa-solid fa-list-check me-1"></i> Gross Fees Breakdown</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="add_stl_doctorspf">Doctor's PF (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-add" name="total_doctorspf" id="add_stl_doctorspf" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="add_stl_meds">Medicines (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-add" name="total_meds" id="add_stl_meds" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="add_stl_lab">Laboratory &amp; Diagnostics (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-add" name="total_lab" id="add_stl_lab" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="add_stl_others">Other Charges (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-add" name="total_others" id="add_stl_others" value="0.00" min="0" step="0.01">
                            </div>
                        </div>

                        <!-- Deductions & Coverage -->
                        <h6 class="fw-bold text-danger border-bottom pb-1 mb-2"><i class="fa-solid fa-tags me-1"></i> Deductions &amp; Coverage</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="add_stl_less_vat">Less VAT (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-add" name="less_vat" id="add_stl_less_vat" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="add_stl_less_discount">Senior / PWD / Promo Discount (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-add" name="less_discount" id="add_stl_less_discount" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="add_stl_less_hmo">Less HMO Covered (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-add" name="less_hmo" id="add_stl_less_hmo" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="add_stl_less_phic">Less PhilHealth / Govt (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-add" name="less_phic" id="add_stl_less_phic" value="0.00" min="0" step="0.01">
                            </div>
                        </div>

                        <!-- Summary Totals -->
                        <div class="row g-2 mb-3 p-3 bg-light rounded border">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="add_stl_total_gross">Total Gross Amount (₱)</label>
                                <input type="number" class="form-control text-end fw-bold bg-white" name="total_gross" id="add_stl_total_gross" value="0.00" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success" for="add_stl_net_payable">Net Payable (₱)</label>
                                <input type="number" class="form-control text-end fw-bold bg-white text-success border-success" name="net_payable" id="add_stl_net_payable" value="0.00" readonly>
                            </div>
                        </div>

                        <!-- Payment Channels -->
                        <h6 class="fw-bold text-success border-bottom pb-1 mb-2"><i class="fa-solid fa-money-bill-wave me-1"></i> Payment Settlement</h6>
                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="add_stl_payment_cash">Cash Paid (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end" name="payment_cash" id="add_stl_payment_cash" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="add_stl_payment_card">Card / CTA Paid (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end" name="payment_card" id="add_stl_payment_card" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="add_stl_cta_type">Card / CTA Provider</label>
                                <input type="text" class="form-control form-control-sm" name="cta_type" id="add_stl_cta_type" placeholder="e.g. VISA, GCASH, Maya">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn_save_add_settlement">
                            <i class="fa-solid fa-check me-1"></i> Save Settlement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detailed Comment: Modal for editing existing Consultation Settlement Record -->
    <div class="modal fade" id="edit_settlement_modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="editStlModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editStlModalLabel">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Consultation Settlement
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit_settlement_form">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="edit_stl_consultationrefno">Consultation Ref # <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace" name="consultationrefno" id="edit_stl_consultationrefno" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="edit_stl_docname">Attending Doctor</label>
                                <input type="text" class="form-control" name="docname" id="edit_stl_docname">
                            </div>
                        </div>

                        <!-- Gross Fees Breakdown -->
                        <h6 class="fw-bold text-primary border-bottom pb-1 mb-2"><i class="fa-solid fa-list-check me-1"></i> Gross Fees Breakdown</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_stl_doctorspf">Doctor's PF (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-edit" name="total_doctorspf" id="edit_stl_doctorspf" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_stl_meds">Medicines (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-edit" name="total_meds" id="edit_stl_meds" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_stl_lab">Laboratory &amp; Diagnostics (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-edit" name="total_lab" id="edit_stl_lab" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_stl_others">Other Charges (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-edit" name="total_others" id="edit_stl_others" min="0" step="0.01">
                            </div>
                        </div>

                        <!-- Deductions & Coverage -->
                        <h6 class="fw-bold text-danger border-bottom pb-1 mb-2"><i class="fa-solid fa-tags me-1"></i> Deductions &amp; Coverage</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_stl_less_vat">Less VAT (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-edit" name="less_vat" id="edit_stl_less_vat" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_stl_less_discount">Senior / PWD / Promo Discount (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-edit" name="less_discount" id="edit_stl_less_discount" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_stl_less_hmo">Less HMO Covered (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-edit" name="less_hmo" id="edit_stl_less_hmo" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="edit_stl_less_phic">Less PhilHealth / Govt (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end calc-stl-edit" name="less_phic" id="edit_stl_less_phic" min="0" step="0.01">
                            </div>
                        </div>

                        <!-- Summary Totals -->
                        <div class="row g-2 mb-3 p-3 bg-light rounded border">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="edit_stl_total_gross">Total Gross Amount (₱)</label>
                                <input type="number" class="form-control text-end fw-bold bg-white" name="total_gross" id="edit_stl_total_gross" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success" for="edit_stl_net_payable">Net Payable (₱)</label>
                                <input type="number" class="form-control text-end fw-bold bg-white text-success border-success" name="net_payable" id="edit_stl_net_payable" readonly>
                            </div>
                        </div>

                        <!-- Payment Channels -->
                        <h6 class="fw-bold text-success border-bottom pb-1 mb-2"><i class="fa-solid fa-money-bill-wave me-1"></i> Payment Settlement</h6>
                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="edit_stl_payment_cash">Cash Paid (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end" name="payment_cash" id="edit_stl_payment_cash" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="edit_stl_payment_card">Card / CTA Paid (₱)</label>
                                <input type="number" class="form-control form-control-sm text-end" name="payment_card" id="edit_stl_payment_card" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="edit_stl_cta_type">Card / CTA Provider</label>
                                <input type="text" class="form-control form-control-sm" name="cta_type" id="edit_stl_cta_type">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn_save_edit_settlement">
                            <i class="fa-solid fa-check me-1"></i> Update Settlement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
