@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/hmo.js')
@endpush

@section('content')
    <!-- Detailed Comment: Admin HMO Masterlist Management View with Column Filters, Add/Edit Modals, and Confirmation Dialogs -->
    <div class="card h-100 p-4" id="admin_page">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h1 class="m-0"><i class="fa-solid fa-hospital-user text-primary me-2"></i> HMO Masterlist</h1>
                <p class="text-muted small m-0">Manage health maintenance organizations, accredited insurance providers, and government partners.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary fw-bold" id="btn_open_add_hmo">
                    <i class="fa-solid fa-plus me-1"></i> Add HMO Provider
                </button>
            </div>
        </div>
        <hr>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle caption-top w-100" id="admin_hmo_table">
                <caption>List of Accredited HMOs and Insurance Companies</caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 150px;" class="text-center">Actions</th>
                        <th scope="col" id="th_hmo_code">HMO Code</th>
                        <th scope="col" id="th_hmo_name">HMO Name</th>
                        <th scope="col" id="th_hmo_type">Type</th>
                        <th scope="col" id="th_hmo_accre">Accreditation #</th>
                        <th scope="col" id="th_hmo_address">Address</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Comment: Modal for creating new HMO record -->
    <div class="modal fade" id="add_hmo_modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="addHmoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addHmoModalLabel">
                        <i class="fa-solid fa-plus-circle text-primary me-2"></i> Add HMO Provider
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_hmo_form">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="add_hmocode">HMO Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace" name="hmocode" id="add_hmocode" placeholder="e.g. MAXICARE" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold" for="add_hmoname">HMO Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="hmoname" id="add_hmoname" placeholder="e.g. Maxicare Healthcare Corp." required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="add_hmotype">Provider Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="hmotype" id="add_hmotype" required>
                                    <option value="HMO" selected>HMO</option>
                                    <option value="GOVERNMENT">Government</option>
                                    <option value="COMPANY">Company</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="add_accre_no">Accreditation Number</label>
                                <input type="text" class="form-control" name="accre_no" id="add_accre_no" placeholder="Accreditation No.">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="add_coacode">Chart of Accounts Code</label>
                                <input type="text" class="form-control" name="coacode" id="add_coacode" placeholder="COA Code">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold" for="add_hmoaddress">Provider Address</label>
                            <textarea class="form-control" name="hmoaddress" id="add_hmoaddress" rows="2" placeholder="Corporate office or billing address"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn_save_add_hmo">
                            <i class="fa-solid fa-check me-1"></i> Save HMO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detailed Comment: Modal for editing existing HMO record -->
    <div class="modal fade" id="edit_hmo_modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="editHmoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editHmoModalLabel">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit HMO Provider
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit_hmo_form">
                    @csrf
                    <input type="hidden" name="code" id="edit_original_hmocode">
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="edit_hmocode">HMO Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace" name="hmocode" id="edit_hmocode" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold" for="edit_hmoname">HMO Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="hmoname" id="edit_hmoname" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="edit_hmotype">Provider Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="hmotype" id="edit_hmotype" required>
                                    <option value="HMO">HMO</option>
                                    <option value="GOVERNMENT">Government</option>
                                    <option value="COMPANY">Company</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="edit_accre_no">Accreditation Number</label>
                                <input type="text" class="form-control" name="accre_no" id="edit_accre_no">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="edit_coacode">Chart of Accounts Code</label>
                                <input type="text" class="form-control" name="coacode" id="edit_coacode">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold" for="edit_hmoaddress">Provider Address</label>
                            <textarea class="form-control" name="hmoaddress" id="edit_hmoaddress" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn_save_edit_hmo">
                            <i class="fa-solid fa-check me-1"></i> Update HMO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
