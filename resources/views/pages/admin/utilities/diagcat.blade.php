@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/utilities/diagcat.js')
@endpush

@section('content')
    <!-- Detailed Comment: Admin Diagnostic Categories Management View with Column Filters, Add/Edit Modals, and Confirmation Dialogs -->
    <div class="card h-100 p-4" id="admin_page">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h1 class="m-0"><i class="fa-solid fa-microscope text-primary me-2"></i> Diagnostic Categories</h1>
                <p class="text-muted small m-0">Manage diagnostic classifications for laboratory tests, imaging, and radiology procedures.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary fw-bold" id="btn_open_add_diagcat">
                    <i class="fa-solid fa-plus me-1"></i> Add Category
                </button>
            </div>
        </div>
        <hr>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle caption-top w-100" id="diag_category_table">
                <caption>Masterlist of Diagnostic Categories</caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 150px;" class="text-center">Actions</th>
                        <th scope="col" id="th_diagcat_refno">Category Ref No</th>
                        <th scope="col" id="th_diagcat_name">Category Name</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Comment: Modal for creating new Diagnostic Category -->
    <div class="modal fade" id="add_diagcat_modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="addDiagCatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addDiagCatModalLabel">
                        <i class="fa-solid fa-plus-circle text-primary me-2"></i> Add Diagnostic Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_diagcat_form">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="add_diagcat_name">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="category_name" id="add_diagcat_name" placeholder="e.g. Hematology, Clinical Microscopy, Radiology" required>
                            <div class="form-text">Provide a diagnostic category title.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn_save_add_diagcat">
                            <i class="fa-solid fa-check me-1"></i> Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detailed Comment: Modal for editing existing Diagnostic Category -->
    <div class="modal fade" id="edit_diagcat_modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="editDiagCatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editDiagCatModalLabel">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Diagnostic Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit_diagcat_form">
                    @csrf
                    <input type="hidden" name="category_refno" id="edit_diagcat_refno">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="edit_diagcat_name">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="category_name" id="edit_diagcat_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn_save_edit_diagcat">
                            <i class="fa-solid fa-check me-1"></i> Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
