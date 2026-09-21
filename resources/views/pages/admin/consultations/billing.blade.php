@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/consultations/billing.js')
@endpush

@section('content')
    <!-- Detailed Comment: Admin Consultations Billing Management View with Column Filters, Add/Edit Modals, and Confirmation Dialogs -->
    <div class="card h-100 p-4" id="admin_page">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h1 class="m-0"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i> Consultations Billing</h1>
                <p class="text-muted small m-0">Review, add, and reconcile patient consultation charges, retail items, service fees, and discounts.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary fw-bold" id="btn_open_add_billing">
                    <i class="fa-solid fa-plus me-1"></i> Add Billing Charge
                </button>
            </div>
        </div>
        <hr>

        <!-- Detailed Comment: min-height ensures column filter dropdown menus have ample vertical space without clipping even when table has few or zero records -->
        <div class="table-responsive" style="min-height: 380px;">
            <table class="table table-sm table-bordered table-hover align-middle caption-top w-100" id="billing_table">
                <caption>Masterlist of Patient Consultation Charges and Billing Transactions</caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 140px;" class="text-center">Actions</th>
                        <th scope="col" id="th_bill_date">Date</th>
                        <th scope="col" id="th_bill_refno">Consultation Ref</th>
                        <th scope="col" id="th_bill_pxname">Patient Name</th>
                        <th scope="col" id="th_bill_servicename">Service / Item</th>
                        <th scope="col" id="th_bill_category">Category</th>
                        <th scope="col" id="th_bill_paytype">Payment Type</th>
                        <th scope="col" id="th_bill_total" class="text-end">Total (₱)</th>
                        <th scope="col" id="th_bill_discount" class="text-end">Discount (₱)</th>
                        <th scope="col" id="th_bill_net" class="text-end">Net Total (₱)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Comment: Modal for creating new Consultation Billing Charge -->
    <div class="modal fade" id="add_billing_modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="addBillingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addBillingModalLabel">
                        <i class="fa-solid fa-plus-circle text-primary me-2"></i> Add Consultation Charge
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_billing_form">
                    @csrf
                    <input type="hidden" name="docrefno" id="add_docrefno">
                    <input type="hidden" name="docname" id="add_docname">
                    <input type="hidden" name="pxcode_pin" id="add_pxcode_pin">

                    <div class="modal-body">
                        <!-- Select Active Consultation -->
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="add_consultation_select">Select Consultation / Patient <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_consultation_select" required>
                                <option value="" selected disabled>-- Choose Active Consultation --</option>
                            </select>
                            <div class="form-text">Choose from active patient consultations or type manual details below.</div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="add_consultationrefno">Consultation Ref # <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace" name="consultationrefno" id="add_consultationrefno" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="add_pxname">Patient Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="pxname" id="add_pxname" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="add_servicename">Service / Charge Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="servicename" id="add_servicename" placeholder="e.g. Medical Consultation, Urinalysis" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="add_group_category">Category</label>
                                <input type="text" class="form-control" name="group_category" id="add_group_category" placeholder="e.g. Consultation, Lab">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="add_payment_type">Payment Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="payment_type" id="add_payment_type" required>
                                    <option value="CASH" selected>CASH</option>
                                    <option value="HMO">HMO</option>
                                    <option value="CARD">CARD</option>
                                    <option value="FREE">FREE</option>
                                </select>
                            </div>
                        </div>

                        <!-- Calculation Fields -->
                        <div class="row g-2 mb-2 p-3 bg-light rounded border">
                            <div class="col-md-2">
                                <label class="form-label fw-bold" for="add_quantity">Qty <span class="text-danger">*</span></label>
                                <input type="number" class="form-control text-center calc-input-add" name="quantity" id="add_quantity" value="1" min="1" step="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="add_retail">Unit Price (₱) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control text-end calc-input-add" name="retail" id="add_retail" value="0.00" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold" for="add_total">Total (₱)</label>
                                <input type="number" class="form-control text-end bg-white" name="total" id="add_total" value="0.00" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold" for="add_discount">Discount (₱)</label>
                                <input type="number" class="form-control text-end calc-input-add" name="discount" id="add_discount" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-success" for="add_net_total">Net Total (₱)</label>
                                <input type="number" class="form-control text-end fw-bold bg-white text-success border-success" name="net_total" id="add_net_total" value="0.00" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn_save_add_billing">
                            <i class="fa-solid fa-check me-1"></i> Save Charge
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detailed Comment: Modal for editing existing Consultation Billing Charge -->
    <div class="modal fade" id="edit_billing_modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="editBillingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editBillingModalLabel">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Consultation Charge
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit_billing_form">
                    @csrf
                    <input type="hidden" name="id" id="edit_billing_id">

                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="edit_consultationrefno">Consultation Ref # <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace" name="consultationrefno" id="edit_consultationrefno" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="edit_pxname">Patient Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="pxname" id="edit_pxname" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="edit_servicename">Service / Charge Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="servicename" id="edit_servicename" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="edit_group_category">Category</label>
                                <input type="text" class="form-control" name="group_category" id="edit_group_category">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="edit_payment_type">Payment Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="payment_type" id="edit_payment_type" required>
                                    <option value="CASH">CASH</option>
                                    <option value="HMO">HMO</option>
                                    <option value="CARD">CARD</option>
                                    <option value="FREE">FREE</option>
                                </select>
                            </div>
                        </div>

                        <!-- Calculation Fields -->
                        <div class="row g-2 mb-2 p-3 bg-light rounded border">
                            <div class="col-md-2">
                                <label class="form-label fw-bold" for="edit_quantity">Qty <span class="text-danger">*</span></label>
                                <input type="number" class="form-control text-center calc-input-edit" name="quantity" id="edit_quantity" min="1" step="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="edit_retail">Unit Price (₱) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control text-end calc-input-edit" name="retail" id="edit_retail" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold" for="edit_total">Total (₱)</label>
                                <input type="number" class="form-control text-end bg-white" name="total" id="edit_total" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold" for="edit_discount">Discount (₱)</label>
                                <input type="number" class="form-control text-end calc-input-edit" name="discount" id="edit_discount" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-success" for="edit_net_total">Net Total (₱)</label>
                                <input type="number" class="form-control text-end fw-bold bg-white text-success border-success" name="net_total" id="edit_net_total" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn_save_edit_billing">
                            <i class="fa-solid fa-check me-1"></i> Update Charge
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
