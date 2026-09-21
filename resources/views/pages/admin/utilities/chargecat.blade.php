@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/utilities/chargecat.js')
@endpush

@section('content')
    <!-- Detailed Comment: Admin Charge Categories Management View with Column Filters, Add/Edit Modals, and Confirmation Dialogs -->
    <div class="card h-100 p-4" id="admin_page">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h1 class="m-0"><i class="fa-solid fa-tags text-primary me-2"></i> Charge Categories</h1>
                <p class="text-muted small m-0">Manage fee and billing categories for clinic charges and procedures.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary fw-bold" id="btn_open_add_category">
                    <i class="fa-solid fa-plus me-1"></i> Add Category
                </button>
            </div>
        </div>
        <hr>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle caption-top w-100" id="charge_category_table">
                <caption>Masterlist of Charge Categories</caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 150px;" class="text-center">Actions</th>
                        <th scope="col" id="th_cat_refno">
                            <!-- Populated with renderColumnFilterHeader in JS -->
                            Category Ref No
                        </th>
                        <th scope="col" id="th_cat_name">
                            <!-- Populated with renderColumnFilterHeader in JS -->
                            Category Name
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Comment: Modal for creating new Charge Category -->
    <div class="modal fade" id="add_charge_category_modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="addChargeCatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addChargeCatModalLabel">
                        <i class="fa-solid fa-plus-circle text-primary me-2"></i> Add Charge Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_charge_category_form">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="add_categoryname">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="categoryname" id="add_categoryname" placeholder="e.g. Consultation, Laboratory, Dental" required>
                            <div class="form-text">Provide a concise and distinct category name.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn_save_add_category">
                            <i class="fa-solid fa-check me-1"></i> Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detailed Comment: Modal for editing existing Charge Category -->
    <div class="modal fade" id="edit_charge_category_modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="editChargeCatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editChargeCatModalLabel">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Charge Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit_charge_category_form">
                    @csrf
                    <input type="hidden" name="ecategoryrefno" id="edit_categoryrefno">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="edit_categoryname">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="categoryname" id="edit_categoryname" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn_save_edit_category">
                            <i class="fa-solid fa-check me-1"></i> Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
